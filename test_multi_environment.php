<?php

/**
 * Script de Verificación de Multi-Ambiente
 * 
 * Este script verifica que la configuración de schemas
 * esté funcionando correctamente en CompareWare
 * 
 * Ejecutar: php test_multi_environment.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Helpers\SchemaManager;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "  TEST DE MULTI-AMBIENTE COMPAREWARE  \n";
echo "========================================\n\n";

// Test 1: Información del ambiente
echo "✓ TEST 1: Información del Ambiente\n";
echo str_repeat("-", 40) . "\n";
$info = SchemaManager::getEnvironmentInfo();
foreach ($info as $key => $value) {
    if (is_array($value)) {
        echo "  $key: " . implode(', ', $value) . "\n";
    } else {
        echo "  $key: $value\n";
    }
}
echo "\n";

// Test 2: Listar schemas
echo "✓ TEST 2: Schemas Disponibles\n";
echo str_repeat("-", 40) . "\n";
$schemas = SchemaManager::listSchemas();
foreach ($schemas as $schema) {
    $exists = SchemaManager::schemaExists($schema) ? '✓' : '✗';
    echo "  $exists $schema\n";
}
echo "\n";

// Test 3: Verificar schema actual
echo "✓ TEST 3: Verificación de Schema Actual\n";
echo str_repeat("-", 40) . "\n";
$currentSchema = SchemaManager::getCurrentSchema();
$envSchema = SchemaManager::getEnvironmentSchema();
echo "  Schema actual: $currentSchema\n";
echo "  Schema esperado: $envSchema\n";

if ($currentSchema === $envSchema) {
    echo "  ✓ CORRECTO: Schema coincide con el ambiente\n";
} else {
    echo "  ✗ ADVERTENCIA: Schema no coincide\n";
    echo "  → Ejecute: php artisan schema:manage switch --schema=$envSchema\n";
}
echo "\n";

// Test 4: Estadísticas de schemas
echo "✓ TEST 4: Estadísticas de Schemas\n";
echo str_repeat("-", 40) . "\n";
foreach ($schemas as $schema) {
    $stats = SchemaManager::getSchemaStats($schema);
    if (isset($stats['error'])) {
        echo "  $schema: ERROR - {$stats['error']}\n";
    } else {
        echo "  $schema:\n";
        echo "    - Tablas: {$stats['tables_count']}\n";
        echo "    - Secuencias: {$stats['sequences_count']}\n";
        echo "    - Tamaño: {$stats['size']}\n";
    }
}
echo "\n";

// Test 5: Verificar configuración de .env
echo "✓ TEST 5: Configuración de Variables de Ambiente\n";
echo str_repeat("-", 40) . "\n";
$requiredVars = [
    'APP_ENV',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_SCHEMA',
];

$allGood = true;
foreach ($requiredVars as $var) {
    $value = env($var);
    if ($value !== null) {
        echo "  ✓ $var = $value\n";
    } else {
        echo "  ✗ $var no está definida\n";
        $allGood = false;
    }
}
echo "\n";

// Test 6: Conexión a la base de datos
echo "✓ TEST 6: Conexión a Base de Datos\n";
echo str_repeat("-", 40) . "\n";
try {
    DB::connection()->getPdo();
    echo "  ✓ Conexión exitosa\n";
    
    // Probar query simple
    $result = DB::select("SELECT version()");
    $version = $result[0]->version;
    echo "  PostgreSQL: $version\n";
} catch (\Exception $e) {
    echo "  ✗ Error de conexión: " . $e->getMessage() . "\n";
    $allGood = false;
}
echo "\n";

// Test 7: Verificar archivos .env
echo "✓ TEST 7: Archivos de Ambiente\n";
echo str_repeat("-", 40) . "\n";
$envFiles = [
    '.env.sandbox' => 'Sandbox',
    '.env.staging' => 'Staging',
    '.env.production' => 'Production',
];

foreach ($envFiles as $file => $name) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "  ✓ $name ($file): " . number_format($size) . " bytes\n";
    } else {
        echo "  ✗ $name ($file): NO EXISTE\n";
        $allGood = false;
    }
}
echo "\n";

// Test 8: Verificar helper SchemaManager
echo "✓ TEST 8: Helper SchemaManager\n";
echo str_repeat("-", 40) . "\n";
if (class_exists('App\Helpers\SchemaManager')) {
    echo "  ✓ Clase SchemaManager existe\n";
    
    $methods = [
        'getEnvironmentSchema',
        'setSchema',
        'getCurrentSchema',
        'listSchemas',
        'schemaExists',
    ];
    
    foreach ($methods as $method) {
        if (method_exists('App\Helpers\SchemaManager', $method)) {
            echo "  ✓ Método $method() disponible\n";
        } else {
            echo "  ✗ Método $method() NO EXISTE\n";
            $allGood = false;
        }
    }
} else {
    echo "  ✗ Clase SchemaManager no encontrada\n";
    $allGood = false;
}
echo "\n";

// Resumen final
echo "========================================\n";
if ($allGood) {
    echo "  🎉 TODOS LOS TESTS PASARON\n";
    echo "========================================\n\n";
    echo "✓ El sistema multi-ambiente está configurado correctamente.\n";
    echo "\nPróximos pasos:\n";
    echo "  1. Ejecutar: psql -U postgres -d Compareware -f database/create_schemas.sql\n";
    echo "  2. Cambiar ambiente: .\\scripts\\switch-environment.bat sandbox\n";
    echo "  3. Ejecutar migraciones: php artisan migrate\n";
    echo "  4. Ver información: php artisan schema:manage info\n";
} else {
    echo "  ⚠️  ALGUNOS TESTS FALLARON\n";
    echo "========================================\n\n";
    echo "✗ Revise los errores anteriores y corrija la configuración.\n";
    echo "\nConsulte: GUIA_MULTI_AMBIENTE.md\n";
}
echo "\n";
