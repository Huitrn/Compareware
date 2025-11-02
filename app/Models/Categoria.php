<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    /**
     * Campos protegidos contra mass assignment
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'nombre',
        'descripcion',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Relación con periféricos
     */
    public function perifericos(): HasMany
    {
        return $this->hasMany(Periferico::class);
    }

    /**
     * Scope para categorías activas
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
