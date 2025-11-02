#!/bin/bash

# ========================================
# SCRIPT PARA CAMBIO DINÁMICO DE AMBIENTES
# CompareWare Multi-Environment Switcher
# ========================================

set -e

ENVIRONMENTS=("sandbox" "staging" "production")
LARAVEL_DIR="."
API_DIR="JavaS/api-node"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar banner
show_banner() {
    echo -e "${BLUE}"
    echo "======================================="
    echo "   COMPAREWARE ENVIRONMENT SWITCHER"
    echo "======================================="
    echo -e "${NC}"
}

# Función para mostrar ayuda
show_help() {
    echo -e "${YELLOW}Uso: $0 [ambiente]${NC}"
    echo ""
    echo "Ambientes disponibles:"
    echo "  sandbox     - Desarrollo y pruebas internas"
    echo "  staging     - Testing de integración (ambiental)"
    echo "  production  - Ambiente productivo"
    echo ""
    echo "Ejemplos:"
    echo "  $0 sandbox    # Cambiar a ambiente sandbox"
    echo "  $0 staging    # Cambiar a ambiente staging"
    echo "  $0 production # Cambiar a ambiente production"
    echo ""
    echo "Si no se especifica ambiente, se mostrará el ambiente actual."
}

# Función para validar ambiente
validate_environment() {
    local env=$1
    for valid_env in "${ENVIRONMENTS[@]}"; do
        if [[ "$env" == "$valid_env" ]]; then
            return 0
        fi
    done
    return 1
}

# Función para mostrar ambiente actual
show_current_environment() {
    if [[ -f "$LARAVEL_DIR/.env" ]]; then
        local current_env=$(grep "APP_ENV=" "$LARAVEL_DIR/.env" | cut -d'=' -f2)
        echo -e "${GREEN}Ambiente actual: $current_env${NC}"
        
        # Mostrar información adicional
        local app_name=$(grep "APP_NAME=" "$LARAVEL_DIR/.env" | cut -d'=' -f2 | tr -d '"')
        local app_url=$(grep "APP_URL=" "$LARAVEL_DIR/.env" | cut -d'=' -f2)
        local db_host=$(grep "DB_HOST=" "$LARAVEL_DIR/.env" | cut -d'=' -f2)
        
        echo "  - Aplicación: $app_name"
        echo "  - URL: $app_url"
        echo "  - Base de datos: $db_host"
    else
        echo -e "${RED}No se encontró archivo .env${NC}"
    fi
}

# Función para hacer backup del .env actual
backup_current_env() {
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    if [[ -f "$LARAVEL_DIR/.env" ]]; then
        cp "$LARAVEL_DIR/.env" "$LARAVEL_DIR/.env.backup.$timestamp"
        echo -e "${YELLOW}Backup creado: .env.backup.$timestamp${NC}"
    fi
}

# Función para cambiar ambiente Laravel
switch_laravel_env() {
    local target_env=$1
    local env_file="$LARAVEL_DIR/.env.$target_env"
    
    if [[ ! -f "$env_file" ]]; then
        echo -e "${RED}Error: No existe el archivo $env_file${NC}"
        return 1
    fi
    
    echo -e "${YELLOW}Cambiando ambiente Laravel a: $target_env${NC}"
    
    # Crear backup
    backup_current_env
    
    # Copiar nuevo archivo de configuración
    cp "$env_file" "$LARAVEL_DIR/.env"
    
    # Limpiar cache de Laravel
    echo "Limpiando cache de Laravel..."
    php artisan config:clear 2>/dev/null || echo "No se pudo limpiar config cache"
    php artisan cache:clear 2>/dev/null || echo "No se pudo limpiar application cache"
    php artisan route:clear 2>/dev/null || echo "No se pudo limpiar route cache"
    php artisan view:clear 2>/dev/null || echo "No se pudo limpiar view cache"
    
    echo -e "${GREEN}✓ Ambiente Laravel cambiado a $target_env${NC}"
}

# Función para cambiar ambiente API Node.js
switch_api_env() {
    local target_env=$1
    local api_env_file="$API_DIR/.env.$target_env"
    local api_main_env="$API_DIR/.env"
    
    if [[ ! -f "$api_env_file" ]]; then
        echo -e "${RED}Error: No existe el archivo $api_env_file${NC}"
        return 1
    fi
    
    echo -e "${YELLOW}Cambiando ambiente API Node.js a: $target_env${NC}"
    
    # Crear backup si existe .env
    if [[ -f "$api_main_env" ]]; then
        local timestamp=$(date +"%Y%m%d_%H%M%S")
        cp "$api_main_env" "$API_DIR/.env.backup.$timestamp"
    fi
    
    # Copiar nuevo archivo de configuración
    cp "$api_env_file" "$api_main_env"
    
    echo -e "${GREEN}✓ Ambiente API cambiado a $target_env${NC}"
}

