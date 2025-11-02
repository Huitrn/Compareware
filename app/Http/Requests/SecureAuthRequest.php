<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class SecureAuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $action = $this->route()->getName();
        
        if ($action === 'register') {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:50',
                    'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', // Solo letras y espacios
                    'not_regex:/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|SCRIPT)\b/i' // Anti-SQL
                ],
                'email' => [
                    'required',
                    'email:strict',
                    'max:255',
                    'unique:users,email',
                    'not_regex:/[\'";]/', // Sin caracteres peligrosos
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'max:128',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                    'not_regex:/\b(password|123456|qwerty|admin)\b/i' // Contraseñas comunes
                ]
            ];
        }

        if ($action === 'login') {
            return [
                'email' => [
                    'required',
                    'email:strict',
                    'max:255',
                    'not_regex:/[\'";]/',
                    'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
                ],
                'password' => [
                    'required',
                    'string',
                    'min:1',
                    'max:128',
                    'not_regex:/\b(SELECT|UNION|INSERT|DELETE|DROP|SCRIPT|<script>)\b/i'
                ]
            ];
        }

        return [];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'name.not_regex' => 'El nombre contiene caracteres no permitidos.',
            'email.not_regex' => 'El email contiene caracteres peligrosos.',
            'email.regex' => 'Formato de email inválido.',
            'password.regex' => 'La contraseña debe contener al menos: 1 minúscula, 1 mayúscula, 1 número y 1 carácter especial.',
            'password.not_regex' => 'La contraseña no cumple con los requisitos de seguridad.',
            '*.not_regex' => 'El campo contiene caracteres o patrones no permitidos por seguridad.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitizar datos antes de la validación
        $sanitizedData = [];
        
        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                // Sanitizar cadenas
                $sanitized = $this->sanitizeInput($value);
                $sanitizedData[$key] = $sanitized;
            } else {
                $sanitizedData[$key] = $value;
            }
        }
        
        $this->merge($sanitizedData);
    }

    /**
     * Sanitiza un input específico
     */
    private function sanitizeInput(string $value): string
    {
        // 1. Remover espacios innecesarios
        $value = trim($value);
        
        // 2. Escapar HTML peligroso
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        // 3. Remover null bytes
        $value = str_replace("\0", '', $value);
        
        // 4. Limitar caracteres de control
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        
        // 5. Decodificar URL encoding múltiple (prevenir bypass)
        $attempts = 0;
        do {
            $original = $value;
            $value = urldecode($value);
            $attempts++;
        } while ($value !== $original && $attempts < 3);
        
        return $value;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        // Log del intento de validación fallida
        $this->logValidationFailure($validator);
        
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors(),
                'security_notice' => 'Request validation failed for security reasons'
            ], 422)
        );
    }

    /**
     * Log de fallos de validación para análisis de seguridad
     */
    private function logValidationFailure(Validator $validator): void
    {
        $securityEvent = [
            'event_type' => 'VALIDATION_FAILURE',
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'failed_rules' => $validator->errors()->toArray(),
            'input_data' => $this->except(['password']), // Excluir contraseña
            'risk_indicators' => $this->analyzeSecurityRisk($validator->errors())
        ];

        Log::channel('security')->warning('Form Validation Failed', $securityEvent);
    }

    /**
     * Analiza el riesgo de seguridad basado en errores de validación
     */
    private function analyzeSecurityRisk($errors): array
    {
        $riskIndicators = [];
        $riskScore = 0;

        foreach ($errors->toArray() as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                // Detectar patrones de alto riesgo
                if (strpos($error, 'not_regex') !== false) {
                    $riskIndicators[] = "SQL_INJECTION_ATTEMPT_IN_$field";
                    $riskScore += 25;
                }
                
                if (strpos($error, 'caracteres peligrosos') !== false) {
                    $riskIndicators[] = "DANGEROUS_CHARS_IN_$field";
                    $riskScore += 15;
                }
                
                if (strpos($error, 'unique') !== false) {
                    $riskIndicators[] = "ENUMERATION_ATTEMPT_$field";
                    $riskScore += 5;
                }
            }
        }

        return [
            'risk_score' => $riskScore,
            'indicators' => $riskIndicators,
            'classification' => $this->classifyRisk($riskScore)
        ];
    }

    /**
     * Clasifica el nivel de riesgo
     */
    private function classifyRisk(int $score): string
    {
        if ($score >= 50) return 'CRITICAL';
        if ($score >= 25) return 'HIGH';
        if ($score >= 10) return 'MEDIUM';
        return 'LOW';
    }
}