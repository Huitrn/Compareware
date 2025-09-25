<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periferico;
use App\Models\Categoria;
use App\Models\Comentario;

class ApiPerifericoController extends Controller
{
    // 1. Listado de periféricos con filtros opcionales
    public function index(Request $request)
    {
        $query = Periferico::query();

        if ($request->has('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }
        if ($request->has('marca')) {
            $query->where('marca', $request->marca);
        }
        // Puedes agregar más filtros aquí

        return response()->json($query->get());
    }

    // 2. Detalle de periférico
    public function show($id)
    {
        $periferico = Periferico::find($id);
        if (!$periferico) {
            return response()->json(['error' => 'No encontrado'], 404);
        }
        return response()->json($periferico);
    }

    // 3. Comparación de periféricos
    public function comparar(Request $request)
    {
        $id1 = $request->get('periferico1');
        $id2 = $request->get('periferico2');
        $p1 = Periferico::find($id1);
        $p2 = Periferico::find($id2);

        if (!$p1 || !$p2) {
            return response()->json(['error' => 'Periféricos no encontrados'], 404);
        }

        // Aquí puedes agregar lógica de comparación personalizada
        $comparacion = "Comparación básica entre {$p1->nombre} y {$p2->nombre}.";

        return response()->json([
            'periferico1' => $p1,
            'periferico2' => $p2,
            'comparacion' => $comparacion
        ]);
    }

    // 4. Listado de categorías
    public function categorias()
    {
        return response()->json(Categoria::all());
    }

    // 5. Crear comentario/reseña
    public function comentar(Request $request, $id)
    {
        $periferico = Periferico::find($id);
        if (!$periferico) {
            return response()->json(['error' => 'No encontrado'], 404);
        }

        $comentario = new Comentario();
        $comentario->periferico_id = $id;
        $comentario->usuario = $request->usuario ?? 'Anónimo';
        $comentario->texto = $request->texto;
        $comentario->save();

        return response()->json($comentario, 201);
    }
}
