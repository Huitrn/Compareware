<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        // 🛡️ CANALES DE SEGURIDAD PERSONALIZADOS
        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 90, // Mantener logs de seguridad por 3 meses
            'replace_placeholders' => true,
        ],

        'critical' => [
            'driver' => 'daily', 
            'path' => storage_path('logs/critical.log'),
            'level' => 'critical',
            'days' => 365, // Mantener eventos críticos por 1 año
            'replace_placeholders' => true,
        ],

        'audit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/audit.log'), 
            'level' => 'info',
            'days' => 180, // Mantener auditoría por 6 meses
            'replace_placeholders' => true,
        ],

        'sql_injection' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sql_injection.log'),
            'level' => 'warning',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        'rate_limiting' => [
            'driver' => 'daily', 
            'path' => storage_path('logs/rate_limiting.log'),
            'level' => 'warning',
            'days' => 14,
            'replace_placeholders' => true,
        ],

        // Environment-specific channels
        'sandbox' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sandbox.log'),
            'level' => 'debug',
            'days' => 7,
            'replace_placeholders' => true,
        ],

        'staging' => [
            'driver' => 'stack',
            'channels' => ['staging_daily', 'slack'],
            'ignore_exceptions' => false,
        ],

        'staging_daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/staging.log'),
            'level' => 'info',
            'days' => 14,
            'replace_placeholders' => true,
        ],

        'production' => [
            'driver' => 'stack',
            'channels' => ['production_daily', 'slack', 'sentry'],
            'ignore_exceptions' => false,
        ],

        'production_daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/production.log'),
            'level' => 'error',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        'sentry' => [
            'driver' => 'monolog',
            'level' => 'error',
            'handler' => \Sentry\Monolog\Handler::class,
            'handler_with' => [
                'level' => \Monolog\Logger::ERROR,
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        // Custom application channels
        'user_activity' => [
            'driver' => 'daily',
            'path' => storage_path('logs/user-activity.log'),
            'level' => 'info',
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'api' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api.log'),
            'level' => env('APP_ENV') === 'production' ? 'error' : 'info',
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'transactions' => [
            'driver' => 'daily',
            'path' => storage_path('logs/transactions.log'),
            'level' => 'info',
            'days' => env('LOG_DAILY_DAYS', 14) * 2,
            'replace_placeholders' => true,
        ],

        'comparisons' => [
            'driver' => 'daily',
            'path' => storage_path('logs/comparisons.log'),
            'level' => 'info',
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/performance.log'),
            'level' => env('APP_ENV') === 'local' ? 'debug' : 'warning',
            'days' => 7,
            'replace_placeholders' => true,
        ],

    ],

];
