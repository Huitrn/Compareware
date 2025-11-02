@echo off
REM ========================================
REM SCRIPT PARA CAMBIO DINÁMICO DE AMBIENTES - WINDOWS
REM CompareWare Multi-Environment Switcher
REM ========================================

setlocal enabledelayedexpansion

set ENVIRONMENTS=sandbox staging production
set LARAVEL_DIR=.
set API_DIR=JavaS\api-node

REM Función para mostrar banner
:show_banner
echo =======================================
echo    COMPAREWARE ENVIRONMENT SWITCHER
echo =======================================
echo.
goto :eof

REM Función para mostrar ayuda
:show_help
echo Uso: %~nx0 [ambiente]
echo.
echo Ambientes disponibles:
echo   sandbox     - Desarrollo y pruebas internas
echo   staging     - Testing de integración (ambiental)
echo   production  - Ambiente productivo
echo.
echo Ejemplos:
echo   %~nx0 sandbox    # Cambiar a ambiente sandbox
echo   %~nx0 staging    # Cambiar a ambiente staging
echo   %~nx0 production # Cambiar a ambiente production
echo.
echo Si no se especifica ambiente, se mostrará el ambiente actual.
goto :eof

REM Función para validar ambiente
:validate_environment
set valid=false
for %%e in (%ENVIRONMENTS%) do (
    if "%1"=="%%e" set valid=true
)
if "%valid%"=="false" (
    echo Error: Ambiente '%1' no válido
    exit /b 1
)
exit /b 0

REM Función para mostrar ambiente actual
:show_current_environment
if exist "%LARAVEL_DIR%\.env" (
    for /f "tokens=2 delims==" %%a in ('findstr "APP_ENV=" "%LARAVEL_DIR%\.env"') do set current_env=%%a
    echo Ambiente actual: !current_env!
    
    for /f "tokens=2 delims==" %%a in ('findstr "APP_NAME=" "%LARAVEL_DIR%\.env"') do set app_name=%%a
    for /f "tokens=2 delims==" %%a in ('findstr "APP_URL=" "%LARAVEL_DIR%\.env"') do set app_url=%%a
    for /f "tokens=2 delims==" %%a in ('findstr "DB_HOST=" "%LARAVEL_DIR%\.env"') do set db_host=%%a
    
    echo   - Aplicación: !app_name!
    echo   - URL: !app_url!
    echo   - Base de datos: !db_host!
) else (
    echo No se encontró archivo .env
)
goto :eof

REM Función para hacer backup
:backup_current_env
set timestamp=%date:~-4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set timestamp=%timestamp: =0%
if exist "%LARAVEL_DIR%\.env" (
    copy "%LARAVEL_DIR%\.env" "%LARAVEL_DIR%\.env.backup.%timestamp%" >nul
    echo Backup creado: .env.backup.%timestamp%
)
goto :eof

REM Función para cambiar ambiente Laravel
:switch_laravel_env
set target_env=%1
set env_file=%LARAVEL_DIR%\.env.%target_env%

if not exist "%env_file%" (
    echo Error: No existe el archivo %env_file%
    exit /b 1
)

echo Cambiando ambiente Laravel a: %target_env%

REM Crear backup
call :backup_current_env

REM Copiar nuevo archivo
copy "%env_file%" "%LARAVEL_DIR%\.env" >nul

REM Limpiar cache de Laravel
echo Limpiando cache de Laravel...
php artisan config:clear >nul 2>&1 || echo No se pudo limpiar config cache
php artisan cache:clear >nul 2>&1 || echo No se pudo limpiar application cache  
php artisan route:clear >nul 2>&1 || echo No se pudo limpiar route cache
php artisan view:clear >nul 2>&1 || echo No se pudo limpiar view cache

echo ✓ Ambiente Laravel cambiado a %target_env%
exit /b 0

REM Función para cambiar ambiente API
:switch_api_env
set target_env=%1
set api_env_file=%API_DIR%\.env.%target_env%
set api_main_env=%API_DIR%\.env

if not exist "%api_env_file%" (
    echo Error: No existe el archivo %api_env_file%
    exit /b 1
)

echo Cambiando ambiente API Node.js a: %target_env%

REM Crear backup si existe
if exist "%api_main_env%" (
    set timestamp=%date:~-4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
    set timestamp=!timestamp: =0!
    copy "%api_main_env%" "%API_DIR%\.env.backup.!timestamp!" >nul
)

REM Copiar nuevo archivo
copy "%api_env_file%" "%api_main_env%" >nul

echo ✓ Ambiente API cambiado a %target_env%
exit /b 0

REM Función para mostrar info del ambiente
:show_environment_info
set env=%1
echo =======================================
echo    INFORMACIÓN DEL AMBIENTE: %env%
echo =======================================

if "%env%"=="sandbox" (
    echo 🏖️  SANDBOX (Desarrollo^)
    echo   - Puerto API: 3000
    echo   - Base de datos: Local (sandbox_db^)
    echo   - SSL: Deshabilitado
    echo   - Logging: Debug (7 días^)
    echo   - Monitoreo: Básico
    echo   - URL: http://sandbox.compareware.local
) else if "%env%"=="staging" (
    echo 🎭 STAGING (Ambiental/Testing^)
    echo   - Puerto API: 3500
    echo   - Base de datos: Cluster (staging-db.compareware.com^)
    echo   - SSL: Habilitado
    echo   - Logging: Info (14 días^)
    echo   - Monitoreo: Medio (Slack alerts^)
    echo   - URL: https://staging.compareware.com
) else if "%env%"=="production" (
    echo 🚀 PRODUCTION (Productivo^)
    echo   - Puerto API: 4000
    echo   - Base de datos: Master/Replica (prod-master.compareware.com^)
    echo   - SSL: Strict
    echo   - Logging: Error only (30 días^)
    echo   - Monitoreo: Completo (Slack + Sentry + SMS^)
    echo   - URL: https://compareware.com
)
echo.
goto :eof

REM Función principal
:main
call :show_banner

REM Si no hay argumentos, mostrar ambiente actual
if "%1"=="" (
    call :show_current_environment
    echo.
    call :show_help
    exit /b 0
)

set target_env=%1

REM Mostrar ayuda
if "%target_env%"=="-h" goto show_help
if "%target_env%"=="--help" goto show_help

REM Validar ambiente
call :validate_environment %target_env%
if errorlevel 1 (
    echo.
    call :show_help
    exit /b 1
)

REM Mostrar información del ambiente
call :show_environment_info %target_env%

REM Confirmar cambio
set /p confirm="¿Desea cambiar al ambiente %target_env%? (y/N): "
if /i not "%confirm%"=="y" (
    echo Operación cancelada
    exit /b 0
)

echo Iniciando cambio de ambiente...

REM Cambiar Laravel
call :switch_laravel_env %target_env%
if errorlevel 1 (
    echo Error al cambiar ambiente Laravel
    exit /b 1
)

REM Cambiar API
call :switch_api_env %target_env%
if errorlevel 1 (
    echo Error al cambiar ambiente API
    exit /b 1
)

echo.
echo 🎉 ¡Cambio de ambiente completado exitosamente!
echo Ambiente actual: %target_env%
echo.
echo Comandos sugeridos para completar el cambio:
echo   php artisan migrate --env=%target_env%    # Ejecutar migraciones
echo   php artisan serve --port=8000           # Iniciar servidor Laravel
echo   cd JavaS\api-node ^&^& npm start         # Iniciar API Node.js
echo.

goto :eof

REM Ejecutar función principal
call :main %*