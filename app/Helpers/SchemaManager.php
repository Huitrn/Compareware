<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * SchemaManager
 * 
 * Helper para gestionar dinámicamente schemas de PostgreSQL
 * según el ambiente activo (sandbox, staging, production)
 * 
 * Uso:
 *   SchemaManager::setSchema('sandbox');
 *   SchemaManager::getCurrentSchema();
 *   SchemaManager::resetToEnvironmentSchema();
 */
class SchemaManager
{
    /**
     * Obtiene el schema configurado para el ambiente actual
     *
     * @return string
     */
    public static function getEnvironmentSchema(): string
    {
        $env = config('app.env', 'production');
        
        return match($env) {
            'sandbox' => 'sandbox',
            'staging' => 'staging',
            'production' => 'public',
            default => 'public',
        };
    }

    /**
     * Establece el schema activo para las consultas
     *
     * @param string $schema
     * @return bool
     */
    public static function setSchema(string $schema): bool
    {
        try {
            // Validar que el schema existe
            $schemaExists = DB::select(
                "SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?",
                [$schema]
            );

            if (empty($schemaExists)) {
                Log::error("Schema '{$schema}' no existe en la base de datos");
                return false;
            }

            // Establecer el search_path
            DB::statement("SET search_path TO {$schema}, public");
            
            // Actualizar configuración en tiempo de ejecución
            Config::set('database.connections.pgsql.schema', $schema);
            
            Log::info("Schema cambiado exitosamente a: {$schema}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Error al cambiar schema: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Restablece el schema al configurado para el ambiente actual
     *
     * @return bool
     */
    public static function resetToEnvironmentSchema(): bool
    {
        $schema = self::getEnvironmentSchema();
        return self::setSchema($schema);
    }

    /**
     * Obtiene el schema actualmente activo
     *
     * @return string|null
     */
    public static function getCurrentSchema(): ?string
    {
        try {
            $result = DB::select("SELECT current_schema()");
            return $result[0]->current_schema ?? null;
        } catch (\Exception $e) {
            Log::error("Error al obtener schema actual: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lista todos los schemas disponibles en la base de datos
     *
     * @return array
     */
    public static function listSchemas(): array
    {
        try {
            $schemas = DB::select(
                "SELECT schema_name 
                 FROM information_schema.schemata 
                 WHERE schema_name NOT LIKE 'pg_%' 
                 AND schema_name != 'information_schema'
                 ORDER BY schema_name"
            );

            return array_map(fn($s) => $s->schema_name, $schemas);
        } catch (\Exception $e) {
            Log::error("Error al listar schemas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si un schema existe
     *
     * @param string $schema
     * @return bool
     */
    public static function schemaExists(string $schema): bool
    {
        try {
            $result = DB::select(
                "SELECT 1 FROM information_schema.schemata WHERE schema_name = ?",
                [$schema]
            );
            return !empty($result);
        } catch (\Exception $e) {
            Log::error("Error al verificar existencia de schema: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Copia la estructura de un schema a otro (sin datos)
     *
     * @param string $sourceSchema
     * @param string $targetSchema
     * @return bool
     */
    public static function cloneSchemaStructure(string $sourceSchema, string $targetSchema): bool
    {
        try {
            if (!self::schemaExists($sourceSchema)) {
                Log::error("Schema origen '{$sourceSchema}' no existe");
                return false;
            }

            // Crear schema destino si no existe
            if (!self::schemaExists($targetSchema)) {
                DB::statement("CREATE SCHEMA {$targetSchema}");
            }

            // Obtener todas las tablas del schema origen
            $tables = DB::select(
                "SELECT table_name 
                 FROM information_schema.tables 
                 WHERE table_schema = ? 
                 AND table_type = 'BASE TABLE'",
                [$sourceSchema]
            );

            foreach ($tables as $table) {
                $tableName = $table->table_name;
                
                // Copiar estructura de la tabla
                DB::statement(
                    "CREATE TABLE IF NOT EXISTS {$targetSchema}.{$tableName} 
                     (LIKE {$sourceSchema}.{$tableName} INCLUDING ALL)"
                );
            }

            Log::info("Estructura copiada de '{$sourceSchema}' a '{$targetSchema}' exitosamente");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Error al clonar estructura de schema: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información del ambiente actual
     *
     * @return array
     */
    public static function getEnvironmentInfo(): array
    {
        return [
            'app_env' => config('app.env'),
            'app_name' => config('app.name'),
            'current_schema' => self::getCurrentSchema(),
            'environment_schema' => self::getEnvironmentSchema(),
            'available_schemas' => self::listSchemas(),
            'database' => config('database.connections.pgsql.database'),
            'host' => config('database.connections.pgsql.host'),
        ];
    }

    /**
     * Ejecuta una consulta en un schema específico y luego vuelve al original
     *
     * @param string $schema
     * @param callable $callback
     * @return mixed
     */
    public static function executeInSchema(string $schema, callable $callback)
    {
        $originalSchema = self::getCurrentSchema();
        
        try {
            self::setSchema($schema);
            $result = $callback();
            return $result;
        } finally {
            if ($originalSchema) {
                self::setSchema($originalSchema);
            }
        }
    }

    /**
     * Obtiene estadísticas de un schema
     *
     * @param string $schema
     * @return array
     */
    public static function getSchemaStats(string $schema): array
    {
        try {
            // Contar tablas
            $tables = DB::select(
                "SELECT COUNT(*) as count 
                 FROM information_schema.tables 
                 WHERE table_schema = ? AND table_type = 'BASE TABLE'",
                [$schema]
            );

            // Contar secuencias
            $sequences = DB::select(
                "SELECT COUNT(*) as count 
                 FROM information_schema.sequences 
                 WHERE sequence_schema = ?",
                [$schema]
            );

            // Tamaño del schema
            $size = DB::select(
                "SELECT pg_size_pretty(SUM(pg_total_relation_size(quote_ident(schemaname)||'.'||quote_ident(tablename)))::bigint) as size
                 FROM pg_tables 
                 WHERE schemaname = ?",
                [$schema]
            );

            return [
                'schema' => $schema,
                'tables_count' => $tables[0]->count ?? 0,
                'sequences_count' => $sequences[0]->count ?? 0,
                'size' => $size[0]->size ?? 'N/A',
            ];
            
        } catch (\Exception $e) {
            Log::error("Error al obtener estadísticas de schema: " . $e->getMessage());
            return [
                'schema' => $schema,
                'error' => $e->getMessage(),
            ];
        }
    }
}
