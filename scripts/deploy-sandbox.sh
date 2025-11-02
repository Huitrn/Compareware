#!/bin/bash

# ========================================
# COMPAREWARE - DEPLOYMENT SCRIPT SANDBOX
# ========================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
ENVIRONMENT="sandbox"
PROJECT_DIR="/var/www/compareware-sandbox"
BACKUP_DIR="/var/backups/compareware-sandbox"
LOG_FILE="/var/log/compareware-sandbox-deploy.log"

# Functions
log() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a $LOG_FILE
}

success() {
    echo -e "${GREEN}[SUCCESS] $1${NC}" | tee -a $LOG_FILE
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}" | tee -a $LOG_FILE
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" | tee -a $LOG_FILE
    exit 1
}

# Pre-deployment checks
pre_deployment_checks() {
    log "Starting pre-deployment checks for $ENVIRONMENT..."
    
    # Check if project directory exists
    if [ ! -d "$PROJECT_DIR" ]; then
        error "Project directory $PROJECT_DIR does not exist"
    fi
    
    # Check if we have write permissions
    if [ ! -w "$PROJECT_DIR" ]; then
        error "No write permissions to $PROJECT_DIR"
    fi
    
    # Check if backup directory exists, create if not
    if [ ! -d "$BACKUP_DIR" ]; then
        mkdir -p "$BACKUP_DIR"
        log "Created backup directory $BACKUP_DIR"
    fi
    
    # Check if Node.js is installed
    if ! command -v node &> /dev/null; then
        error "Node.js is not installed"
    fi
    
    # Check if PHP is installed
    if ! command -v php &> /dev/null; then
        error "PHP is not installed"
    fi
    
    # Check if Composer is installed
    if ! command -v composer &> /dev/null; then
        error "Composer is not installed"
    fi
    
    success "Pre-deployment checks passed"
}

# Create backup
create_backup() {
    log "Creating backup..."
    
    BACKUP_NAME="compareware-sandbox-$(date +%Y%m%d-%H%M%S)"
    BACKUP_PATH="$BACKUP_DIR/$BACKUP_NAME"
    
    # Create backup directory
    mkdir -p "$BACKUP_PATH"
    
    # Backup application files
    cp -r "$PROJECT_DIR" "$BACKUP_PATH/app"
    
    # Backup database (if PostgreSQL is running)
    if systemctl is-active --quiet postgresql; then
        pg_dump -U postgres compareware_sandbox > "$BACKUP_PATH/database.sql"
        success "Database backup created"
    else
        warning "PostgreSQL is not running, skipping database backup"
    fi
    
    success "Backup created at $BACKUP_PATH"
}

# Deploy application
deploy_application() {
    log "Deploying application for $ENVIRONMENT..."
    
    cd "$PROJECT_DIR"
    
    # Pull latest changes from develop branch
    git fetch origin
    git checkout develop
    git pull origin develop
    
    # Copy environment file
    if [ -f ".env.$ENVIRONMENT" ]; then
        cp ".env.$ENVIRONMENT" ".env"
        success "Environment file copied for $ENVIRONMENT"
    else
        error "Environment file .env.$ENVIRONMENT not found"
    fi
    
    # Install/Update Composer dependencies
    composer install --no-dev --optimize-autoloader
    
    # Clear and cache configuration
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    # Run migrations (with confirmation for sandbox)
    read -p "Run database migrations? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan migrate --force
        success "Database migrations completed"
    fi
    
    # Generate application key if not exists
    if [ ! grep -q "APP_KEY=" .env ] || [ -z "$(grep APP_KEY= .env | cut -d'=' -f2)" ]; then
        php artisan key:generate --force
        success "Application key generated"
    fi
    
    # Cache configuration
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    success "Laravel application deployed"
}

