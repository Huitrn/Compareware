<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SQLSecurityMiddleware
{
    /**
     * Patrones avanzados de detección de SQL Injection
     * Basado en el sistema Node.js implementado anteriormente
     */
    private $sqlPatterns = [
        // Comentarios SQL
        '/--[\s\S]*?(\n|$)/',
        '/\/\*[\s\S]*?\*\//',
        '/\#.*?(\n|$)/',
        
        // Comandos peligrosos
        '/\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE|TRUNCATE)\b/i',
        
        // Funciones de sistema
        '/\b(xp_cmdshell|sp_executesql|openrowset|openquery|opendatasource)\b/i',
        
        // Caracteres de escape y concatenación
        '/[\'"]\s*\+\s*[\'"]/',
        '/[\'"]\s*\|\|\s*[\'"]/',
        '/[\'"]\s*CONCAT\s*\(/',
        
        // Inyección condicional
        '/\b(AND|OR)\s+[\'""]?\d+[\'""]?\s*=\s*[\'""]?\d+/',
        '/\b(AND|OR)\s+[\'""]?\w+[\'""]?\s*=\s*[\'""]?\w+/',
        
        // Time-based attacks
        '/\b(SLEEP|WAITFOR|DELAY|BENCHMARK)\s*\(/i',
        
        // Error-based attacks
        '/\b(EXTRACTVALUE|UPDATEXML|EXP|FLOOR|RAND)\s*\(/i',
        
        // Blind SQL injection
        '/\b(SUBSTR|SUBSTRING|MID|LENGTH|ASCII|CHAR)\s*\(/i',
        
        // Union-based attacks
        '/UNION\s+(ALL\s+)?SELECT/i',
        
        // Stacked queries
        '/;\s*(SELECT|INSERT|UPDATE|DELETE|DROP)/i',
        
        // Bypass techniques
        '/\b(CHAR|CHR|CONCAT|CONCATENATE)\s*\(/i',
        '/\/\*!\d+/',
        '/\%[0-9a-fA-F]{2}/',
        
        // Database fingerprinting
        '/\b(@@version|@@servername|version\(\)|database\(\)|user\(\)|current_user)/i',
        
        // Privilege escalation
        '/\b(GRANT|REVOKE|ALTER\s+USER|CREATE\s+USER)\b/i',
        
        // File operations
        '/\b(LOAD_FILE|INTO\s+OUTFILE|INTO\s+DUMPFILE)\b/i'
    ];

    /**
     * Patrones específicos para bypass avanzado
     */
    private $bypassPatterns = [
        // Encoding bypass
        '/\%[0-9a-fA-F]{2}/',
        '/\\\\x[0-9a-fA-F]{2}/',
        '/\&\#x[0-9a-fA-F]+;/',
        '/\&\#\d+;/',
        
        // Space bypass
        '/\/\*.*?\*\//',
        '/\+/',
        '/\%20/',
        '/\t|\n|\r|\f/',
        
        // Quote bypass
        '/\\\\"/',
        '/\\\\\'/',
        '/\%22/',
        '/\%27/',
        
        // Case manipulation
        '/[sS][eE][lL][eE][cC][tT]/',
        '/[uU][nN][iI][oO][nN]/',
        '/[iI][nN][sS][eE][rR][tT]/',
        
        // Function bypass
        '/CHAR\s*\(\s*\d+[\s,\d]*\)/',
        '/CONCAT\s*\([^)]+\)/',
        '/SUBSTRING\s*\([^)]+\)/'
    ];

    /**
     * Lista negra de caracteres peligrosos
     */
    private $dangerousChars = [
        "'", '"', ';', '--', '/*', '*/', '#', 
        'xp_', 'sp_', '@@', 'char(', 'chr(', 'ascii('
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $mode = 'strict'): Response
    {
        // Analizar todas las entradas del request
        $inputData = $this->getAllInputs($request);
        
        // Detectar inyección SQL
        $sqlDetection = $this->detectSQLInjection($inputData);
        
        if ($sqlDetection['detected']) {
            // Log del intento de ataque
            $this->logSecurityEvent($request, $sqlDetection);
            
            // Bloquear en modo estricto
            if ($mode === 'strict') {
                return $this->blockRequest($sqlDetection);
            }
            
            // En modo permisivo, sanitizar y continuar
            $this->sanitizeRequest($request);
        }
        
        return $next($request);
    }

    /**
     * Obtiene todas las entradas del request
     */
    private function getAllInputs(Request $request): array
    {
        $inputs = [];
        
        // Request body data
        $inputs = array_merge($inputs, $request->all());
        
        // Query parameters
        $inputs = array_merge($inputs, $request->query());
        
        // Headers sospechosos
        $suspiciousHeaders = ['user-agent', 'referer', 'x-forwarded-for', 'x-real-ip'];
        foreach ($suspiciousHeaders as $header) {
            if ($request->hasHeader($header)) {
                $inputs['header_' . $header] = $request->header($header);
            }
        }
        
        // Route parameters
        $inputs = array_merge($inputs, $request->route()?->parameters() ?? []);
        
        return $inputs;
    }

    /**
     * Detecta inyección SQL usando múltiples técnicas
     */
    private function detectSQLInjection(array $inputs): array
    {
        $result = [
            'detected' => false,
            'risk_score' => 0,
            'patterns_matched' => [],
            'dangerous_inputs' => [],
            'attack_type' => 'none',
            'bypass_detected' => false
        ];

        foreach ($inputs as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            // Analizar el valor
            $analysis = $this->analyzeString($value);
            
            if ($analysis['is_suspicious']) {
                $result['detected'] = true;
                $result['risk_score'] += $analysis['risk_score'];
                $result['patterns_matched'] = array_merge($result['patterns_matched'], $analysis['patterns']);
                $result['dangerous_inputs'][] = [
                    'key' => $key,
                    'value' => substr($value, 0, 100), // Limitar longitud para logs
                    'patterns' => $analysis['patterns'],
                    'risk_score' => $analysis['risk_score']
                ];
                
                // Determinar tipo de ataque
                $result['attack_type'] = $this->determineAttackType($analysis['patterns']);
                
                // Detectar bypass
                if ($this->detectBypass($value)) {
                    $result['bypass_detected'] = true;
                    $result['risk_score'] += 20;
                }
            }
        }

        return $result;
    }

    /**
     * Analiza una cadena específica
     */
    private function analyzeString(string $value): array
    {
        $result = [
            'is_suspicious' => false,
            'risk_score' => 0,
            'patterns' => []
        ];

        // Normalizar el valor para el análisis
        $normalized = strtolower($value);
        $decoded = urldecode($value);
        
        // Buscar patrones SQL
        foreach ($this->sqlPatterns as $index => $pattern) {
            if (preg_match($pattern, $normalized) || preg_match($pattern, $decoded)) {
                $result['is_suspicious'] = true;
                $result['patterns'][] = "SQL_PATTERN_" . $index;
                $result['risk_score'] += 15;
            }
        }

        // Buscar caracteres peligrosos
        foreach ($this->dangerousChars as $char) {
            if (stripos($value, $char) !== false) {
                $result['is_suspicious'] = true;
                $result['patterns'][] = "DANGEROUS_CHAR_" . str_replace(['(', ')', '_'], '', $char);
                $result['risk_score'] += 5;
            }
        }

        // Análisis contextual adicional
        $contextAnalysis = $this->contextualAnalysis($value);
        if ($contextAnalysis['suspicious']) {
            $result['is_suspicious'] = true;
            $result['risk_score'] += $contextAnalysis['risk_score'];
            $result['patterns'] = array_merge($result['patterns'], $contextAnalysis['patterns']);
        }

        return $result;
    }

    /**
     * Análisis contextual avanzado
     */
    private function contextualAnalysis(string $value): array
    {
        $result = [
            'suspicious' => false,
            'risk_score' => 0,
            'patterns' => []
        ];

        // Detectar múltiples palabras clave SQL juntas
        $sqlKeywords = ['SELECT', 'FROM', 'WHERE', 'UNION', 'INSERT', 'UPDATE', 'DELETE'];
        $keywordCount = 0;
        
        foreach ($sqlKeywords as $keyword) {
            if (stripos($value, $keyword) !== false) {
                $keywordCount++;
            }
        }

        if ($keywordCount >= 2) {
            $result['suspicious'] = true;
            $result['risk_score'] += $keywordCount * 10;
            $result['patterns'][] = "MULTIPLE_SQL_KEYWORDS";
        }

        // Detectar estructura de query SQL completa
        if (preg_match('/SELECT.+FROM.+/i', $value)) {
            $result['suspicious'] = true;
            $result['risk_score'] += 25;
            $result['patterns'][] = "COMPLETE_SQL_QUERY";
        }

        // Detectar intentos de concatenación maliciosa
        if (preg_match('/[\'"]\s*\+\s*[\'"]|[\'"]\s*\|\|\s*[\'"]/', $value)) {
            $result['suspicious'] = true;
            $result['risk_score'] += 20;
            $result['patterns'][] = "STRING_CONCATENATION_ATTACK";
        }

        return $result;
    }

    /**
     * Detecta técnicas de bypass
     */
    private function detectBypass(string $value): bool
    {
        foreach ($this->bypassPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determina el tipo de ataque basado en patrones
     */
    private function determineAttackType(array $patterns): string
    {
        if (in_array('COMPLETE_SQL_QUERY', $patterns)) {
            return 'UNION_BASED';
        }
        if (in_array('MULTIPLE_SQL_KEYWORDS', $patterns)) {
            return 'BOOLEAN_BASED';
        }
        if (in_array('STRING_CONCATENATION_ATTACK', $patterns)) {
            return 'ERROR_BASED';
        }
        return 'GENERIC_SQL_INJECTION';
    }

    /**
     * Sanitiza el request (modo permisivo)
     */
    private function sanitizeRequest(Request $request): void
    {
        // Aplicar sanitización básica a todos los inputs
        $allInputs = $request->all();
        
        foreach ($allInputs as $key => $value) {
            if (is_string($value)) {
                // Remover caracteres peligrosos
                $sanitized = $this->sanitizeString($value);
                $request->merge([$key => $sanitized]);
            }
        }
    }

    /**
     * Sanitiza una cadena específica
     */
    private function sanitizeString(string $value): string
    {
        // Escapar caracteres SQL peligrosos
        $sanitized = str_replace(['\'', '"', ';', '--'], '', $value);
        
        // Remover comentarios SQL
        $sanitized = preg_replace('/\/\*.*?\*\//', '', $sanitized);
        $sanitized = preg_replace('/#.*?(\n|$)/', '', $sanitized);
        
        // Decodificar y limpiar
        $sanitized = urldecode($sanitized);
        $sanitized = strip_tags($sanitized);
        
        return trim($sanitized);
    }

    /**
     * Registra evento de seguridad
     */
    private function logSecurityEvent(Request $request, array $detection): void
    {
        $eventData = [
            'event_type' => 'SQL_INJECTION_ATTEMPT',
            'timestamp' => now()->toISOString(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'detection' => $detection,
            'request_data' => [
                'headers' => $request->headers->all(),
                'query_params' => $request->query(),
                'body_params' => $request->except(['password']), // Excluir datos sensibles
            ]
        ];

        // Log estructurado
        Log::channel('security')->warning('SQL Injection Attempt Detected', $eventData);
        
        // También log en archivo específico para análisis
        $logPath = storage_path('logs/sql_injection_attempts.json');
        file_put_contents(
            $logPath, 
            json_encode($eventData) . "\n", 
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Bloquea el request y retorna error
     */
    private function blockRequest(array $detection): JsonResponse
    {
        return response()->json([
            'error' => 'Request blocked for security reasons',
            'code' => 'SQL_INJECTION_DETECTED',
            'risk_score' => $detection['risk_score'],
            'attack_type' => $detection['attack_type'],
            'timestamp' => now()->toISOString()
        ], 403);
    }
}