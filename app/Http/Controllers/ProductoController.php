<?php

namespace App\Http\Controllers;

use App\Models\Periferico;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $productos = Periferico::all();
        $categorias = Categoria::all();

        // Elimina las llamadas a APIs externas
        // $mercadolibre = ...;
        // $clima = ...;
        // $geo = ...;

        return view('comparadora', compact('productos', 'categorias'));
    }
     // Método para mostrar periférico por ID
    public function mostrarPeriferico($id)
    {
        return response()->json([
            'mensaje' => 'Mostrando periférico',
            'id_periferico' => $id,
            'tipo' => 'periférico individual',
            'url_solicitada' => request()->fullUrl()
        ]);
    }
    
    // Método para mostrar categoría por nombre
    public function mostrarCategoria($nombre)
    {
        return response()->json([
            'mensaje' => 'Mostrando categoría',
            'nombre_categoria' => $nombre,
            'categoria_formateada' => ucfirst($nombre),
            'ruta_base' => '/categoria/' . $nombre
        ]);
    }
}