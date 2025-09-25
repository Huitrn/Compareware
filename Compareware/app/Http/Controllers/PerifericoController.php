<?php

namespace App\Http\Controllers;

use App\Models\Periferico;
use Illuminate\Http\Request;

class PerifericoController extends Controller
{
    // GET: Obtener todos los periféricos (solo usuarios autenticados)
    public function index()
    {
        return response()->json(Periferico::all());
    }

    // GET: Obtener un periférico por ID
    public function show($id)
    {
        return Periferico::findOrFail($id);
    }

    // POST: Crear un nuevo periférico (solo admin)
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $periferico = Periferico::create($request->all());
        return response()->json($periferico, 201);
    }

    // PUT: Actualizar un periférico existente (solo admin)
    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $periferico = Periferico::findOrFail($id);
        $periferico->update($request->all());
        return response()->json($periferico, 200);
    }

    // DELETE: Eliminar un periférico (solo admin)
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $periferico = Periferico::findOrFail($id);
        $periferico->delete();
        return response()->json(null, 204);
    }
}