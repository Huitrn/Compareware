<?php

use App\Helpers\EnvironmentHelper;

return [
    /*
    |--------------------------------------------------------------------------
    | Environment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración específica para cada ambiente del sistema CompareWare
    |
    */

    'current' => env('APP_ENV', 'production'),

    'environments' => [
        'sandbox' => [
            'name' => 'Sandbox',
            'description' => 'Ambiente de desarrollo y pruebas internas',
            'url' => 'http://sandbox.compareware.local',
            'debug' => true,
            'maintenance_mode' => false,
            'db_schema' => 'sandbox',  // Schema de PostgreSQL
        ],
        'staging' => [
            'name' => 'Staging',
            'description' => 'Ambiente de pre-producción para testing',
            'url' => 'https://staging.compareware.com',
            'debug' => false,
            'maintenance_mode' => false,
            'db_schema' => 'staging',  // Schema de PostgreSQL
        ],
        'production' => [
            'name' => 'Production',
            'description' => 'Ambiente de producción',
            'url' => 'https://compareware.com',
            'debug' => false,
            'maintenance_mode' => false,
            'db_schema' => 'public',   // Schema de PostgreSQL
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags por Ambiente
    |--------------------------------------------------------------------------
    */
    'features' => [
        'analytics' => env('FEATURE_ANALYTICS', false),
        'cache' => env('FEATURE_CACHE', false),
        'rate_limiting' => env('FEATURE_RATE_LIMITING', false),
        'mock_apis' => env('FEATURE_MOCK_APIS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Performance
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'cache_ttl' => match(env('APP_ENV')) {
            'sandbox' => 300,     // 5 minutos
            'staging' => 1800,    // 30 minutos
            'production' => 3600, // 1 hora
            default => 60
        },
        'session_timeout' => match(env('APP_ENV')) {
            'sandbox' => 480,     // 8 horas
            'staging' => 240,     // 4 horas
            'production' => 120,  // 2 horas
            default => 120
        },
        'query_timeout' => match(env('APP_ENV')) {
            'sandbox' => 30,      // 30 segundos
            'staging' => 15,      // 15 segundos
            'production' => 10,   // 10 segundos
            default => 60
        },
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de APIs Externas
    |--------------------------------------------------------------------------
    */
    'apis' => [
        'timeout' => match(env('APP_ENV')) {
            'sandbox' => 30000,   // 30 segundos
            'staging' => 15000,   // 15 segundos
            'production' => 10000, // 10 segundos
            default => 60000
        },
        'retries' => match(env('APP_ENV')) {
            'sandbox' => 3,
            'staging' => 2,
            'production' => 1,
            default => 5
        },
        'rate_limit' => match(env('APP_ENV')) {
            'sandbox' => false,
            'staging' => true,
            'production' => true,
            default => false
        },
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'level' => match(env('APP_ENV')) {
            'sandbox' => 'debug',
            'staging' => 'info',
            'production' => 'error',
            default => 'debug'
        },
        'channels' => match(env('APP_ENV')) {
            'sandbox' => ['single', 'daily'],
            'staging' => ['daily'],
            'production' => ['daily', 'syslog'],
            default => ['single']
        },
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Seguridad
    |--------------------------------------------------------------------------
    */
    'security' => [
        'bcrypt_rounds' => match(env('APP_ENV')) {
            'sandbox' => 10,
            'staging' => 12,
            'production' => 15,
            default => 10
        },
        'session_encrypt' => match(env('APP_ENV')) {
            'sandbox' => false,
            'staging' => true,
            'production' => true,
            default => false
        },
        'force_https' => match(env('APP_ENV')) {
            'sandbox' => false,
            'staging' => true,
            'production' => true,
            default => false
        },
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Monitoreo
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => env('APP_ENV') !== 'sandbox',
        'services' => [
            'new_relic' => [
                'enabled' => env('NEW_RELIC_LICENSE_KEY', false) !== false,
                'license_key' => env('NEW_RELIC_LICENSE_KEY'),
            ],
            'sentry' => [
                'enabled' => env('SENTRY_LARAVEL_DSN', false) !== false,
                'dsn' => env('SENTRY_LARAVEL_DSN'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Node.js API Configuration
    |--------------------------------------------------------------------------
    */
    'node_api' => [
        'url' => env('NODE_API_URL', 'http://localhost:4000'),
        'timeout' => env('NODE_API_TIMEOUT', 15000),
        'port' => match(env('APP_ENV')) {
            'sandbox' => 4001,
            'staging' => 4000,
            'production' => 443,
            default => 4000
        },
    ],

    /*
    |--------------------------------------------------------------------------
    | Deployment Configuration
    |--------------------------------------------------------------------------
    */
    'deployment' => [
        'branch' => match(env('APP_ENV')) {
            'sandbox' => 'develop',
            'staging' => 'staging',
            'production' => 'main',
            default => 'develop'
        },
        'auto_deploy' => match(env('APP_ENV')) {
            'sandbox' => true,
            'staging' => false,
            'production' => false,
            default => false
        },
        'rollback_enabled' => match(env('APP_ENV')) {
            'sandbox' => true,
            'staging' => true,
            'production' => true,
            default => true
        },
    ],
];