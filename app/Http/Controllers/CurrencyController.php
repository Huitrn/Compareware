<?php

namespace App\Http\Controllers;

use App\Services\CurrencyExchangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestion de conversion de monedas
 */
class CurrencyController extends Controller
{
    private $currencyService;

    public function __construct(CurrencyExchangeService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Convertir monto entre dos monedas
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function convert(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'from_currency' => 'required|string|size:3',
            'to_currency' => 'required|string|size:3',
        ]);

        try {
            $result = $this->currencyService->convert(
                $validated['amount'],
                $validated['from_currency'],
                $validated['to_currency']
            );

            return response()->json($result, 200);

        } catch (\Exception $e) {
            Log::error("Error en CurrencyController::convert", [
                'error' => $e->getMessage(),
                'amount' => $validated['amount'],
                'from' => $validated['from_currency'],
                'to' => $validated['to_currency'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al convertir moneda',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    /**
     * Convertir a multiples monedas
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function convertMultiple(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'from_currency' => 'required|string|size:3',
            'to_currencies' => 'required|array|min:1',
            'to_currencies.*' => 'string|size:3',
        ]);

        try {
            $result = $this->currencyService->convertMultiple(
                $validated['amount'],
                $validated['from_currency'],
                $validated['to_currencies']
            );

            return response()->json($result, 200);

        } catch (\Exception $e) {
            Log::error("Error en CurrencyController::convertMultiple", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al convertir monedas',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    /**
     * Obtener todas las tasas de cambio para una moneda base
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRates(Request $request)
    {
        $validated = $request->validate([
            'base_currency' => 'nullable|string|size:3',
        ]);

        $baseCurrency = $validated['base_currency'] ?? 'USD';

        try {
            $result = $this->currencyService->getAllRates($baseCurrency);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            Log::error("Error en CurrencyController::getRates", [
                'error' => $e->getMessage(),
                'base' => $baseCurrency,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tasas de cambio',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    /**
     * Obtener lista de monedas soportadas
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupportedCurrencies()
    {
        try {
            $result = $this->currencyService->getSupportedCurrencies();

            return response()->json($result, 200);

        } catch (\Exception $e) {
            Log::error("Error en CurrencyController::getSupportedCurrencies", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener monedas soportadas',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno',
            ], 500);
        }
    }

    /**
     * Vista de prueba para conversion de monedas
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function testView(Request $request)
    {
        $amount = $request->get('amount', 100);
        $fromCurrency = $request->get('from', 'USD');
        $toCurrency = $request->get('to', 'MXN');
        
        try {
            $conversion = $this->currencyService->convert($amount, $fromCurrency, $toCurrency);
            $supportedCurrencies = $this->currencyService->getSupportedCurrencies();
            
            return view('currency.test', [
                'amount' => $amount,
                'fromCurrency' => $fromCurrency,
                'toCurrency' => $toCurrency,
                'conversion' => $conversion,
                'currencies' => $supportedCurrencies['currencies'],
            ]);

        } catch (\Exception $e) {
            return view('currency.test', [
                'amount' => $amount,
                'fromCurrency' => $fromCurrency,
                'toCurrency' => $toCurrency,
                'conversion' => ['success' => false, 'message' => $e->getMessage()],
                'currencies' => [],
            ]);
        }
    }
}
