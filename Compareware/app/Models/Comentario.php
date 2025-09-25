<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $fillable = [
        'periferico_id',
        'usuario',
        'texto',
    ];

    public function periferico()
    {
        return $this->belongsTo(Periferico::class);
    }
}