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
        'imagen_alt',
        'galeria_imagenes',
        'imagen_path',
        'imagen_blob',
        'imagen_mime_type',
        'thumbnail_url',
        'imagen_source',
        'amazon_url',
        'amazon_asin',
        'especificaciones',
        'is_active'
    ];

    /**
     * Casteo de tipos para seguridad
     */
    protected $casts = [
        'precio' => 'decimal:2',
        'especificaciones' => 'json',
        'galeria_imagenes' => 'array',
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
     * Atributos que se agregan a la serialización
     */
    protected $appends = [
        'imagen_url_completa',
        'thumbnail_url_completa'
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

    /**
     * ========================================
     * MÉTODOS DE GESTIÓN DE IMÁGENES
     * ========================================
     */

    /**
     * Obtener URL completa de la imagen o placeholder si no existe
     * Prioridad: 1) Archivo local, 2) BLOB en BD, 3) URL externa, 4) Placeholder
     */
    public function getImagenUrlCompletaAttribute(): string
    {
        // 1. Si tiene archivo local guardado
        if ($this->imagen_path) {
            return asset('storage/' . $this->imagen_path);
        }
        
        // 2. Si tiene BLOB en base de datos
        if ($this->imagen_blob) {
            return route('images.show', ['id' => $this->id]);
        }
        
        // 3. Si tiene URL externa
        if ($this->imagen_url) {
            // Si es URL completa (http/https)
            if (filter_var($this->imagen_url, FILTER_VALIDATE_URL)) {
                return $this->imagen_url;
            }
            // Si es path relativo
            return asset('storage/' . $this->imagen_url);
        }
        
        // 4. Imagen placeholder por defecto
        return asset('images/placeholder-product.png');
    }

    /**
     * Obtener URL del thumbnail o generar desde imagen principal
     */
    public function getThumbnailUrlCompletaAttribute(): string
    {
        if ($this->thumbnail_url) {
            if (filter_var($this->thumbnail_url, FILTER_VALIDATE_URL)) {
                return $this->thumbnail_url;
            }
            return asset('storage/' . $this->thumbnail_url);
        }
        
        // Si no hay thumbnail, usar imagen principal
        return $this->imagen_url_completa;
    }

    /**
     * Verificar si tiene imagen
     */
    public function hasImage(): bool
    {
        return !empty($this->imagen_url);
    }

    /**
     * Verificar si tiene galería de imágenes
     */
    public function hasGallery(): bool
    {
        return !empty($this->galeria_imagenes) && is_array($this->galeria_imagenes);
    }

    /**
     * Obtener cantidad de imágenes en la galería
     */
    public function getGalleryCountAttribute(): int
    {
        if (!$this->hasGallery()) {
            return 0;
        }
        return count($this->galeria_imagenes);
    }

    /**
     * Obtener todas las URLs de imágenes (principal + galería)
     */
    public function getAllImagesAttribute(): array
    {
        $images = [];
        
        // Agregar imagen principal
        if ($this->hasImage()) {
            $images[] = [
                'url' => $this->imagen_url_completa,
                'alt' => $this->imagen_alt ?? $this->nombre,
                'type' => 'main'
            ];
        }
        
        // Agregar galería
        if ($this->hasGallery()) {
            foreach ($this->galeria_imagenes as $index => $imagen) {
                $images[] = [
                    'url' => is_array($imagen) ? ($imagen['url'] ?? $imagen) : $imagen,
                    'alt' => is_array($imagen) ? ($imagen['alt'] ?? $this->nombre) : $this->nombre,
                    'type' => 'gallery',
                    'index' => $index
                ];
            }
        }
        
        return $images;
    }

    /**
     * Agregar imagen a la galería
     */
    public function addToGallery(string $imageUrl, ?string $alt = null): void
    {
        $galeria = $this->galeria_imagenes ?? [];
        
        $galeria[] = [
            'url' => $imageUrl,
            'alt' => $alt ?? $this->nombre,
            'added_at' => now()->toISOString()
        ];
        
        $this->galeria_imagenes = $galeria;
        $this->save();
    }

    /**
     * Eliminar imagen de la galería por índice
     */
    public function removeFromGallery(int $index): bool
    {
        if (!$this->hasGallery() || !isset($this->galeria_imagenes[$index])) {
            return false;
        }
        
        $galeria = $this->galeria_imagenes;
        unset($galeria[$index]);
        $this->galeria_imagenes = array_values($galeria); // Reindexar
        $this->save();
        
        return true;
    }

    /**
     * Mutator para imagen_alt - sanitización
     */
    public function setImagenAltAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['imagen_alt'] = $this->nombre ?? 'Imagen de producto';
            return;
        }
        
        // Sanitizar y limitar longitud
        $sanitized = strip_tags($value);
        $sanitized = substr($sanitized, 0, 255);
        
        $this->attributes['imagen_alt'] = $sanitized;
    }

    /**
     * Obtener datos completos de imagen para APIs/JSON
     */
    public function getImageDataAttribute(): array
    {
        return [
            'main' => [
                'url' => $this->imagen_url_completa,
                'thumbnail' => $this->thumbnail_url_completa,
                'alt' => $this->imagen_alt ?? $this->nombre,
                'source' => $this->imagen_source ?? 'manual'
            ],
            'gallery' => $this->all_images,
            'has_image' => $this->hasImage(),
            'gallery_count' => $this->gallery_count
        ];
    }
}
