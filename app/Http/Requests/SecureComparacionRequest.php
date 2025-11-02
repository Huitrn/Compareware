<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SecureComparacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'periferico1' => $this->sanitizeInput($this->query('periferico1')),
            'periferico2' => $this->sanitizeInput($this->query('periferico2'))
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'periferico1' => [
                'required',
                'integer',
                'min:1',
                'max:999999',
                'exists:perifericos,id',
                'regex:/^[0-9]+$/', // Solo números
            ],
            'periferico2' => [
                'required',
                'integer',
                'min:1',
                'max:999999',
                'exists:perifericos,id',
                'regex:/^[0-9]+$/', // Solo números
                'different:periferico1', // No puede ser el mismo
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'periferico1.required' => 'El primer periférico es requerido',
            'periferico1.integer' => 'El ID del primer periférico debe ser un número entero',
            'periferico1.exists' => 'El primer periférico no existe',
            'periferico1.regex' => 'El ID del primer periférico contiene caracteres inválidos',
            'periferico2.required' => 'El segundo periférico es requerido',
            'periferico2.integer' => 'El ID del segundo periférico debe ser un número entero',
            'periferico2.exists' => 'El segundo periférico no existe',
            'periferico2.regex' => 'El ID del segundo periférico contiene caracteres inválidos',
            'periferico2.different' => 'Los periféricos deben ser diferentes',
        ];
    }

    /**
     * Sanitize input to prevent SQL injection and XSS
     */
    private function sanitizeInput($input): string
    {
        if (is_null($input)) {
            return '';
        }

        // Convertir a string
        $input = (string) $input;

        // Remover caracteres peligrosos
        $dangerous_patterns = [
            '/[\'";\\\\]/',           // Comillas y backslashes
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b/i',
            '/(-{2,}|\/\*|\*\/|\#)/', // Comentarios SQL
            '/<[^>]*>/',              // Tags HTML
            '/javascript:/i',          // JavaScript
            '/on\w+\s*=/i',           // Event handlers
        ];

        foreach ($dangerous_patterns as $pattern) {
            $input = preg_replace($pattern, '', $input);
        }

        // Log de intento de inyección si se detecta
        if ($input !== $this->query('periferico1') || $input !== $this->query('periferico2')) {
            Log::warning('Posible intento de SQL Injection detectado en ComparacionRequest', [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'original_input' => $this->query('periferico1') . ' | ' . $this->query('periferico2'),
                'sanitized_input' => $input,
                'timestamp' => now()
            ]);
        }

        return trim($input);
    }

    /**
     * Get validated and sanitized periferico IDs
     */
    public function getPerifericoIds(): array
    {
        return [
            'periferico1' => (int) $this->validated('periferico1'),
            'periferico2' => (int) $this->validated('periferico2')
        ];
    }
}