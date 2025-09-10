<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periferico;
use App\Models\Comparacion;

class ComparacionController extends Controller
{
    public function comparar(Request $request)
    {
        $id1 = $request->query('periferico1');
        $id2 = $request->query('periferico2');

        // Buscar los periféricos
        $periferico1 = Periferico::find($id1);
        $periferico2 = Periferico::find($id2);

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
}
