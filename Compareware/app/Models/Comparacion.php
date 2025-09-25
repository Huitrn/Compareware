<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comparacion extends Model
{
    protected $table = 'comparaciones';

    protected $fillable = [
        'periferico1_id',
        'periferico2_id',
        'descripcion'
    ];
}