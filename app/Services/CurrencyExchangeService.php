<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Servicio para conversion de monedas
 * Utiliza API de tasas de cambio para convertir precios entre diferentes monedas
 */
class CurrencyExchangeService
{
    private $rapidApiKey;
    private $rapidApiHost;
    private $baseUrl;
    private $defaultCurrency;
    private $useMockData;

    // Monedas soportadas
    private $supportedCurrencies = [
        'USD' => ['name' => 'Dolar Estadounidense', 'symbol' => '$'],
        'MXN' => ['name' => 'Peso Mexicano', 'symbol' => '$'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€'],
        'GBP' => ['name' => 'Libra Esterlina', 'symbol' => '£'],
        'CAD' => ['name' => 'Dolar Canadiense', 'symbol' => 'C$'],
        'JPY' => ['name' => 'Yen Japones', 'symbol' => '¥'],
        'CNY' => ['name' => 'Yuan Chino', 'symbol' => '¥'],
        'BRL' => ['name' => 'Real Brasileno', 'symbol' => 'R$'],
        'ARS' => ['name' => 'Peso Argentino', 'symbol' => '$'],
        'COP' => ['name' => 'Peso Colombiano', 'symbol' => '$'],
    ];

    public function __construct()
    {
        $this->rapidApiKey = config('services.currency_exchange.rapidapi_key');
        $this->rapidApiHost = config('services.currency_exchange.rapidapi_host');
        $this->baseUrl = config('services.currency_exchange.base_url');
        $this->defaultCurrency = config('services.currency_exchange.default_currency', 'USD');
        
        // Usar datos mock en desarrollo/staging
        $this->useMockData = empty($this->rapidApiKey) || 
                           str_contains($this->rapidApiKey ?? '', 'test') ||
                           config('app.env') === 'local';
    }

    /**
     * Convertir monto de una moneda a otra
     * 
     * @param float $amount Monto a convertir
     * @param string $fromCurrency Moneda origen (ej: USD)
     * @param string $toCurrency Moneda destino (ej: MXN)
     * @return array
     */
    public function convert($amount, $fromCurrency = 'USD', $toCurrency = 'MXN')
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        Log::info("Currency API: Convirtiendo {$amount} de {$fromCurrency} a {$toCurrency}");

        // Si son la misma moneda, no hay conversion
        if ($fromCurrency === $toCurrency) {
            return [
                'success' => true,
                'amount' => $amount,
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
                'converted_amount' => $amount,
                'rate' => 1.0,
                'timestamp' => now()->toIso8601String(),
            ];
        }

        if ($this->useMockData) {
            return $this->getMockConversion($amount, $fromCurrency, $toCurrency);
        }

        try {
            $cacheKey = "currency_rate_{$fromCurrency}_{$toCurrency}";
            
            // Cache de 1 hora (las tasas no cambian tan rapido)
            if (Cache::has($cacheKey)) {
                $rate = Cache::get($cacheKey);
                Log::info("Currency API: Tasa desde cache: {$rate}");
            } else {
                $rate = $this->getExchangeRate($fromCurrency, $toCurrency);
                Cache::put($cacheKey, $rate, 3600);
            }

            $convertedAmount = $amount * $rate;

            return [
                'success' => true,
                'amount' => $amount,
                'from_currency' => $fromCurrency,
                'from_symbol' => $this->getCurrencySymbol($fromCurrency),
                'to_currency' => $toCurrency,
                'to_symbol' => $this->getCurrencySymbol($toCurrency),
                'converted_amount' => round($convertedAmount, 2),
                'rate' => $rate,
                'formatted' => $this->formatCurrency($convertedAmount, $toCurrency),
                'timestamp' => now()->toIso8601String(),
            ];

        } catch (\Exception $e) {
            Log::error("Currency API Exception: " . $e->getMessage());
            return $this->getMockConversion($amount, $fromCurrency, $toCurrency);
        }
    }

