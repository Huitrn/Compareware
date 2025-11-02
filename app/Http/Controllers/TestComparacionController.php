<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periferico;
use App\Models\Comparacion;

class TestComparacionController extends Controller
{
    public function __construct()
    {
        // Sin middleware por ahora para pruebas
    }

    public function comparar(Request $request)
    {
        // Obtener parámetros
        $id1 = $request->query('periferico1');
        $id2 = $request->query('periferico2');

        // Validación básica manual
        if (!is_numeric($id1) || !is_numeric($id2) || $id1 <= 0 || $id2 <= 0) {
            return response()->json([
                'success' => false, 
                'message' => 'IDs inválidos - solo se permiten números positivos'
            ], 400);
        }

        // Validar que no sean iguales
        if ($id1 == $id2) {
            return response()->json([
                'success' => false, 
                'message' => 'Los periféricos deben ser diferentes'
            ], 400);
        }

        // Log del intento
        \Log::info('COMPARISON_TEST_REQUEST', [
            'periferico1_id' => $id1,
            'periferico2_id' => $id2,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Respuesta simplificada para testing
        return response()->json([
            'success' => true,
            'message' => 'Comparación procesada correctamente',
            'periferico1_id' => (int) $id1,
            'periferico2_id' => (int) $id2,
            'security_check' => 'PASSED',
            'timestamp' => now()
        ]);
    }
}