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

        // Buscar los periféricos con Eloquent para obtener accessors
        $periferico1 = Periferico::with(['marca', 'categoria'])
            ->find($id1);
            
        $periferico2 = Periferico::with(['marca', 'categoria'])
            ->find($id2);
        
        // Forzar que se incluyan los accessors
        if ($periferico1) {
            $periferico1->append('imagen_url_completa', 'thumbnail_url_completa');
        }
        if ($periferico2) {
            $periferico2->append('imagen_url_completa', 'thumbnail_url_completa');
        }

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
    
    /**
     * Obtener comparación con imágenes incluidas
     * Endpoint: GET /api/comparacion/{id}/with-images
     */
    public function getComparisonWithImages($id)
    {
        $comparacion = Comparacion::with(['periferico1', 'periferico2'])->find($id);
        
        if (!$comparacion) {
            return response()->json([
                'success' => false,
                'message' => 'Comparación no encontrada'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'comparacion' => [
                'id' => $comparacion->id,
                'descripcion' => $comparacion->descripcion,
                'ganador' => $comparacion->ganador
            ],
            'productos' => [
                [
                    'id' => $comparacion->periferico1->id,
                    'nombre' => $comparacion->periferico1->nombre,
                    'precio' => $comparacion->periferico1->precio,
                    'imagen_data' => $comparacion->periferico1->imagen_data
                ],
                [
                    'id' => $comparacion->periferico2->id,
                    'nombre' => $comparacion->periferico2->nombre,
                    'precio' => $comparacion->periferico2->precio,
                    'imagen_data' => $comparacion->periferico2->imagen_data
                ]
            ]
        ]);
    }
    
    /**
     * Comparar dos productos con sus imágenes
     * Endpoint: GET /api/comparacion/compare-products?periferico1=X&periferico2=Y
     */
    public function compareProductsWithImages(Request $request)
    {
        $id1 = $request->input('periferico1');
        $id2 = $request->input('periferico2');
        
        if (!$id1 || !$id2) {
            return response()->json([
                'success' => false,
                'message' => 'Debe proporcionar ambos IDs de periféricos'
            ], 400);
        }
        
        $periferico1 = Periferico::with(['marca', 'categoria'])->find($id1);
        $periferico2 = Periferico::with(['marca', 'categoria'])->find($id2);
        
        if (!$periferico1 || !$periferico2) {
            return response()->json([
                'success' => false,
                'message' => 'Uno o ambos periféricos no encontrados'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'periferico1' => [
                'id' => $periferico1->id,
                'nombre' => $periferico1->nombre,
                'modelo' => $periferico1->modelo,
                'precio' => $periferico1->precio,
                'marca' => $periferico1->marca?->nombre,
                'categoria' => $periferico1->categoria?->nombre,
                'tipo_conectividad' => $periferico1->tipo_conectividad,
                'imagen_data' => $periferico1->imagen_data,
                'tiene_imagen' => $periferico1->hasImage(),
                'tiene_galeria' => $periferico1->hasGallery()
            ],
            'periferico2' => [
                'id' => $periferico2->id,
                'nombre' => $periferico2->nombre,
                'modelo' => $periferico2->modelo,
                'precio' => $periferico2->precio,
                'marca' => $periferico2->marca?->nombre,
                'categoria' => $periferico2->categoria?->nombre,
                'tipo_conectividad' => $periferico2->tipo_conectividad,
                'imagen_data' => $periferico2->imagen_data,
                'tiene_imagen' => $periferico2->hasImage(),
                'tiene_galeria' => $periferico2->hasGallery()
            ]
        ]);
    }
}
