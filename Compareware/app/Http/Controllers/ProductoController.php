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
}