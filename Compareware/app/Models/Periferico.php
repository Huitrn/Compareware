<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periferico extends Model
{
    // Opcional: Si la tabla se llama 'perifericos', no necesitas especificar nada más.
    // protected $table = 'perifericos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'marca_id',
        'categoria_id',
        // agrega aquí otros campos de tu tabla perifericos
    ];
}