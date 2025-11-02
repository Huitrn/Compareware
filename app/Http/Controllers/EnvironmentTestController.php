<?php

namespace App\Http\Controllers;

use App\Models\EnvironmentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnvironmentTestController extends Controller
{
    /**
     * Dashboard para probar ambientes
     */
    public function dashboard()
    {
        $currentEnv = app()->environment();
        $stats = EnvironmentLog::getEnvironmentStats();
        $recentLogs = EnvironmentLog::getByEnvironment($currentEnv, 10);
        
        // Registrar acceso al dashboard
        EnvironmentLog::logAction('dashboard_access', 'Usuario accedió al dashboard de pruebas');
        
        return view('environment-test.dashboard', compact('currentEnv', 'stats', 'recentLogs'));
    }

    /**
     * Probar funcionalidades específicas del ambiente
     */
    public function testEnvironment(Request $request)
    {
        $environment = app()->environment();
        $testResults = [];

        try {
            // Test 1: Conexión a Base de Datos
            $dbTest = $this->testDatabase();
            $testResults['database'] = $dbTest;

            // Test 2: Sistema de Cache
            $cacheTest = $this->testCache($environment);
            $testResults['cache'] = $cacheTest;

            // Test 3: Sistema de Logs
            $logTest = $this->testLogging($environment);
            $testResults['logging'] = $logTest;

            // Test 4: Variables de Configuración
            $configTest = $this->testConfiguration($environment);
            $testResults['configuration'] = $configTest;

            // Test 5: Funcionalidades específicas por ambiente
            $envSpecificTest = $this->testEnvironmentSpecific($environment);
            $testResults['environment_specific'] = $envSpecificTest;

            // Registrar la prueba
            EnvironmentLog::logAction('environment_test', "Prueba completa del ambiente {$environment}", $testResults);

            return response()->json([
                'success' => true,
                'environment' => $environment,
                'timestamp' => now()->toISOString(),
                'tests' => $testResults
            ]);

        } catch (\Exception $e) {
            EnvironmentLog::logAction('test_error', "Error en prueba de ambiente: {$e->getMessage()}");
            
            return response()->json([
                'success' => false,
                'environment' => $environment,
                'error' => $e->getMessage(),
                'tests' => $testResults
            ], 500);
        }
    }

    /**
     * Probar conexión a base de datos
     */
    private function testDatabase(): array
    {
        try {
            $start = microtime(true);
            
            // Test de conexión básica
            $connection = DB::connection()->getPdo();
            $connectionTime = (microtime(true) - $start) * 1000;
            
            // Test de consulta
            $userCount = DB::table('users')->count();
            $environmentLogCount = DB::table('environment_logs')->count();
            
            return [
                'status' => 'success',
                'connection_time_ms' => round($connectionTime, 2),
                'database' => config('database.connections.pgsql.database'),
                'host' => config('database.connections.pgsql.host'),
                'users_count' => $userCount,
                'environment_logs_count' => $environmentLogCount
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Probar sistema de cache
     */
    private function testCache(string $environment): array
    {
        try {
            $testKey = "env_test_{$environment}_" . time();
            $testValue = [
                'environment' => $environment,
                'timestamp' => now()->toISOString(),
                'random' => rand(1000, 9999)
            ];

            // Test de escritura
            Cache::put($testKey, $testValue, 60);
            
            // Test de lectura
            $retrieved = Cache::get($testKey);
            
            // Test de eliminación
            Cache::forget($testKey);
            $afterDelete = Cache::get($testKey);

            return [
                'status' => 'success',
                'driver' => config('cache.default'),
                'write_test' => $retrieved === $testValue ? 'pass' : 'fail',
                'delete_test' => $afterDelete === null ? 'pass' : 'fail'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Probar sistema de logging
     */
    private function testLogging(string $environment): array
    {
        try {
            $logMessage = "Test de logging para ambiente {$environment} - " . now()->toISOString();
            
            // Probar diferentes niveles según el ambiente
            switch ($environment) {
                case 'sandbox':
                    Log::debug($logMessage);
                    Log::info($logMessage);
                    break;
                case 'staging':
                    Log::info($logMessage);
                    Log::warning($logMessage);
                    break;
                case 'production':
                    Log::error($logMessage);
                    break;
            }

            return [
                'status' => 'success',
                'default_channel' => config('logging.default'),
                'log_level' => config('logging.level', env('LOG_LEVEL', 'debug')),
                'channels' => config('logging.channels.stack.channels', [])
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Probar configuración del ambiente
     */
    private function testConfiguration(string $environment): array
    {
        return [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'app_url' => config('app.url'),
            'session_driver' => config('session.driver'),
            'cache_driver' => config('cache.default'),
            'queue_connection' => config('queue.default'),
            'expected_env' => $environment,
            'matches_expected' => config('app.env') === $environment
        ];
    }

    /**
     * Probar funcionalidades específicas por ambiente
     */
    private function testEnvironmentSpecific(string $environment): array
    {
        $features = [];

        switch ($environment) {
            case 'sandbox':
                $features = [
                    'debug_mode' => config('app.debug') === true,
                    'detailed_errors' => true,
                    'mock_apis' => true,
                    'rate_limiting' => false,
                    'ssl_required' => false
                ];
                break;

            case 'staging':
                $features = [
                    'debug_mode' => config('app.debug') === false,
                    'integration_tests' => true,
                    'slack_notifications' => true,
                    'rate_limiting' => true,
                    'ssl_required' => true
                ];
                break;

            case 'production':
                $features = [
                    'debug_mode' => config('app.debug') === false,
                    'error_tracking' => true,
                    'monitoring' => true,
                    'backups' => true,
                    'ssl_required' => true,
                    'security_hardened' => true
                ];
                break;
        }

        return $features;
    }

    /**
     * Comparar ambientes
     */
    public function compareEnvironments()
    {
        $environments = ['sandbox', 'staging', 'production'];
        $comparison = [];

        foreach ($environments as $env) {
            $envFile = base_path(".env.{$env}");
            if (file_exists($envFile)) {
                $content = file_get_contents($envFile);
                preg_match('/APP_NAME="?([^"\\n]*)"?/', $content, $nameMatch);
                preg_match('/APP_DEBUG=([^\\n]*)/', $content, $debugMatch);
                preg_match('/APP_URL=([^\\n]*)/', $content, $urlMatch);
                preg_match('/LOG_LEVEL=([^\\n]*)/', $content, $logMatch);

                $comparison[$env] = [
                    'app_name' => $nameMatch[1] ?? 'N/A',
                    'debug_mode' => ($debugMatch[1] ?? 'false') === 'true',
                    'app_url' => $urlMatch[1] ?? 'N/A',
                    'log_level' => $logMatch[1] ?? 'debug'
                ];
            }
        }

        return response()->json($comparison);
    }
}