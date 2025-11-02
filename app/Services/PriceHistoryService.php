<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PriceHistoryService
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        // En un entorno real, estos vendrían de config/services.php
        $this->baseUrl = 'https://price-tracker-api.rapidapi.com';
        $this->apiKey = config('services.rapidapi.key', env('RAPIDAPI_KEY'));
    }

    /**
     * Obtener historial de precios para un producto
     */
    public function getPriceHistory($productName, $days = 90)
    {
        try {
            Log::info("📈 Obteniendo historial de precios para: {$productName}");

            // Por ahora simulamos datos realistas de historial de precios
            // En producción, aquí haríamos la llamada real a la API
            return $this->generateRealisticPriceHistory($productName, $days);

        } catch (\Exception $e) {
            Log::error("❌ Error al obtener historial de precios: " . $e->getMessage());
            return $this->generateFallbackPriceHistory($productName, $days);
        }
    }

    /**
     * Obtener tendencias y análisis de precios
     */
    public function getPriceTrends($productName)
    {
        try {
            $history = $this->getPriceHistory($productName, 90);
            
            if (!$history['success']) {
                return ['success' => false, 'message' => 'No se pudo obtener el historial'];
            }

            return $this->analyzePriceTrends($history['data'], $productName);

        } catch (\Exception $e) {
            Log::error("❌ Error al analizar tendencias de precios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en análisis de tendencias'];
        }
    }

    /**
     * Generar historial de precios realista basado en el tipo de producto
     */
    private function generateRealisticPriceHistory($productName, $days)
    {
        $productType = $this->detectProductType($productName);
        $brand = $this->extractBrand($productName);
        $isPremium = in_array(strtolower($brand), ['sony', 'bose', 'apple', 'corsair', 'razer', 'logitech']);

        // Precio base según tipo de producto y marca
        $basePrice = $this->getBasePrice($productType, $isPremium);
        
        $priceHistory = [];
        $currentDate = Carbon::now();
        
        // Generar datos de precios para los últimos X días
        for ($i = $days; $i >= 0; $i--) {
            $date = $currentDate->copy()->subDays($i);
            $price = $this->calculateDayPrice($basePrice, $i, $days, $productType);
            
            $priceHistory[] = [
                'date' => $date->format('Y-m-d'),
                'price' => round($price, 2),
                'store' => $this->getRandomStore(),
                'currency' => 'USD',
                'availability' => $this->getAvailabilityStatus($i, $days)
            ];
        }

        return [
            'success' => true,
            'product_name' => $productName,
            'price_history' => $priceHistory,
            'period_days' => $days,
            'current_price' => end($priceHistory)['price'],
            'source' => 'Price History Simulator',
            'last_updated' => Carbon::now()->toISOString()
        ];
    }

    /**
     * Analizar tendencias de precios
     */
    private function analyzePriceTrends($priceData, $productName)
    {
        $prices = array_column($priceData['price_history'], 'price');
        $currentPrice = end($prices);
        $initialPrice = reset($prices);
        
        // Calcular estadísticas
        $minPrice = min($prices);
        $maxPrice = max($prices);
        $avgPrice = array_sum($prices) / count($prices);
        
        // Calcular tendencia
        $priceChange = $currentPrice - $initialPrice;
        $percentageChange = ($priceChange / $initialPrice) * 100;
        
        // Tendencia reciente (últimos 7 días)
        $recentPrices = array_slice($prices, -7);
        $recentTrend = $this->calculateTrend($recentPrices);
        
        // Predicción simple basada en tendencia
        $prediction = $this->predictNextPrice($prices, $currentPrice);
        
        // Recomendación de compra
        $buyRecommendation = $this->getBuyRecommendation($currentPrice, $minPrice, $maxPrice, $avgPrice, $recentTrend);

        return [
            'success' => true,
            'product_name' => $productName,
            'analysis' => [
                'current_price' => $currentPrice,
                'price_statistics' => [
                    'minimum_price' => $minPrice,
                    'maximum_price' => $maxPrice,
                    'average_price' => round($avgPrice, 2),
                    'price_range' => $maxPrice - $minPrice,
                ],
                'trend_analysis' => [
                    'overall_change' => round($priceChange, 2),
                    'percentage_change' => round($percentageChange, 2),
                    'trend_direction' => $percentageChange > 0 ? 'increasing' : 'decreasing',
                    'recent_trend' => $recentTrend,
                    'volatility' => $this->calculateVolatility($prices),
                ],
                'predictions' => [
                    'next_week_estimate' => $prediction['next_week'],
                    'next_month_estimate' => $prediction['next_month'],
                    'confidence_level' => $prediction['confidence'],
                ],
                'buy_recommendation' => $buyRecommendation,
                'price_alerts' => [
                    'is_good_deal' => $currentPrice <= ($minPrice + ($maxPrice - $minPrice) * 0.3),
                    'is_overpriced' => $currentPrice >= ($minPrice + ($maxPrice - $minPrice) * 0.8),
                    'savings_potential' => round($maxPrice - $currentPrice, 2),
                ]
            ],
            'timestamps' => [
                'period_start' => reset($priceData['price_history'])['date'],
                'period_end' => end($priceData['price_history'])['date'],
                'analysis_date' => Carbon::now()->toISOString()
            ]
        ];
    }

    private function detectProductType($productName)
    {
        $name = strtolower($productName);
        
        if (strpos($name, 'headphone') !== false || strpos($name, 'auricular') !== false) return 'headphones';
        if (strpos($name, 'mouse') !== false) return 'mouse';
        if (strpos($name, 'keyboard') !== false || strpos($name, 'teclado') !== false) return 'keyboard';
        if (strpos($name, 'microphone') !== false || strpos($name, 'micrófono') !== false) return 'microphone';
        
        return 'electronics';
    }

    private function extractBrand($productName)
    {
        $brands = [
            'Sony', 'Bose', 'Apple', 'Samsung', 'JBL', 'Sennheiser', 'Audio-Technica', 
            'Corsair', 'Razer', 'Logitech', 'SteelSeries', 'HyperX', 'ASUS'
        ];
        
        foreach ($brands as $brand) {
            if (stripos($productName, $brand) !== false) {
                return $brand;
            }
        }
        
        return 'Generic';
    }

    private function getBasePrice($productType, $isPremium)
    {
        $basePrices = [
            'headphones' => $isPremium ? 299 : 79,
            'mouse' => $isPremium ? 129 : 39,
            'keyboard' => $isPremium ? 179 : 59,
            'microphone' => $isPremium ? 199 : 49,
            'electronics' => $isPremium ? 199 : 89
        ];

        return $basePrices[$productType] ?? 99;
    }

    private function calculateDayPrice($basePrice, $daysAgo, $totalDays, $productType)
    {
        // Simular fluctuaciones realistas de precio
        $seasonality = sin(($daysAgo / $totalDays) * 2 * pi()) * 0.1; // ±10% variación estacional
        $randomFluctuation = (mt_rand(-15, 15) / 100); // ±15% fluctuación aleatoria
        $trendFactor = ($daysAgo / $totalDays) * 0.05; // Tendencia ligera hacia abajo con el tiempo
        
        // Eventos especiales (Black Friday, ofertas)
        $specialEvents = $this->getSpecialEventMultiplier($daysAgo);
        
        $multiplier = 1 + $seasonality + $randomFluctuation - $trendFactor + $specialEvents;
        
        return $basePrice * $multiplier;
    }

    private function getSpecialEventMultiplier($daysAgo)
    {
        // Simular ofertas especiales
        if ($daysAgo >= 25 && $daysAgo <= 30) return -0.25; // Black Friday hace ~4 semanas
        if ($daysAgo >= 60 && $daysAgo <= 65) return -0.15; // Oferta de verano
        if ($daysAgo >= 10 && $daysAgo <= 15) return -0.10; // Oferta reciente
        
        return 0;
    }

    private function getRandomStore()
    {
        $stores = ['Amazon', 'Best Buy', 'Newegg', 'B&H', 'Walmart', 'Target'];
        return $stores[array_rand($stores)];
    }

    private function getAvailabilityStatus($daysAgo, $totalDays)
    {
        // Simular disponibilidad (más probable que esté disponible recientemente)
        $availabilityChance = 1 - ($daysAgo / $totalDays) * 0.3;
        return mt_rand(0, 100) / 100 < $availabilityChance ? 'in_stock' : 'out_of_stock';
    }

    private function calculateTrend($prices)
    {
        if (count($prices) < 2) return 'stable';
        
        $firstHalf = array_slice($prices, 0, ceil(count($prices) / 2));
        $secondHalf = array_slice($prices, floor(count($prices) / 2));
        
        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);
        
        $difference = ($secondAvg - $firstAvg) / $firstAvg;
        
        if ($difference > 0.02) return 'increasing';
        if ($difference < -0.02) return 'decreasing';
        return 'stable';
    }

    private function calculateVolatility($prices)
    {
        if (count($prices) < 2) return 0;
        
        $mean = array_sum($prices) / count($prices);
        $squaredDiffs = array_map(function($price) use ($mean) {
            return pow($price - $mean, 2);
        }, $prices);
        
        $variance = array_sum($squaredDiffs) / count($prices);
        $stdDev = sqrt($variance);
        
        // Normalizar volatilidad como porcentaje
        return round(($stdDev / $mean) * 100, 2);
    }

    private function predictNextPrice($prices, $currentPrice)
    {
        // Predicción simple basada en tendencia lineal
        $recentPrices = array_slice($prices, -14); // Últimas 2 semanas
        
        if (count($recentPrices) < 2) {
            return [
                'next_week' => $currentPrice,
                'next_month' => $currentPrice,
                'confidence' => 'low'
            ];
        }
        
        // Calcular tendencia lineal simple
        $n = count($recentPrices);
        $sumX = ($n * ($n + 1)) / 2;
        $sumY = array_sum($recentPrices);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $recentPrices[$i];
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        
        return [
            'next_week' => round($currentPrice + ($slope * 7), 2),
            'next_month' => round($currentPrice + ($slope * 30), 2),
            'confidence' => abs($slope) > 1 ? 'medium' : 'high'
        ];
    }

    private function getBuyRecommendation($currentPrice, $minPrice, $maxPrice, $avgPrice, $recentTrend)
    {
        $pricePosition = ($currentPrice - $minPrice) / ($maxPrice - $minPrice);
        
        if ($pricePosition <= 0.25 && $recentTrend !== 'increasing') {
            return [
                'recommendation' => 'buy_now',
                'confidence' => 'high',
                'reason' => 'Precio cerca del mínimo histórico y tendencia estable/descendente'
            ];
        }
        
        if ($pricePosition <= 0.40) {
            return [
                'recommendation' => 'good_deal',
                'confidence' => 'medium',
                'reason' => 'Precio por debajo del promedio histórico'
            ];
        }
        
        if ($pricePosition >= 0.80 || $recentTrend === 'increasing') {
            return [
                'recommendation' => 'wait',
                'confidence' => 'high',
                'reason' => 'Precio alto comparado con historial, considera esperar'
            ];
        }
        
        return [
            'recommendation' => 'neutral',
            'confidence' => 'medium',
            'reason' => 'Precio dentro del rango normal, decisión personal'
        ];
    }

    /**
     * Generar datos de fallback en caso de error
     */
    private function generateFallbackPriceHistory($productName, $days)
    {
        Log::warning("⚠️ Generando datos de fallback para historial de precios: {$productName}");
        
        $basePrice = 99.99;
        $priceHistory = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $variation = mt_rand(-10, 10) / 100; // ±10% variación
            $price = $basePrice * (1 + $variation);
            
            $priceHistory[] = [
                'date' => $date->format('Y-m-d'),
                'price' => round($price, 2),
                'store' => 'Estimated',
                'currency' => 'USD',
                'availability' => 'unknown'
            ];
        }

        return [
            'success' => true,
            'product_name' => $productName,
            'price_history' => $priceHistory,
            'period_days' => $days,
            'current_price' => end($priceHistory)['price'],
            'source' => 'Fallback Price Generator',
            'last_updated' => Carbon::now()->toISOString(),
            'is_fallback' => true
        ];
    }
}