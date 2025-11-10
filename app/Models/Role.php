<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'permisos',
        'is_active'
    ];

    protected $casts = [
        'permisos' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Usuarios que tienen este rol
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Verificar si el rol tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permisos) {
            return false;
        }

        return in_array($permission, $this->permisos);
    }

    /**
     * Verificar si el rol tiene alguno de los permisos dados
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->permisos) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (in_array($permission, $this->permisos)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si el rol tiene todos los permisos dados
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (!$this->permisos) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!in_array($permission, $this->permisos)) {
                return false;
            }
        }

        return true;
    }
}
