<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * SEGURIDAD: Usar $guarded en lugar de $fillable para mayor control
     * Solo permitir campos específicos y seguros
     */
    protected $guarded = [
        'id',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
        'is_admin',           // Campo protegido para seguridad
        'permissions',        // Campo protegido
        'last_login_ip',      // Campo de auditoría protegido
        'failed_login_attempts' // Campo de seguridad protegido
    ];

    /**
     * Campos que pueden ser asignados masivamente (whitelist específica)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',  // Agregar para sistema de roles con foreign key
        'is_suspended'
    ];

    /**
     * Campos ocultos en serialización
     */
    protected $hidden = [
        'password',
        'remember_token',
        'failed_login_attempts',
        'last_login_ip',
    ];

    /**
     * Casteo de tipos para seguridad
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'failed_login_attempts' => 'integer'
        ];
    }

    /**
     * VALIDACIÓN DE ROLES PERMITIDOS
     */
    protected $allowedRoles = ['admin', 'user', 'moderator'];

    /**
     * Mutator seguro para name - sanitizar input
     */
    public function setNameAttribute($value): void
    {
        // Sanitizar y validar nombre
        $sanitized = $this->sanitizeName($value);
        $this->attributes['name'] = $sanitized;
    }

    /**
     * Mutator seguro para email - validar formato
     */
    public function setEmailAttribute($value): void
    {
        // Convertir a minúsculas y sanitizar
        $sanitized = strtolower(trim($value));
        
        // Validación adicional de seguridad
        if (!filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Formato de email inválido');
        }
        
        $this->attributes['email'] = $sanitized;
    }

    /**
     * Mutator seguro para role - validar roles permitidos
     */
    /**
     * Mutator seguro para role - validar roles permitidos
     */
    public function setRoleAttribute($value): void
    {
        $role = strtolower(trim($value));
        
        if (!in_array($role, $this->allowedRoles)) {
            Log::channel('security')->warning('Intento de asignar rol inválido', [
                'attempted_role' => $value,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // Asignar role por defecto en lugar de fallar
            $role = 'user';
        }
        
        $this->attributes['role'] = $role;
    }

    /**
     * Mutator seguro para password - hash automático
     */
    public function setPasswordAttribute($value): void
    {
        // Si ya está hasheado, no volver a hashear
        if (password_get_info($value)['algo'] !== null) {
            $this->attributes['password'] = $value;
            return;
        }
        
        // Validar longitud mínima
        if (strlen($value) < 8) {
            throw new \InvalidArgumentException('Password debe tener al menos 8 caracteres');
        }
        
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Sanitizar nombre de usuario
     */
    private function sanitizeName($name): string
    {
        // Remover caracteres peligrosos
        $sanitized = preg_replace('/[^\p{L}\p{N}\s\-\'\.]/u', '', $name);
        
        // Limitar longitud
        $sanitized = substr(trim($sanitized), 0, 50);
        
        // Verificar que no contenga patrones SQL
        $sqlPatterns = [
            '/\bSELECT\b/i', '/\bINSERT\b/i', '/\bUPDATE\b/i', 
            '/\bDELETE\b/i', '/\bDROP\b/i', '/\bUNION\b/i'
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $sanitized)) {
                throw new \InvalidArgumentException('Nombre contiene caracteres no permitidos');
            }
        }
        
        return $sanitized;
    }

    /**
     * Scope para usuarios activos solamente
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para roles específicos
     */
    public function scopeWithRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin(): bool
    {
        return $this->userRole && $this->userRole->slug === 'administrador';
    }

    /**
     * Verificar si el usuario puede realizar una acción
     */
    public function can($ability, $arguments = []): bool
    {
        // Implementar lógica de permisos personalizada
        if ($this->isAdmin()) {
            return true; // Admin puede todo
        }
        
        return parent::can($ability, $arguments);
    }

    /**
     * Override del método save para logging de seguridad
     */
    public function save(array $options = []): bool
    {
        $isDirty = $this->isDirty();
        $changes = $this->getDirty();
        
        // Log de cambios sensibles
        if ($isDirty && (
            array_key_exists('role', $changes) || 
            array_key_exists('email', $changes) || 
            array_key_exists('password', $changes)
        )) {
            Log::channel('security')->info('User Model Changed', [
                'user_id' => $this->id,
                'changed_fields' => array_keys($changes),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString()
            ]);
        }
        
        return parent::save($options);
    }

    /**
     * Registrar último login
     */
    public function recordLogin(): void
    {
        $this->timestamps = false; // No actualizar updated_at
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'failed_login_attempts' => 0 // Reset intentos fallidos
        ]);
        $this->timestamps = true;
    }

    /**
     * Incrementar intentos de login fallidos
     */
    public function incrementFailedAttempts(): void
    {
        $this->timestamps = false;
        $this->increment('failed_login_attempts');
        $this->timestamps = true;
    }

    /**
     * Verificar si la cuenta está bloqueada por intentos fallidos
     */
    public function isLocked(): bool
    {
        return $this->failed_login_attempts >= 5;
    }

    /**
     * Relación con el rol (tabla roles)
     */
    public function userRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->userRole) {
            return false;
        }

        return $this->userRole->hasPermission($permission);
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->userRole) {
            return false;
        }

        return $this->userRole->hasAnyPermission($permissions);
    }

    /**
     * Verificar si el usuario tiene todos los permisos
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (!$this->userRole) {
            return false;
        }

        return $this->userRole->hasAllPermissions($permissions);
    }

    /**
     * Obtener nombre del rol
     */
    public function getRoleName(): string
    {
        return $this->userRole ? $this->userRole->nombre : 'Sin rol';
    }

    /**
     * Verificar si es Supervisor
     */
    public function isSupervisor(): bool
    {
        $roleName = strtolower($this->getRoleName());
        return in_array($roleName, ['supervisor', 'supervisión']);
    }

    /**
     * Verificar si es Desarrollador
     */
    public function isDeveloper(): bool
    {
        $roleName = strtolower($this->getRoleName());
        return in_array($roleName, ['developer', 'desarrollador', 'dev']);
    }

    /**
     * Verificar si tiene acceso a gestión de usuarios
     * Admin: acceso total | Supervisor: solo ver/editar | Developer: sin acceso
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin() || $this->isSupervisor();
    }

    /**
     * Verificar si puede eliminar usuarios (solo Admin)
     */
    public function canDeleteUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verificar si puede cambiar roles (solo Admin)
     */
    public function canChangeRoles(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verificar si tiene acceso a gestión de productos
     * Admin: acceso total | Supervisor: aprobar/editar | Developer: sin acceso
     */
    public function canManageProducts(): bool
    {
        return $this->isAdmin() || $this->isSupervisor();
    }

    /**
     * Verificar si puede eliminar productos (solo Admin)
     */
    public function canDeleteProducts(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verificar si tiene acceso a herramientas de desarrollo
     * Admin: sí | Supervisor: no | Developer: sí
     */
    public function canAccessDevTools(): bool
    {
        return $this->isAdmin() || $this->isDeveloper();
    }

    /**
     * Verificar si puede ver estadísticas y reportes
     * Admin: sí | Supervisor: sí | Developer: limitado
     */
    public function canViewReports(): bool
    {
        return $this->isAdmin() || $this->isSupervisor();
    }

    /**
     * Verificar si puede gestionar configuraciones del sistema (solo Admin)
     */
    public function canManageSettings(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Obtener permisos detallados del usuario según su rol
     */
    public function getDetailedPermissions(): array
    {
        if ($this->isAdmin()) {
            return [
                'users' => ['view', 'create', 'edit', 'delete', 'change_role', 'suspend'],
                'products' => ['view', 'create', 'edit', 'delete', 'approve'],
                'categories' => ['view', 'create', 'edit', 'delete'],
                'brands' => ['view', 'create', 'edit', 'delete'],
                'settings' => ['view', 'edit'],
                'logs' => ['view', 'clear'],
                'reports' => ['view', 'export'],
                'dev_tools' => ['access', 'cache', 'debug']
            ];
        }

        if ($this->isSupervisor()) {
            return [
                'users' => ['view', 'edit'],
                'products' => ['view', 'edit', 'approve'],
                'categories' => ['view', 'create', 'edit'],
                'brands' => ['view', 'create', 'edit'],
                'settings' => ['view'],
                'logs' => ['view'],
                'reports' => ['view', 'export'],
                'dev_tools' => []
            ];
        }

        if ($this->isDeveloper()) {
            return [
                'users' => [],
                'products' => [],
                'categories' => [],
                'brands' => [],
                'settings' => ['view'],
                'logs' => ['view', 'clear'],
                'reports' => ['view'],
                'dev_tools' => ['access', 'cache', 'debug', 'api_test']
            ];
        }

        // Usuario normal
        return [
            'users' => [],
            'products' => ['view'],
            'categories' => ['view'],
            'brands' => ['view'],
            'settings' => [],
            'logs' => [],
            'reports' => [],
            'dev_tools' => []
        ];
    }

    /**
     * Enviar notificación de restablecimiento de contraseña
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}