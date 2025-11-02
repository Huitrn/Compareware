<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SecurityLogger
{
    /**
     * Tipos de eventos de seguridad
     */
    const EVENT_TYPES = [
        'SQL_INJECTION_ATTEMPT' => 'sql_injection',
        'AUTHENTICATION_FAILURE' => 'auth_failure', 
        'AUTHORIZATION_FAILURE' => 'authz_failure',
        'RATE_LIMIT_EXCEEDED' => 'rate_limit',
        'VALIDATION_FAILURE' => 'validation_failure',
        'SUSPICIOUS_ACTIVITY' => 'suspicious',
        'DATA_BREACH_ATTEMPT' => 'data_breach',
        'ADMIN_ACTION' => 'admin_action',
        'USER_ENUMERATION' => 'enumeration',
        'BRUTE_FORCE_ATTACK' => 'brute_force'
    ];

    /**
     * Niveles de severidad
     */
    const SEVERITY_LEVELS = [
        'LOW' => 1,
        'MEDIUM' => 2, 
        'HIGH' => 3,
        'CRITICAL' => 4,
        'EMERGENCY' => 5
    ];

    /**
     * Log un evento de seguridad
     */
    public function logSecurityEvent(string $eventType, array $data = [], string $severity = 'MEDIUM'): void
    {
        $event = $this->buildSecurityEvent($eventType, $data, $severity);
        
        // Log estructurado por canal
        Log::channel('security')->log(
            $this->getSyslogLevel($severity),
            $this->formatSecurityMessage($event),
            $event
        );

        // Guardar en archivo específico para análisis
        $this->saveToSecurityFile($event);

        // Alertas críticas
        if (in_array($severity, ['CRITICAL', 'EMERGENCY'])) {
            $this->handleCriticalAlert($event);
        }

        // Métricas de seguridad
        $this->updateSecurityMetrics($event);
    }

    /**
     * Construir evento de seguridad estructurado
     */
    private function buildSecurityEvent(string $eventType, array $data, string $severity): array
    {
        $request = request();
        
        return [
            // Metadatos del evento
            'event_id' => uniqid('sec_', true),
            'timestamp' => Carbon::now()->toISOString(),
            'event_type' => $eventType,
            'severity' => $severity,
            'severity_level' => self::SEVERITY_LEVELS[$severity] ?? 2,
            
            // Información de la request
            'request' => [
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName(),
                'referer' => $request->header('referer'),
                'headers' => $this->filterHeaders($request->headers->all())
            ],
            
            // Información del usuario
            'user' => [
                'id' => auth()->id(),
                'email' => auth()->user()?->email,
                'role' => auth()->user()?->role,
                'authenticated' => auth()->check()
            ],
            
            // Datos del evento específico
            'event_data' => $data,
            
            // Contexto técnico
            'technical' => [
                'session_id' => session()->getId(),
                'request_id' => $request->header('X-Request-ID', uniqid()),
                'server_name' => $request->server('SERVER_NAME'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version()
            ],
            
            // Geolocalización (si está disponible)
            'geo' => $this->getGeoInfo($this->getClientIp($request)),
            
            // Risk scoring
            'risk' => $this->calculateRiskScore($eventType, $data),
            
            // Tags para clasificación
            'tags' => $this->generateEventTags($eventType, $data)
        ];
    }

    /**
     * Obtener IP real del cliente
     */
    private function getClientIp(Request $request): string
    {
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',    // Cloudflare
            'HTTP_X_REAL_IP',          // Nginx
            'HTTP_X_FORWARDED_FOR',    // Standard proxy
            'HTTP_CLIENT_IP',          // Proxy
            'REMOTE_ADDR'              // Standard
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->server($header);
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }

        return $request->ip();
    }

    /**
     * Filtrar headers sensibles
     */
    private function filterHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization', 'x-api-key', 'cookie', 'x-auth-token',
            'x-csrf-token', 'x-xsrf-token'
        ];

        $filtered = [];
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $filtered[$key] = '[REDACTED]';
            } else {
                $filtered[$key] = is_array($value) ? $value[0] : $value;
            }
        }

        return $filtered;
    }

    /**
     * Obtener información de geolocalización básica
     */
    private function getGeoInfo(string $ip): array
    {
        // Implementación básica - en producción usar servicio como MaxMind
        return [
            'ip' => $ip,
            'country' => null,
            'region' => null,
            'city' => null,
            'timezone' => null
        ];
    }

    /**
     * Calcular score de riesgo
     */
    private function calculateRiskScore(string $eventType, array $data): array
    {
        $baseScores = [
            'SQL_INJECTION_ATTEMPT' => 80,
            'BRUTE_FORCE_ATTACK' => 70,
            'DATA_BREACH_ATTEMPT' => 90,
            'AUTHENTICATION_FAILURE' => 30,
            'RATE_LIMIT_EXCEEDED' => 40,
            'VALIDATION_FAILURE' => 20,
            'SUSPICIOUS_ACTIVITY' => 60,
            'USER_ENUMERATION' => 50
        ];

        $baseScore = $baseScores[$eventType] ?? 25;
        $modifiers = 0;

        // Modificadores basados en contexto
        if (isset($data['patterns_matched']) && count($data['patterns_matched']) > 5) {
            $modifiers += 20;
        }

        if (isset($data['bypass_detected']) && $data['bypass_detected']) {
            $modifiers += 30;
        }

        if (!auth()->check()) {
            $modifiers += 10; // Ataques desde usuarios no autenticados son más sospechosos
        }

        $finalScore = min(100, $baseScore + $modifiers);

        return [
            'score' => $finalScore,
            'base_score' => $baseScore,
            'modifiers' => $modifiers,
            'classification' => $this->classifyRisk($finalScore)
        ];
    }

    /**
     * Clasificar nivel de riesgo
     */
    private function classifyRisk(int $score): string
    {
        if ($score >= 90) return 'CRITICAL';
        if ($score >= 70) return 'HIGH';
        if ($score >= 50) return 'MEDIUM';
        if ($score >= 30) return 'LOW';
        return 'MINIMAL';
    }

    /**
     * Generar tags para clasificación
     */
    private function generateEventTags(string $eventType, array $data): array
    {
        $tags = [strtolower($eventType)];

        // Tags automáticos basados en el evento
        if (strpos($eventType, 'SQL') !== false) {
            $tags[] = 'sql_security';
        }

        if (strpos($eventType, 'AUTH') !== false) {
            $tags[] = 'authentication';
        }

        if (isset($data['attack_type'])) {
            $tags[] = strtolower($data['attack_type']);
        }

        if (!auth()->check()) {
            $tags[] = 'unauthenticated';
        }

        return array_unique($tags);
    }

    /**
     * Formatear mensaje de seguridad legible
     */
    private function formatSecurityMessage(array $event): string
    {
        $ip = $event['request']['ip_address'];
        $eventType = $event['event_type'];
        $user = $event['user']['authenticated'] 
            ? $event['user']['email'] 
            : 'unauthenticated';

        return "Security Event: {$eventType} from {$ip} (user: {$user})";
    }

    /**
     * Obtener nivel de syslog apropiado
     */
    private function getSyslogLevel(string $severity): string
    {
        $levels = [
            'EMERGENCY' => 'emergency',
            'CRITICAL' => 'critical', 
            'HIGH' => 'error',
            'MEDIUM' => 'warning',
            'LOW' => 'info'
        ];

        return $levels[$severity] ?? 'warning';
    }

    /**
     * Guardar en archivo específico de seguridad
     */
    private function saveToSecurityFile(array $event): void
    {
        $filename = 'security_events_' . Carbon::now()->format('Y-m-d') . '.json';
        $logEntry = json_encode($event) . "\n";
        
        Storage::disk('local')->append("security_logs/{$filename}", $logEntry);
    }

    /**
     * Manejar alertas críticas
     */
    private function handleCriticalAlert(array $event): void
    {
        // Log inmediato en canal crítico
        Log::channel('critical')->emergency('Critical Security Event', $event);

        // Aquí se podría enviar notificación por email, SMS, Slack, etc.
        // $this->sendCriticalAlert($event);
        
        // Crear archivo de alerta crítica
        $filename = 'critical_alert_' . $event['event_id'] . '.json';
        Storage::disk('local')->put("critical_alerts/{$filename}", json_encode($event, JSON_PRETTY_PRINT));
    }

    /**
     * Actualizar métricas de seguridad
     */
    private function updateSecurityMetrics(array $event): void
    {
        $date = Carbon::now()->format('Y-m-d');
        $metricsKey = "security_metrics:{$date}";
        
        // Incrementar contadores
        $metrics = cache()->get($metricsKey, [
            'total_events' => 0,
            'events_by_type' => [],
            'events_by_severity' => [],
            'unique_ips' => [],
            'critical_events' => 0
        ]);

        $metrics['total_events']++;
        $metrics['events_by_type'][$event['event_type']] = 
            ($metrics['events_by_type'][$event['event_type']] ?? 0) + 1;
        $metrics['events_by_severity'][$event['severity']] = 
            ($metrics['events_by_severity'][$event['severity']] ?? 0) + 1;
        
        $ip = $event['request']['ip_address'];
        if (!in_array($ip, $metrics['unique_ips'])) {
            $metrics['unique_ips'][] = $ip;
        }

        if ($event['severity'] === 'CRITICAL') {
            $metrics['critical_events']++;
        }

        // Guardar métricas por 7 días
        cache()->put($metricsKey, $metrics, 7 * 24 * 60 * 60);
    }

    /**
     * Obtener reporte de seguridad diario
     */
    public function getDailySecurityReport(Carbon $date = null): array
    {
        $date = $date ?? Carbon::now();
        $metricsKey = "security_metrics:{$date->format('Y-m-d')}";
        
        return cache()->get($metricsKey, [
            'total_events' => 0,
            'events_by_type' => [],
            'events_by_severity' => [],
            'unique_ips' => [],
            'critical_events' => 0
        ]);
    }

    /**
     * Obtener eventos de seguridad recientes
     */
    public function getRecentSecurityEvents(int $hours = 24): array
    {
        $filename = 'security_events_' . Carbon::now()->format('Y-m-d') . '.json';
        
        if (!Storage::disk('local')->exists("security_logs/{$filename}")) {
            return [];
        }

        $content = Storage::disk('local')->get("security_logs/{$filename}");
        $lines = explode("\n", trim($content));
        $events = [];
        $cutoff = Carbon::now()->subHours($hours);

        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            $event = json_decode($line, true);
            if ($event && Carbon::parse($event['timestamp'])->isAfter($cutoff)) {
                $events[] = $event;
            }
        }

        return array_reverse($events); // Más recientes primero
    }

    /**
     * Limpiar logs antiguos
     */
    public function cleanupOldLogs(int $daysToKeep = 30): void
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        $files = Storage::disk('local')->files('security_logs');
        
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/security_events_(\d{4}-\d{2}-\d{2})\.json/', $filename, $matches)) {
                $fileDate = Carbon::parse($matches[1]);
                if ($fileDate->isBefore($cutoffDate)) {
                    Storage::disk('local')->delete($file);
                }
            }
        }
    }
}