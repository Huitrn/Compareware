<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComparisonHistory extends Model
{
    protected $table = 'comparisons_history';

    protected $fillable = [
        'user_id',
        'periferico1_id',
        'periferico2_id',
        'comparison_data',
        'session_id',
        'ip_address',
    ];

    protected $casts = [
        'comparison_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Usuario que realizó la comparación
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Primer periférico comparado
     */
    public function periferico1(): BelongsTo
    {
        return $this->belongsTo(Periferico::class, 'periferico1_id');
    }

    /**
     * Segundo periférico comparado
     */
    public function periferico2(): BelongsTo
    {
        return $this->belongsTo(Periferico::class, 'periferico2_id');
    }

    /**
     * Obtener ambos periféricos de una vez
     */
    public function perifericos()
    {
        return [
            'periferico1' => $this->periferico1,
            'periferico2' => $this->periferico2,
        ];
    }

    /**
     * Scope para obtener comparaciones de un usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para obtener comparaciones recientes
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Formatear la fecha de manera amigable
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