    /**
     * Obtener tasa de cambio entre dos monedas
     * 
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    private function getExchangeRate($fromCurrency, $toCurrency)
    {
        try {
            $response = Http::withHeaders([
                'X-RapidAPI-Key' => $this->rapidApiKey,
                'X-RapidAPI-Host' => $this->rapidApiHost,
            ])->get($this->baseUrl . '/latest', [
                'base' => $fromCurrency,
                'symbols' => $toCurrency,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['rates'][$toCurrency])) {
                    return $data['rates'][$toCurrency];
                }
            }

            throw new \Exception("No se pudo obtener la tasa de cambio");

        } catch (\Exception $e) {
            Log::error("Error obteniendo tasa de cambio: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convertir multiples monedas a la vez
     * 
     * @param float $amount
     * @param string $fromCurrency
     * @param array $toCurrencies Array de codigos de moneda
     * @return array
     */
    public function convertMultiple($amount, $fromCurrency = 'USD', $toCurrencies = ['MXN', 'EUR', 'GBP'])
    {
        $results = [];

        foreach ($toCurrencies as $toCurrency) {
            $results[$toCurrency] = $this->convert($amount, $fromCurrency, $toCurrency);
        }

        return [
            'success' => true,
            'original_amount' => $amount,
            'from_currency' => $fromCurrency,
            'conversions' => $results,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Obtener todas las tasas de cambio actuales para una moneda base
     * 
     * @param string $baseCurrency
     * @return array
     */
    public function getAllRates($baseCurrency = 'USD')
    {
        $baseCurrency = strtoupper($baseCurrency);

        Log::info("Currency API: Obteniendo todas las tasas para {$baseCurrency}");

        if ($this->useMockData) {
            return $this->getMockAllRates($baseCurrency);
        }

        try {
            $cacheKey = "currency_all_rates_{$baseCurrency}";
            
            if (Cache::has($cacheKey)) {
                Log::info("Currency API: Tasas desde cache");
                return Cache::get($cacheKey);
            }

            $response = Http::withHeaders([
                'X-RapidAPI-Key' => $this->rapidApiKey,
                'X-RapidAPI-Host' => $this->rapidApiHost,
            ])->get($this->baseUrl . '/latest', [
                'base' => $baseCurrency,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $result = [
                    'success' => true,
                    'base_currency' => $baseCurrency,
                    'rates' => $data['rates'] ?? [],
                    'timestamp' => now()->toIso8601String(),
                ];

                Cache::put($cacheKey, $result, 3600);
                
                return $result;
            }

            return $this->getMockAllRates($baseCurrency);

        } catch (\Exception $e) {
            Log::error("Currency API Exception: " . $e->getMessage());
            return $this->getMockAllRates($baseCurrency);
        }
    }

    /**
     * Obtener lista de monedas soportadas
     * 
     * @return array
     */
    public function getSupportedCurrencies()
    {
        return [
            'success' => true,
            'currencies' => $this->supportedCurrencies,
            'total' => count($this->supportedCurrencies),
        ];
    }

    /**
     * Formatear monto con simbolo de moneda
     * 
     * @param float $amount
     * @param string $currency
     * @return string
     */
    private function formatCurrency($amount, $currency)
    {
        $symbol = $this->getCurrencySymbol($currency);
        $formatted = number_format($amount, 2, '.', ',');
        
        // Para USD, EUR, GBP el simbolo va antes
        if (in_array($currency, ['USD', 'EUR', 'GBP', 'CAD'])) {
            return $symbol . ' ' . $formatted;
        }
        
        // Para las demas, el simbolo va despues
        return $formatted . ' ' . $symbol;
    }

    /**
     * Obtener simbolo de moneda
     * 
     * @param string $currency
     * @return string
     */
    private function getCurrencySymbol($currency)
    {
        return $this->supportedCurrencies[$currency]['symbol'] ?? $currency;
    }

    /**
     * Datos mock para desarrollo/demostracion
     * Tasas realistas aproximadas
     */
    private function getMockConversion($amount, $fromCurrency, $toCurrency)
    {
        Log::info("Currency API: Usando datos MOCK para demostracion");

        // Tasas de cambio aproximadas (Nov 2025)
        $mockRates = [
            'USD_MXN' => 17.50,
            'USD_EUR' => 0.92,
            'USD_GBP' => 0.79,
            'USD_CAD' => 1.36,
            'USD_JPY' => 149.50,
            'USD_CNY' => 7.24,
            'USD_BRL' => 4.98,
            'USD_ARS' => 350.00,
            'USD_COP' => 4100.00,
            'MXN_USD' => 0.057,
            'EUR_USD' => 1.09,
            'GBP_USD' => 1.27,
        ];

        $rateKey = "{$fromCurrency}_{$toCurrency}";
        $rate = $mockRates[$rateKey] ?? 1.0;
        
        $convertedAmount = $amount * $rate;

        return [
            'success' => true,
            'amount' => $amount,
            'from_currency' => $fromCurrency,
            'from_symbol' => $this->getCurrencySymbol($fromCurrency),
            'to_currency' => $toCurrency,
            'to_symbol' => $this->getCurrencySymbol($toCurrency),
            'converted_amount' => round($convertedAmount, 2),
            'rate' => $rate,
            'formatted' => $this->formatCurrency($convertedAmount, $toCurrency),
            'timestamp' => now()->toIso8601String(),
            'using_mock_data' => true,
        ];
    }

    /**
     * Todas las tasas mock
     */
    private function getMockAllRates($baseCurrency)
    {
        $mockRates = [
            'USD' => [
                'MXN' => 17.50,
                'EUR' => 0.92,
                'GBP' => 0.79,
                'CAD' => 1.36,
                'JPY' => 149.50,
                'CNY' => 7.24,
                'BRL' => 4.98,
                'ARS' => 350.00,
                'COP' => 4100.00,
            ],
            'MXN' => [
                'USD' => 0.057,
                'EUR' => 0.052,
                'GBP' => 0.045,
            ],
            'EUR' => [
                'USD' => 1.09,
                'MXN' => 19.07,
                'GBP' => 0.86,
            ],
        ];

        return [
            'success' => true,
            'base_currency' => $baseCurrency,
            'rates' => $mockRates[$baseCurrency] ?? $mockRates['USD'],
            'timestamp' => now()->toIso8601String(),
            'using_mock_data' => true,
        ];
    }
}
