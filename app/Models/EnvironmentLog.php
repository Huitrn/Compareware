<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class EnvironmentLog extends Model
{
    protected $fillable = [
        'environment',
        'action',
        'description',
        'data',
        'ip_address',
        'user_agent',
        'session_id'
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime'
    ];

    // Usar solo created_at, no updated_at
    const UPDATED_AT = null;

    /**
     * Registrar una acción en el ambiente actual
     */
    public static function logAction(string $action, string $description = null, array $data = []): self
    {
        return self::create([
            'environment' => app()->environment(),
            'action' => $action,
            'description' => $description,
            'data' => $data,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'session_id' => session()->getId()
        ]);
    }

    /**
     * Obtener logs por ambiente
     */
    public static function getByEnvironment(string $environment, int $limit = 50)
    {
        return self::where('environment', $environment)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Estadísticas por ambiente
     */
    public static function getEnvironmentStats()
    {
        return self::selectRaw('environment, COUNT(*) as total, MAX(created_at) as last_activity')
            ->groupBy('environment')
            ->get()
            ->keyBy('environment');
    }
}