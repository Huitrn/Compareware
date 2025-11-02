<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SwitchEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'env:switch 
                            {environment : El ambiente al que cambiar (sandbox, staging, production)}
                            {--force : Forzar el cambio sin confirmación}
                            {--backup : Crear backup del .env actual}';

    /**
     * The console command description.
     */
    protected $description = 'Cambiar entre diferentes ambientes de la aplicación';

    /**
     * Ambientes válidos
     */
    protected array $validEnvironments = ['sandbox', 'staging', 'production'];

    /**
     * Información de ambientes
     */
    protected array $environmentInfo = [
        'sandbox' => [
            'name' => 'Sandbox (Desarrollo)',
            'icon' => '🏖️',
            'description' => 'Ambiente de desarrollo y pruebas internas',
            'url' => 'http://sandbox.compareware.local',
            'api_port' => 3000,
            'database' => 'Local (sandbox_db)',
            'ssl' => false,
            'monitoring' => 'Básico'
        ],
        'staging' => [
            'name' => 'Staging (Ambiental)',
            'icon' => '🎭',
            'description' => 'Ambiente de testing de integración',
            'url' => 'https://staging.compareware.com',
            'api_port' => 3500,
            'database' => 'Cluster (staging-db.compareware.com)',
            'ssl' => true,
            'monitoring' => 'Medio (Slack alerts)'
        ],
        'production' => [
            'name' => 'Production (Productivo)',
            'icon' => '🚀',
            'description' => 'Ambiente productivo',
            'url' => 'https://compareware.com',
            'api_port' => 4000,
            'database' => 'Master/Replica (prod-master.compareware.com)',
            'ssl' => true,
            'monitoring' => 'Completo (Slack + Sentry + SMS)'
        ]
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetEnvironment = $this->argument('environment');

        // Mostrar banner
        $this->showBanner();

        // Validar ambiente
        if (!$this->isValidEnvironment($targetEnvironment)) {
            $this->error("❌ Ambiente '{$targetEnvironment}' no válido.");
            $this->showAvailableEnvironments();
            return self::FAILURE;
        }

        // Mostrar ambiente actual
        $this->showCurrentEnvironment();

        // Mostrar información del ambiente objetivo
        $this->showEnvironmentInfo($targetEnvironment);

        // Confirmar cambio (a menos que se use --force)
        if (!$this->option('force') && !$this->confirmSwitch($targetEnvironment)) {
            $this->warn('🚫 Operación cancelada por el usuario.');
            return self::SUCCESS;
        }

        // Ejecutar cambio de ambiente
        return $this->switchEnvironment($targetEnvironment);
    }

    /**
     * Mostrar banner de la aplicación
     */
    protected function showBanner(): void
    {
        $this->info('');
        $this->info('=========================================');
        $this->info('   COMPAREWARE ENVIRONMENT SWITCHER');
        $this->info('=========================================');
        $this->info('');
    }

    /**
     * Verificar si el ambiente es válido
     */
    protected function isValidEnvironment(string $environment): bool
    {
        return in_array($environment, $this->validEnvironments);
    }

    /**
     * Mostrar ambientes disponibles
     */
    protected function showAvailableEnvironments(): void
    {
        $this->info('Ambientes disponibles:');
        foreach ($this->environmentInfo as $env => $info) {
            $this->line("  {$info['icon']} {$env} - {$info['description']}");
        }
    }

    /**
     * Mostrar ambiente actual
     */
    protected function showCurrentEnvironment(): void
    {
        $currentEnv = config('app.env');
        $appName = config('app.name');
        $appUrl = config('app.url');
        
        $this->info("📍 Ambiente actual: {$currentEnv}");
        $this->line("   - Aplicación: {$appName}");
        $this->line("   - URL: {$appUrl}");
        $this->info('');
    }

    /**
     * Mostrar información del ambiente objetivo
     */
    protected function showEnvironmentInfo(string $environment): void
    {
        $info = $this->environmentInfo[$environment];
        
        $this->info("=========================================");
        $this->info("   {$info['icon']} {$info['name']}");
        $this->info("=========================================");
        $this->line("📝 Descripción: {$info['description']}");
        $this->line("🌐 URL: {$info['url']}");
        $this->line("🔌 Puerto API: {$info['api_port']}");
        $this->line("💾 Base de datos: {$info['database']}");
        $this->line("🔒 SSL: " . ($info['ssl'] ? 'Habilitado' : 'Deshabilitado'));
        $this->line("📊 Monitoreo: {$info['monitoring']}");
        $this->info('');
    }

    /**
     * Confirmar el cambio de ambiente
     */
    protected function confirmSwitch(string $targetEnvironment): bool
    {
        return $this->confirm("¿Desea cambiar al ambiente {$targetEnvironment}?", false);
    }

    /**
     * Ejecutar el cambio de ambiente
     */
    protected function switchEnvironment(string $targetEnvironment): int
    {
        try {
            $this->info("🔄 Iniciando cambio de ambiente a: {$targetEnvironment}");

            // Crear backup si se solicita
            if ($this->option('backup')) {
                $this->createBackup();
            }

            // Cambiar archivo .env
            $this->switchEnvFile($targetEnvironment);

            // Limpiar caches
            $this->clearCaches();

            // Cambiar API Node.js si existe
            $this->switchApiEnvironment($targetEnvironment);

            $this->info('');
            $this->info('🎉 ¡Cambio de ambiente completado exitosamente!');
            $this->info("✅ Ambiente actual: {$targetEnvironment}");
            
            // Mostrar comandos sugeridos
            $this->showSuggestedCommands($targetEnvironment);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error al cambiar ambiente: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * Crear backup del .env actual
     */
    protected function createBackup(): void
    {
        $timestamp = now()->format('Ymd_His');
        $backupPath = base_path(".env.backup.{$timestamp}");
        
        if (File::exists(base_path('.env'))) {
            File::copy(base_path('.env'), $backupPath);
            $this->line("💾 Backup creado: .env.backup.{$timestamp}");
        }
    }

    /**
     * Cambiar archivo .env
     */
    protected function switchEnvFile(string $targetEnvironment): void
    {
        $envFile = base_path(".env.{$targetEnvironment}");
        $mainEnvFile = base_path('.env');

        if (!File::exists($envFile)) {
            throw new \Exception("No existe el archivo de configuración para el ambiente: {$targetEnvironment}");
        }

        File::copy($envFile, $mainEnvFile);
        $this->line("📋 Archivo .env actualizado para ambiente: {$targetEnvironment}");
    }

    /**
     * Limpiar caches de Laravel
     */
    protected function clearCaches(): void
    {
        $this->line("🧹 Limpiando caches...");

        $cacheCommands = [
            'config:clear' => 'Cache de configuración',
            'cache:clear' => 'Cache de aplicación',
            'route:clear' => 'Cache de rutas',
            'view:clear' => 'Cache de vistas'
        ];

        foreach ($cacheCommands as $command => $description) {
            try {
                Artisan::call($command);
                $this->line("  ✓ {$description} limpiado");
            } catch (\Exception $e) {
                $this->line("  ⚠️ No se pudo limpiar {$description}");
            }
        }
    }

    /**
     * Cambiar ambiente de API Node.js
     */
    protected function switchApiEnvironment(string $targetEnvironment): void
    {
        $apiDir = base_path('JavaS/api-node');
        $apiEnvFile = "{$apiDir}/.env.{$targetEnvironment}";
        $mainApiEnvFile = "{$apiDir}/.env";

        if (!File::exists($apiEnvFile)) {
            $this->line("⚠️ No existe configuración API para ambiente: {$targetEnvironment}");
            return;
        }

        // Crear backup del .env de API
        if (File::exists($mainApiEnvFile)) {
            $timestamp = now()->format('Ymd_His');
            File::copy($mainApiEnvFile, "{$apiDir}/.env.backup.{$timestamp}");
        }

        File::copy($apiEnvFile, $mainApiEnvFile);
        $this->line("🔌 Configuración API Node.js actualizada");
    }

    /**
     * Mostrar comandos sugeridos
     */
    protected function showSuggestedCommands(string $environment): void
    {
        $this->info('');
        $this->info('💡 Comandos sugeridos para completar el cambio:');
        $this->line("   php artisan migrate --env={$environment}    # Ejecutar migraciones");
        $this->line("   php artisan serve --port=8000              # Iniciar servidor Laravel");
        $this->line("   cd JavaS/api-node && npm start            # Iniciar API Node.js");
        $this->info('');
    }
}