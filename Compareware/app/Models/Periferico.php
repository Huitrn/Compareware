<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Periferico extends Model
{
    use HasFactory;

    /**
     * SEGURIDAD: Campos protegidos contra mass assignment
     */
    protected $guarded = [
        'id',
        'created_at', 
        'updated_at',
        'created_by',     // Campo de auditoría
        'approved_at',    // Campo de aprobación
        'is_featured',    // Campo administrativo
        'admin_notes'     // Notas internas
    ];

    /**
     * Whitelist específica de campos permitidos
     */
    protected $fillable = [
        'nombre',
        'descripcion', 
        'precio',
        'marca_id',
        'categoria_id',
        'imagen_url',
        'especificaciones',
        'is_active'
    ];

    /**
     * Casteo de tipos para seguridad
     */
    protected $casts = [
        'precio' => 'decimal:2',
        'especificaciones' => 'json',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Atributos ocultos en serialización
     */
    protected $hidden = [
        'admin_notes',
        'created_by'
    ];

    /**
     * Mutator seguro para nombre - sanitización y validación
     */
    public function setNombreAttribute($value): void
    {
        $sanitized = $this->sanitizeString($value, 100);
        
        // Validaciones de seguridad específicas
        if ($this->containsSQLPatterns($sanitized)) {
            throw new \InvalidArgumentException('Nombre contiene patrones no permitidos');
        }
        
        $this->attributes['nombre'] = $sanitized;
    }

    /**
     * Mutator seguro para descripción
     */
    public function setDescripcionAttribute($value): void
    {
        $sanitized = $this->sanitizeString($value, 1000);
        
        // Verificar patrones maliciosos
        if ($this->containsSQLPatterns($sanitized) || $this->containsScriptTags($sanitized)) {
            throw new \InvalidArgumentException('Descripción contiene contenido no permitido');
        }
        
        $this->attributes['descripcion'] = $sanitized;
    }

    /**
     * Mutator seguro para precio
     */
    public function setPrecioAttribute($value): void
    {
        // Sanitizar y validar precio
        $precio = (float) $value;
        
        if ($precio < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo');
        }
        
        if ($precio > 999999.99) {
            throw new \InvalidArgumentException('El precio excede el límite máximo');
        }
        
        // Redondear a 2 decimales
        $this->attributes['precio'] = round($precio, 2);
    }

    /**
     * Mutator para imagen URL - validación de seguridad
     */
    public function setImagenUrlAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['imagen_url'] = null;
            return;
        }
        
        // Validar URL
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('URL de imagen inválida');
        }
        
        // Verificar dominio permitido (opcional)
        $allowedDomains = ['imgur.com', 'cloudinary.com', 'amazonaws.com'];
        $domain = parse_url($value, PHP_URL_HOST);
        
        // Log si es dominio no común (para auditoría)
        if (!in_array($domain, $allowedDomains)) {
            Log::channel('security')->info('Imagen desde dominio no común', [
                'url' => $value,
                'domain' => $domain,
                'ip' => request()->ip()
            ]);
        }
        
        $this->attributes['imagen_url'] = $value;
    }

    /**
     * Mutator para especificaciones JSON
     */
    public function setEspecificacionesAttribute($value): void
    {
        if (is_string($value)) {
            // Verificar que sea JSON válido
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Especificaciones deben ser JSON válido');
            }
            $value = $decoded;
        }
        
        // Sanitizar cada valor del array
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $val) {
                if (is_string($key)) {
                    $key = $this->sanitizeString($key, 50);
                }
                if (is_string($val)) {
                    $val = $this->sanitizeString($val, 200);
                }
                $sanitized[$key] = $val;
            }
            $value = $sanitized;
        }
        
        $this->attributes['especificaciones'] = json_encode($value);
    }

    /**
     * Sanitizar cadenas de texto
     */
    private function sanitizeString(string $value, int $maxLength = 255): string
    {
        // Trim espacios
        $sanitized = trim($value);
        
        // Remover caracteres de control
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/', '', $sanitized);
        
        // Escapar HTML manteniendo algunos caracteres seguros
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8', false);
        
        // Limitar longitud
        $sanitized = substr($sanitized, 0, $maxLength);
        
        // Normalizar espacios múltiples
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        
        return $sanitized;
    }

    /**
     * Detectar patrones SQL maliciosos
     */
    private function containsSQLPatterns(string $value): bool
    {
        $sqlPatterns = [
            '/\bSELECT\s+/i', '/\bINSERT\s+INTO\b/i', '/\bUPDATE\s+/i',
            '/\bDELETE\s+FROM\b/i', '/\bDROP\s+TABLE\b/i', '/\bUNION\s+/i',
            '/\bEXEC\s*\(/i', '/\bEXECUTE\s*\(/i', '/--/', '/\/\*/', '/\*\//'
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Detectar tags de script maliciosos
     */
    private function containsScriptTags(string $value): bool
    {
        $scriptPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i'
        ];
        
        foreach ($scriptPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Relación con Marca
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    /**
     * Relación con Categoría
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Scope para productos activos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para productos por categoría
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('categoria_id', $categoryId);
    }

    /**
     * Scope para productos por rango de precio
     */
    public function scopePriceRange(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('precio', [$min, $max]);
    }

    /**
     * Scope para búsqueda segura
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        // Sanitizar término de búsqueda
        $cleanTerm = $this->sanitizeString($term, 100);
        
        return $query->where(function($q) use ($cleanTerm) {
            $q->where('nombre', 'LIKE', "%{$cleanTerm}%")
              ->orWhere('descripcion', 'LIKE', "%{$cleanTerm}%");
        });
    }

    /**
     * Override del método save para auditoría
     */
    public function save(array $options = []): bool
    {
        $isDirty = $this->isDirty();
        $changes = $this->getDirty();
        
        // Log de cambios para auditoría
        if ($isDirty) {
            Log::channel('audit')->info('Periferico Model Changed', [
                'periferico_id' => $this->id,
                'changed_fields' => array_keys($changes),
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'timestamp' => now()->toISOString()
            ]);
        }
        
        return parent::save($options);
    }

    /**
     * Accessor para precio formateado
     */
    public function getPrecioFormateadoAttribute(): string
    {
        return '$' . number_format($this->precio, 2);
    }

    /**
     * Verificar si el periférico puede ser editado por el usuario actual
     */
    public function canBeEditedBy($user): bool
    {
        if (!$user) {
            return false;
        }
        
        return $user->isAdmin() || $this->created_by === $user->id;
    }
}