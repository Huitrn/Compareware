<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\SchemaManager;
use Illuminate\Support\Facades\DB;

class SchemaManagementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schema:manage 
                            {action : Acción a realizar (info|list|switch|clone|stats)}
                            {--schema= : Schema específico para la acción}
                            {--source= : Schema origen (para clone)}
                            {--target= : Schema destino (para clone)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestión de schemas de PostgreSQL para multi-ambiente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        return match($action) {
            'info' => $this->showInfo(),
            'list' => $this->listSchemas(),
            'switch' => $this->switchSchema(),
            'clone' => $this->cloneSchema(),
            'stats' => $this->showStats(),
            default => $this->error("Acción '{$action}' no válida. Use: info, list, switch, clone, stats")
        };
    }

    /**
     * Muestra información del ambiente y schema actual
     */
    protected function showInfo()
    {
        $this->info('========================================');
        $this->info('   INFORMACIÓN DE AMBIENTE Y SCHEMA');
        $this->info('========================================');
        $this->newLine();

        $info = SchemaManager::getEnvironmentInfo();

        $this->table(
            ['Propiedad', 'Valor'],
            [
                ['Ambiente', $info['app_env']],
                ['Aplicación', $info['app_name']],
                ['Base de Datos', $info['database']],
                ['Host', $info['host']],
                ['Schema Actual', $info['current_schema']],
                ['Schema del Ambiente', $info['environment_schema']],
            ]
        );

        if ($info['current_schema'] !== $info['environment_schema']) {
            $this->warn("⚠️  ADVERTENCIA: El schema actual no coincide con el del ambiente.");
            $this->warn("   Ejecute: php artisan schema:manage switch --schema={$info['environment_schema']}");
        } else {
            $this->info("✓ El schema está correctamente configurado para el ambiente.");
        }

        $this->newLine();
        $this->info("Schemas disponibles: " . implode(', ', $info['available_schemas']));

        return 0;
    }

    /**
     * Lista todos los schemas disponibles
     */
    protected function listSchemas()
    {
        $this->info('Schemas disponibles en la base de datos:');
        $this->newLine();

        $schemas = SchemaManager::listSchemas();
        
        if (empty($schemas)) {
            $this->error('No se encontraron schemas.');
            return 1;
        }

        $currentSchema = SchemaManager::getCurrentSchema();
        $envSchema = SchemaManager::getEnvironmentSchema();

        $tableData = [];
        foreach ($schemas as $schema) {
            $status = [];
            if ($schema === $currentSchema) {
                $status[] = '← ACTIVO';
            }
            if ($schema === $envSchema) {
                $status[] = '(ambiente)';
            }

            $tableData[] = [
                $schema,
                implode(' ', $status)
            ];
        }

        $this->table(['Schema', 'Estado'], $tableData);

        return 0;
    }

    /**
     * Cambia al schema especificado
     */
    protected function switchSchema()
    {
        $schema = $this->option('schema');

        if (!$schema) {
            $this->error('Debe especificar --schema=nombre_schema');
            return 1;
        }

        $this->info("Cambiando a schema: {$schema}");

        if (SchemaManager::setSchema($schema)) {
            $this->info("✓ Schema cambiado exitosamente a: {$schema}");
            
            // Verificar
            $current = SchemaManager::getCurrentSchema();
            $this->info("Schema actual confirmado: {$current}");
            
            return 0;
        } else {
            $this->error("✗ Error al cambiar al schema: {$schema}");
            return 1;
        }
    }

    /**
     * Clona la estructura de un schema a otro
     */
    protected function cloneSchema()
    {
        $source = $this->option('source');
        $target = $this->option('target');

        if (!$source || !$target) {
            $this->error('Debe especificar --source=schema_origen y --target=schema_destino');
            return 1;
        }

        if (!$this->confirm("¿Está seguro de clonar '{$source}' a '{$target}'? Esto copiará solo la estructura.")) {
            $this->info('Operación cancelada.');
            return 0;
        }

        $this->info("Clonando estructura de '{$source}' a '{$target}'...");

        if (SchemaManager::cloneSchemaStructure($source, $target)) {
            $this->info("✓ Estructura clonada exitosamente.");
            return 0;
        } else {
            $this->error("✗ Error al clonar estructura.");
            return 1;
        }
    }

    /**
     * Muestra estadísticas de un schema
     */
    protected function showStats()
    {
        $schema = $this->option('schema');

        if (!$schema) {
            // Mostrar stats de todos los schemas
            $schemas = SchemaManager::listSchemas();
            
            $this->info('Estadísticas de Schemas:');
            $this->newLine();

            $tableData = [];
            foreach ($schemas as $schemaName) {
                $stats = SchemaManager::getSchemaStats($schemaName);
                $tableData[] = [
                    $stats['schema'],
                    $stats['tables_count'] ?? 'N/A',
                    $stats['sequences_count'] ?? 'N/A',
                    $stats['size'] ?? 'N/A',
                ];
            }

            $this->table(
                ['Schema', 'Tablas', 'Secuencias', 'Tamaño'],
                $tableData
            );
        } else {
            // Mostrar stats de un schema específico
            $stats = SchemaManager::getSchemaStats($schema);

            if (isset($stats['error'])) {
                $this->error("Error: {$stats['error']}");
                return 1;
            }

            $this->info("Estadísticas del schema: {$schema}");
            $this->newLine();

            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Número de tablas', $stats['tables_count']],
                    ['Número de secuencias', $stats['sequences_count']],
                    ['Tamaño total', $stats['size']],
                ]
            );
        }

        return 0;
    }
}
