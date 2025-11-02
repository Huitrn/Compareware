<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class EnvironmentHelper
{
    /**
     * Ambientes disponibles del sistema
     */
    public const ENVIRONMENTS = [
        'local' => 'local',
        'sandbox' => 'sandbox',
        'staging' => 'staging',
        'production' => 'production',
    ];

    /**
     * Verifica si estamos en ambiente de desarrollo
     */
    public static function isDevelopment(): bool
    {
        return in_array(App::environment(), ['local', 'sandbox']);
    }

    /**
     * Verifica si estamos en ambiente de staging
     */
    public static function isStaging(): bool
    {
        return App::environment('staging');
    }

    /**
     * Verifica si estamos en ambiente de producción
     */
    public static function isProduction(): bool
    {
        return App::environment('production');
    }

    /**
     * Verifica si estamos en ambiente sandbox
     */
    public static function isSandbox(): bool
    {
        return App::environment('sandbox');
    }

    /**
     * Obtiene configuraciones específicas del ambiente
     */
    public static function getEnvironmentConfig(): array
    {
        $env = App::environment();
        
        return match($env) {
            'sandbox' => [
                'debug' => true,
                'cache_ttl' => 300, // 5 minutos
                'rate_limit' => false,
                'analytics' => true,
                'mock_apis' => true,
                'log_level' => 'debug',
                'session_timeout' => 480,
            ],
            'staging' => [
                'debug' => false,
                'cache_ttl' => 1800, // 30 minutos
                'rate_limit' => true,
                'analytics' => true,
                'mock_apis' => false,
                'log_level' => 'info',
                'session_timeout' => 240,
            ],
            'production' => [
                'debug' => false,
                'cache_ttl' => 3600, // 1 hora
                'rate_limit' => true,
                'analytics' => true,
                'mock_apis' => false,
                'log_level' => 'error',
                'session_timeout' => 120,
            ],
            default => [
                'debug' => true,
                'cache_ttl' => 60,
                'rate_limit' => false,
                'analytics' => false,
                'mock_apis' => true,
                'log_level' => 'debug',
                'session_timeout' => 120,
            ]
        };
    }

    /**
     * Obtiene la configuración de base de datos según el ambiente
     */
    public static function getDatabaseConfig(): array
    {
        $env = App::environment();
        
        return match($env) {
            'sandbox' => [
                'connections' => 1,
                'pool_size' => 5,
                'timeout' => 30,
                'retry_attempts' => 3,
            ],
            'staging' => [
                'connections' => 2,
                'pool_size' => 10,
                'timeout' => 15,
                'retry_attempts' => 2,
            ],
            'production' => [
                'connections' => 5,
                'pool_size' => 20,
                'timeout' => 10,
                'retry_attempts' => 1,
            ],
            default => [
                'connections' => 1,
                'pool_size' => 3,
                'timeout' => 60,
                'retry_attempts' => 5,
            ]
        };
    }

    /**
     * Obtiene los canales de logging según el ambiente
     */
    public static function getLogChannels(): array
    {
        $env = App::environment();
        
        return match($env) {
            'sandbox' => ['sandbox', 'security'],
            'staging' => ['staging_daily', 'slack', 'security'],
            'production' => ['production_daily', 'slack', 'sentry', 'security'],
            default => ['single', 'security']
        };
    }

    /**
     * Obtiene los días de retención de logs según el ambiente
     */
    public static function getLogRetentionDays(): int
    {
        $env = App::environment();
        
        return match($env) {
            'sandbox' => 7,
            'staging' => 14,
            'production' => 30,
            default => 14
        };
    }

    /**
     * Obtiene la facilidad de syslog según el ambiente
     */
    public static function getSyslogFacility(): int
    {
        $env = App::environment();
        
        return match($env) {
            'sandbox' => LOG_LOCAL0,
            'staging' => LOG_LOCAL1,
            'production' => LOG_LOCAL2,
            default => LOG_USER
        };
    }

    /**
     * Obtiene la configuración de APIs externas según el ambiente
     */
    public static function getApiConfig(): array
    {
        $env = App::environment();
        
        return match($env) {
            'sandbox' => [
                'timeout' => 30,
                'retries' => 3,
                'mock_enabled' => true,
                'rate_limit' => false,
            ],
            'staging' => [
                'timeout' => 15,
                'retries' => 2,
                'mock_enabled' => false,
                'rate_limit' => true,
            ],
            'production' => [
                'timeout' => 10,
                'retries' => 1,
                'mock_enabled' => false,
                'rate_limit' => true,
            ],
            default => [
                'timeout' => 60,
                'retries' => 5,
                'mock_enabled' => true,
                'rate_limit' => false,
            ]
        };
    }

    /**
     * Verifica si las feature flags están habilitadas
     */
    public static function isFeatureEnabled(string $feature): bool
    {
        $featureKey = "FEATURE_" . strtoupper($feature);
        return (bool) env($featureKey, false);
    }

    /**
     * Obtiene el prefijo de cache según el ambiente
     */
    public static function getCachePrefix(): string
    {
        $env = App::environment();
        return match($env) {
            'sandbox' => 'sandbox_',
            'staging' => 'staging_',
            'production' => 'prod_',
            default => 'local_'
        };
    }

    /**
     * Obtiene el dominio según el ambiente
     */
    public static function getDomain(): string
    {
        $env = App::environment();
        return match($env) {
            'sandbox' => 'sandbox.compareware.local',
            'staging' => 'staging.compareware.com',
            'production' => 'compareware.com',
            default => 'localhost'
        };
    }
}