# Deploy Node.js API
deploy_node_api() {
    log "Deploying Node.js API for $ENVIRONMENT..."
    
    cd "$PROJECT_DIR/JavaS/api-node"
    
    # Install/Update npm dependencies
    npm ci --production
    
    # Copy environment file for Node.js
    if [ -f ".env.$ENVIRONMENT" ]; then
        cp ".env.$ENVIRONMENT" ".env"
        success "Node.js environment file copied"
    else
        warning "Node.js environment file .env.$ENVIRONMENT not found, using default"
    fi
    
    # Restart Node.js service (if using PM2)
    if command -v pm2 &> /dev/null; then
        pm2 restart compareware-api-sandbox || pm2 start app.js --name compareware-api-sandbox
        success "Node.js API service restarted"
    else
        warning "PM2 not found, please manually restart Node.js service"
    fi
    
    success "Node.js API deployed"
}

# Post-deployment tasks
post_deployment() {
    log "Running post-deployment tasks..."
    
    cd "$PROJECT_DIR"
    
    # Set proper permissions
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    
    # Restart services
    if systemctl is-active --quiet nginx; then
        systemctl reload nginx
        success "Nginx reloaded"
    fi
    
    if systemctl is-active --quiet php*-fpm; then
        systemctl restart php*-fpm
        success "PHP-FPM restarted"
    fi
    
    # Clear OPcache if enabled
    if php -m | grep -q "Zend OPcache"; then
        php artisan opcache:clear 2>/dev/null || true
    fi
    
    success "Post-deployment tasks completed"
}

# Health check
health_check() {
    log "Performing health check..."
    
    # Check Laravel application
    cd "$PROJECT_DIR"
    if php artisan --version > /dev/null 2>&1; then
        success "Laravel application is healthy"
    else
        error "Laravel application health check failed"
    fi
    
    # Check database connection
    if php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; then
        success "Database connection is healthy"
    else
        error "Database connection failed"
    fi
    
    # Check Node.js API (if URL is set)
    NODE_API_URL=$(grep NODE_API_URL .env | cut -d'=' -f2 | tr -d '"' | tr -d "'")
    if [ ! -z "$NODE_API_URL" ]; then
        if curl -f "$NODE_API_URL/api/health" > /dev/null 2>&1; then
            success "Node.js API is healthy"
        else
            warning "Node.js API health check failed"
        fi
    fi
    
    success "Health check completed"
}

# Rollback function
rollback() {
    log "Starting rollback..."
    
    # Find latest backup
    LATEST_BACKUP=$(ls -t "$BACKUP_DIR" | head -n1)
    
    if [ -z "$LATEST_BACKUP" ]; then
        error "No backup found for rollback"
    fi
    
    log "Rolling back to $LATEST_BACKUP..."
    
    # Restore application files
    rm -rf "$PROJECT_DIR.old"
    mv "$PROJECT_DIR" "$PROJECT_DIR.old"
    cp -r "$BACKUP_DIR/$LATEST_BACKUP/app" "$PROJECT_DIR"
    
    # Restore database
    if [ -f "$BACKUP_DIR/$LATEST_BACKUP/database.sql" ]; then
        psql -U postgres compareware_sandbox < "$BACKUP_DIR/$LATEST_BACKUP/database.sql"
        success "Database restored"
    fi
    
    success "Rollback completed"
}

# Main deployment process
main() {
    log "Starting deployment process for $ENVIRONMENT environment"
    
    case "$1" in
        "deploy")
            pre_deployment_checks
            create_backup
            deploy_application
            deploy_node_api
            post_deployment
            health_check
            ;;
        "rollback")
            rollback
            ;;
        "health")
            health_check
            ;;
        *)
            echo "Usage: $0 {deploy|rollback|health}"
            echo "  deploy  - Deploy application to sandbox"
            echo "  rollback - Rollback to previous version"
            echo "  health  - Perform health check"
            exit 1
            ;;
    esac
    
    success "Process completed successfully!"
}

# Execute main function with all arguments
main "$@"