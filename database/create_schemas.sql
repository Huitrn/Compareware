-- ========================================
-- SCRIPT DE INICIALIZACIÓN DE SCHEMAS
-- CompareWare - Multi-ambiente en una sola BD
-- ========================================
-- 
-- Este script crea y configura los schemas para cada ambiente:
-- - sandbox: Para desarrollo y experimentación
-- - staging: Para testing y QA
-- - public: Para producción (ya existe por defecto)
--
-- IMPORTANTE: Ejecutar este script UNA SOLA VEZ
-- después de crear la base de datos Compareware
-- ========================================

-- Conectar a la base de datos Compareware
\c Compareware;

-- ========================================
-- 1. CREAR SCHEMAS
-- ========================================

-- Schema para Sandbox (desarrollo)
CREATE SCHEMA IF NOT EXISTS sandbox;
COMMENT ON SCHEMA sandbox IS 'Schema para ambiente de desarrollo y experimentación';

-- Schema para Staging (pre-producción)
CREATE SCHEMA IF NOT EXISTS staging;
COMMENT ON SCHEMA staging IS 'Schema para ambiente de testing y QA';

-- El schema public ya existe y será usado para producción
COMMENT ON SCHEMA public IS 'Schema para ambiente de producción';

-- ========================================
-- 2. PERMISOS Y SEGURIDAD
-- ========================================

-- Otorgar permisos al usuario postgres en todos los schemas
GRANT ALL PRIVILEGES ON SCHEMA sandbox TO postgres;
GRANT ALL PRIVILEGES ON SCHEMA staging TO postgres;
GRANT ALL PRIVILEGES ON SCHEMA public TO postgres;

-- Permitir que postgres cree objetos en los schemas
ALTER DEFAULT PRIVILEGES IN SCHEMA sandbox GRANT ALL ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES IN SCHEMA staging GRANT ALL ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO postgres;

ALTER DEFAULT PRIVILEGES IN SCHEMA sandbox GRANT ALL ON SEQUENCES TO postgres;
ALTER DEFAULT PRIVILEGES IN SCHEMA staging GRANT ALL ON SEQUENCES TO postgres;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO postgres;

-- ========================================
-- 3. CONFIGURAR SEARCH_PATH
-- ========================================

-- Esto permite que cada conexión busque primero en su schema
-- y luego en public si no encuentra lo que busca

-- Para el usuario postgres (puedes agregar más usuarios si es necesario)
ALTER USER postgres SET search_path TO public;

-- ========================================
-- 4. COPIAR ESTRUCTURA DE PRODUCCIÓN A OTROS AMBIENTES
-- ========================================
-- NOTA: Este paso copia solo la ESTRUCTURA de las tablas,
-- no los datos. Ejecuta esto solo si ya tienes tablas en public.

-- Función auxiliar para copiar estructura de tablas
CREATE OR REPLACE FUNCTION copy_schema_structure(source_schema TEXT, target_schema TEXT)
RETURNS void AS $$
DECLARE
    table_record RECORD;
    seq_record RECORD;
BEGIN
    -- Copiar tablas (solo estructura, sin datos)
    FOR table_record IN 
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = source_schema 
        AND table_type = 'BASE TABLE'
    LOOP
        EXECUTE format('CREATE TABLE IF NOT EXISTS %I.%I (LIKE %I.%I INCLUDING ALL)',
            target_schema, table_record.table_name,
            source_schema, table_record.table_name);
    END LOOP;
    
    -- Copiar secuencias
    FOR seq_record IN
        SELECT sequence_name
        FROM information_schema.sequences
        WHERE sequence_schema = source_schema
    LOOP
        EXECUTE format('CREATE SEQUENCE IF NOT EXISTS %I.%I',
            target_schema, seq_record.sequence_name);
    END LOOP;
    
    RAISE NOTICE 'Estructura copiada de % a % exitosamente', source_schema, target_schema;
END;
$$ LANGUAGE plpgsql;

-- Descomentar estas líneas SOLO si ya tienes tablas en public y quieres copiarlas
-- SELECT copy_schema_structure('public', 'sandbox');
-- SELECT copy_schema_structure('public', 'staging');

-- ========================================
-- 5. DATOS DE PRUEBA PARA SANDBOX
-- ========================================
-- Puedes insertar datos de prueba aquí para el ambiente sandbox

-- Ejemplo (descomentar y ajustar según tus tablas):
/*
INSERT INTO sandbox.users (name, email, password, created_at, updated_at)
VALUES 
    ('Usuario Sandbox 1', 'sandbox1@test.com', '$2y$10$sandbox_hash', NOW(), NOW()),
    ('Usuario Sandbox 2', 'sandbox2@test.com', '$2y$10$sandbox_hash', NOW(), NOW());
*/

-- ========================================
-- 6. VERIFICACIÓN
-- ========================================

-- Verificar que los schemas fueron creados
SELECT schema_name, schema_owner
FROM information_schema.schemata
WHERE schema_name IN ('public', 'sandbox', 'staging')
ORDER BY schema_name;

-- Verificar permisos
SELECT 
    n.nspname AS schema_name,
    r.rolname AS owner
FROM pg_namespace n
JOIN pg_roles r ON n.nspowner = r.oid
WHERE n.nspname IN ('public', 'sandbox', 'staging')
ORDER BY n.nspname;

-- ========================================
-- INFORMACIÓN IMPORTANTE
-- ========================================
/*
 * Para usar cada ambiente en Laravel:
 * 
 * 1. SANDBOX:
 *    - Archivo: .env.sandbox
 *    - Variable: DB_SCHEMA=sandbox
 *    - Comando: php artisan config:cache
 * 
 * 2. STAGING:
 *    - Archivo: .env.staging
 *    - Variable: DB_SCHEMA=staging
 *    - Comando: php artisan config:cache
 * 
 * 3. PRODUCTION:
 *    - Archivo: .env (o .env.production)
 *    - Variable: DB_SCHEMA=public
 *    - Comando: php artisan config:cache
 * 
 * Para cambiar entre ambientes:
 *    .\scripts\switch-environment.bat [sandbox|staging|production]
 * 
 * Para ejecutar migraciones en un ambiente específico:
 *    php artisan migrate --env=sandbox
 *    php artisan migrate --env=staging
 *    php artisan migrate --env=production
 */

-- ========================================
-- FIN DEL SCRIPT
-- ========================================
