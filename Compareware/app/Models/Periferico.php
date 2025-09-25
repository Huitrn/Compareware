<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periferico extends Model
{

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'marca_id',
        'categoria_id',
    ];
}