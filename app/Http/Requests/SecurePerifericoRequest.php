<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use App\Models\Periferico;
use App\Models\Categoria;

class SecurePerifericoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return false;
        }

        // Para operaciones de creación/edición, verificar rol admin
        if (in_array($this->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return auth()->user()->role === 'admin';
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $method = $this->method();
        $perifericoId = $this->route('id') ?? $this->route('periferico');

        // Reglas base
        $rules = [];

        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $rules = [
                'nombre' => [
                    'required',
                    'string',
                    'min:3',
                    'max:100',
                    'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_.()]+$/', // Alfanumérico + caracteres seguros
                    'not_regex:/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|SCRIPT|<script>|javascript:)\b/i',
                    $method === 'POST' ? 'unique:perifericos,nombre' : "unique:perifericos,nombre,$perifericoId"
                ],
                'descripcion' => [
                    'required',
                    'string',
                    'min:10',
                    'max:1000',
                    'not_regex:/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|SCRIPT|<script>|javascript:|eval\()\b/i',
                    'not_regex:/[\'";]/' // Sin comillas o punto y coma
                ],
                'precio' => [
                    'required',
                    'numeric',
                    'min:0.01',
                    'max:999999.99',
                    'regex:/^\d+(\.\d{1,2})?$/' // Formato decimal válido
                ],
                'marca_id' => [
                    'required',
                    'integer',
                    'min:1',
                    'exists:marcas,id' // Verificar que la marca existe
                ],
                'categoria_id' => [
                    'required',
                    'integer', 
                    'min:1',
                    'exists:categorias,id' // Verificar que la categoría existe
                ]
            ];

            // Campos opcionales para actualización
            if ($method !== 'POST') {
                foreach ($rules as $key => $rule) {
                    if (is_array($rule) && in_array('required', $rule)) {
                        $rules[$key] = array_filter($rule, fn($r) => $r !== 'required');
                        array_unshift($rules[$key], 'sometimes', 'required');
                    }
                }
            }
        }

        // Reglas para filtros en GET
        if ($method === 'GET') {
            $rules = [
                'categoria' => [
                    'sometimes',
                    'integer',
                    'min:1',
                    'exists:categorias,id'
                ],
                'marca' => [
                    'sometimes',
                    'string',
                    'max:50',
                    'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_]+$/',
                    'not_regex:/[\'";]/'
                ],
                'precio_min' => [
                    'sometimes',
                    'numeric',
                    'min:0'
                ],
                'precio_max' => [
                    'sometimes',
                    'numeric',
                    'min:0',
                    'gt:precio_min'
                ],
                'busqueda' => [
                    'sometimes',
                    'string',
                    'max:100',
                    'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_.()]+$/',
                    'not_regex:/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|SCRIPT)\b/i'
                ],
                'page' => [
                    'sometimes',
                    'integer',
                    'min:1',
                    'max:1000'
                ],
                'per_page' => [
                    'sometimes',
                    'integer',
                    'min:1',
                    'max:100'
                ]
            ];
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'nombre.regex' => 'El nombre contiene caracteres no permitidos. Use solo letras, números y caracteres básicos.',
            'nombre.not_regex' => 'El nombre contiene patrones de código malicioso.',
            'descripcion.not_regex' => 'La descripción contiene patrones de código malicioso.',
            'precio.regex' => 'El precio debe tener formato decimal válido (ej: 123.45).',
            'marca_id.exists' => 'La marca seleccionada no existe.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'busqueda.regex' => 'La búsqueda contiene caracteres no permitidos.',
            'busqueda.not_regex' => 'La búsqueda contiene patrones no seguros.',
            '*.not_regex' => 'El campo contiene patrones de código malicioso.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $sanitizedData = [];
        
        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                $sanitizedData[$key] = $this->sanitizeInput($value);
            } elseif (is_numeric($value)) {
                // Asegurar que los números sean del tipo correcto
                if (in_array($key, ['precio'])) {
                    $sanitizedData[$key] = (float) $value;
                } elseif (in_array($key, ['marca_id', 'categoria_id', 'page', 'per_page'])) {
                    $sanitizedData[$key] = (int) $value;
                } else {
                    $sanitizedData[$key] = $value;
                }
            } else {
                $sanitizedData[$key] = $value;
            }
        }
        
        $this->merge($sanitizedData);
    }

    /**
     * Sanitiza input
     */
    private function sanitizeInput(string $value): string
    {
        // Trim espacios
        $value = trim($value);
        
        // Remover caracteres de control
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        
        // Escapar HTML (pero mantener algunos caracteres seguros para nombres/descripciones)
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
        
        // Decodificar URL encoding múltiple
        $attempts = 0;
        do {
            $original = $value;
            $value = urldecode($value);
            $attempts++;
        } while ($value !== $original && $attempts < 3);
        
        // Normalizar espacios múltiples
        $value = preg_replace('/\s+/', ' ', $value);
        
        return $value;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Validaciones adicionales de seguridad
            $this->additionalSecurityValidation($validator);
        });
    }

    /**
     * Validaciones de seguridad adicionales
     */
    private function additionalSecurityValidation(Validator $validator): void
    {
        // Verificar longitud total del request
        $totalLength = strlen(json_encode($this->all()));
        if ($totalLength > 10000) { // 10KB max
            $validator->errors()->add('request', 'Request demasiado grande');
        }

        // Verificar inyección SQL avanzada
        $dangerousPatterns = [
            '/\bunion\s+select\b/i',
            '/\bselect.*from.*where\b/i',
            '/\bdrop\s+table\b/i',
            '/\bdelete\s+from\b/i',
            '/\binsert\s+into\b/i',
            '/\bupdate.*set\b/i'
        ];

        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                foreach ($dangerousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $validator->errors()->add($key, 'Patrón de SQL injection detectado');
                        break;
                    }
                }
            }
        }

        // Rate limiting por IP para prevención de abuse
        $this->checkRateLimit($validator);
    }

    /**
     * Verificar rate limiting básico
     */
    private function checkRateLimit(Validator $validator): void
    {
        $ip = request()->ip();
        $cacheKey = "periferico_requests:$ip:" . now()->format('Y-m-d-H-i');
        $requests = cache()->get($cacheKey, 0);
        
        if ($requests > 60) { // 60 requests por minuto
            $validator->errors()->add('rate_limit', 'Demasiadas solicitudes. Intente más tarde.');
        } else {
            cache()->put($cacheKey, $requests + 1, 60);
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        $this->logSecurityEvent($validator);
        
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors(),
                'security_info' => [
                    'timestamp' => now()->toISOString(),
                    'request_id' => request()->header('X-Request-ID', uniqid())
                ]
            ], 422)
        );
    }

    /**
     * Log de eventos de seguridad
     */
    private function logSecurityEvent(Validator $validator): void
    {
        $securityEvent = [
            'event_type' => 'PERIFERICO_VALIDATION_FAILURE',
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'failed_rules' => $validator->errors()->toArray(),
            'input_data' => $this->safe(), // Solo datos que pasaron validación
            'risk_score' => $this->calculateRiskScore($validator->errors())
        ];

        Log::channel('security')->warning('Periferico Validation Failed', $securityEvent);
    }

    /**
     * Calcula score de riesgo
     */
    private function calculateRiskScore($errors): int
    {
        $score = 0;
        foreach ($errors->toArray() as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                if (strpos($error, 'malicioso') !== false) $score += 30;
                if (strpos($error, 'SQL injection') !== false) $score += 50;
                if (strpos($error, 'rate_limit') !== false) $score += 20;
            }
        }
        return $score;
    }
}