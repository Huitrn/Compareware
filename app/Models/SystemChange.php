<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_type',
        'model_type',
        'model_id',
        'description',
        'changes',
        'ip_address',
        'user_agent',
        'notified',
    ];

    protected $casts = [
        'changes' => 'array',
        'notified' => 'boolean',
    ];

    /**
     * Relación con el usuario que realizó el cambio
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el modelo relacionado
     */
    public function model()
    {
        if ($this->model_type && $this->model_id) {
            $modelClass = "App\\Models\\{$this->model_type}";
            if (class_exists($modelClass)) {
                return $modelClass::find($this->model_id);
            }
        }
        return null;
    }

    /**
     * Registrar un cambio en el sistema
     */
    public static function logChange(
        string $actionType,
        string $modelType,
        $modelId,
        string $description,
        array $changes = null,
        $userId = null
    ) {
        $userId = $userId ?? auth()->id();
        
        return self::create([
            'user_id' => $userId,
            'action_type' => $actionType,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'notified' => false,
        ]);
    }

    /**
     * Marcar como notificado
     */
    public function markAsNotified()
    {
        $this->update(['notified' => true]);
    }

    /**
     * Scope para cambios no notificados
     */
    public function scopeNotNotified($query)
    {
        return $query->where('notified', false);
    }

    /**
     * Scope para cambios recientes (últimas 24 horas)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }
}