# Función para reiniciar servicios
restart_services() {
    local target_env=$1
    
    echo -e "${YELLOW}Reiniciando servicios para ambiente: $target_env${NC}"
    
    # Reiniciar API Node.js si está corriendo con PM2
    if command -v pm2 >/dev/null 2>&1; then
        if pm2 list | grep -q "compareware-api"; then
            echo "Reiniciando API Node.js..."
            pm2 restart compareware-api 2>/dev/null || echo "No se pudo reiniciar API con PM2"
        fi
    fi
    
    echo -e "${GREEN}✓ Servicios reiniciados${NC}"
}

# Función para mostrar información del ambiente
show_environment_info() {
    local env=$1
    
    echo -e "${BLUE}"
    echo "======================================="
    echo "   INFORMACIÓN DEL AMBIENTE: $env"
    echo "======================================="
    echo -e "${NC}"
    
    case $env in
        "sandbox")
            echo "🏖️  SANDBOX (Desarrollo)"
            echo "  - Puerto API: 3000"
            echo "  - Base de datos: Local (sandbox_db)"
            echo "  - SSL: Deshabilitado"
            echo "  - Logging: Debug (7 días)"
            echo "  - Monitoreo: Básico"
            echo "  - URL: http://sandbox.compareware.local"
            ;;
        "staging")
            echo "🎭 STAGING (Ambiental/Testing)"
            echo "  - Puerto API: 3500"
            echo "  - Base de datos: Cluster (staging-db.compareware.com)"
            echo "  - SSL: Habilitado"
            echo "  - Logging: Info (14 días)"
            echo "  - Monitoreo: Medio (Slack alerts)"
            echo "  - URL: https://staging.compareware.com"
            ;;
        "production")
            echo "🚀 PRODUCTION (Productivo)"
            echo "  - Puerto API: 4000"
            echo "  - Base de datos: Master/Replica (prod-master.compareware.com)"
            echo "  - SSL: Strict"
            echo "  - Logging: Error only (30 días)"
            echo "  - Monitoreo: Completo (Slack + Sentry + SMS)"
            echo "  - URL: https://compareware.com"
            ;;
    esac
    echo ""
}

# Función principal
main() {
    show_banner
    
    # Si no hay argumentos, mostrar ambiente actual
    if [[ $# -eq 0 ]]; then
        show_current_environment
        echo ""
        show_help
        exit 0
    fi
    
    local target_env=$1
    
    # Mostrar ayuda
    if [[ "$target_env" == "-h" || "$target_env" == "--help" ]]; then
        show_help
        exit 0
    fi
    
    # Validar ambiente
    if ! validate_environment "$target_env"; then
        echo -e "${RED}Error: Ambiente '$target_env' no válido${NC}"
        echo ""
        show_help
        exit 1
    fi
    
    # Mostrar información del ambiente objetivo
    show_environment_info "$target_env"
    
    # Confirmar cambio
    read -p "¿Desea cambiar al ambiente $target_env? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Operación cancelada${NC}"
        exit 0
    fi
    
    # Ejecutar cambio de ambiente
    echo -e "${BLUE}Iniciando cambio de ambiente...${NC}"
    
    # Cambiar Laravel
    if ! switch_laravel_env "$target_env"; then
        echo -e "${RED}Error al cambiar ambiente Laravel${NC}"
        exit 1
    fi
    
    # Cambiar API Node.js
    if ! switch_api_env "$target_env"; then
        echo -e "${RED}Error al cambiar ambiente API${NC}"
        exit 1
    fi
    
    # Reiniciar servicios
    restart_services "$target_env"
    
    echo ""
    echo -e "${GREEN}🎉 ¡Cambio de ambiente completado exitosamente!${NC}"
    echo -e "${GREEN}Ambiente actual: $target_env${NC}"
    echo ""
    
    # Mostrar comandos sugeridos
    echo -e "${YELLOW}Comandos sugeridos para completar el cambio:${NC}"
    echo "  php artisan migrate --env=$target_env    # Ejecutar migraciones"
    echo "  php artisan serve --port=8000           # Iniciar servidor Laravel"
    echo "  cd JavaS/api-node && npm start         # Iniciar API Node.js"
    echo ""
}

# Ejecutar función principal con todos los argumentos
main "$@"