<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Periferico;
use App\Models\Comparacion;

class ComparacionController extends Controller
{

    public function comparar(\Illuminate\Http\Request $request)
    {
        // Obtener IDs desde GET o POST
        $id1 = $request->input('periferico1');
        $id2 = $request->input('periferico2');
        
        // Validar que los IDs sean enteros válidos
        if (!$id1 || !$id2 || !is_numeric($id1) || !is_numeric($id2)) {
            return response()->json(['success' => false, 'message' => 'IDs de periféricos inválidos']);
        }
        
        $id1 = (int)$id1;
        $id2 = (int)$id2;

        // Log de actividad de comparación
        \Log::info('COMPARISON_REQUEST', [
            'periferico1_id' => $id1,
            'periferico2_id' => $id2,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Buscar los periféricos con información completa
        $periferico1 = DB::table('perifericos as p')
            ->leftJoin('marcas as m', 'p.marca_id', '=', 'm.id')
            ->leftJoin('categorias as c', 'p.categoria_id', '=', 'c.id')
            ->select(
                'p.id', 'p.nombre', 'p.modelo', 'p.precio', 'p.tipo_conectividad',
                'm.nombre as marca_nombre',
                'c.nombre as categoria_nombre'
            )
            ->where('p.id', $id1)
            ->first();
            
        $periferico2 = DB::table('perifericos as p')
            ->leftJoin('marcas as m', 'p.marca_id', '=', 'm.id')
            ->leftJoin('categorias as c', 'p.categoria_id', '=', 'c.id')
            ->select(
                'p.id', 'p.nombre', 'p.modelo', 'p.precio', 'p.tipo_conectividad',
                'm.nombre as marca_nombre',
                'c.nombre as categoria_nombre'
            )
            ->where('p.id', $id2)
            ->first();

        if (!$periferico1 || !$periferico2) {
            return response()->json(['success' => false, 'message' => 'Periféricos no encontrados']);
        }

        // Buscar la comparación en la tabla comparaciones
        $comparacion = Comparacion::where(function($q) use ($id1, $id2) {
            $q->where('periferico1_id', $id1)->where('periferico2_id', $id2);
        })->orWhere(function($q) use ($id1, $id2) {
            $q->where('periferico1_id', $id2)->where('periferico2_id', $id1);
        })->first();

        return response()->json([
            'success' => true,
            'periferico1' => $periferico1,
            'periferico2' => $periferico2,
            'comparacion' => $comparacion ? $comparacion->descripcion : null
        ]);
    }
     public function compararDosProductos($id1, $id2)
    {
        return response()->json([
            'mensaje' => 'Comparando dos productos',
            'producto_1' => $id1,
            'producto_2' => $id2,
            'tipo_comparacion' => 'directa',
            'url_comparacion' => "/comparar/{$id1}/{$id2}"
        ]);
    }
}
