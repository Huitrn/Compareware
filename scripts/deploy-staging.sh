#!/bin/bash

# ========================================
# COMPAREWARE - DEPLOYMENT SCRIPT STAGING
# ========================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
ENVIRONMENT="staging"
PROJECT_DIR="/var/www/compareware-staging"
BACKUP_DIR="/var/backups/compareware-staging"
LOG_FILE="/var/log/compareware-staging-deploy.log"
SLACK_WEBHOOK_URL="" # Add your Slack webhook URL here

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
    send_notification "❌ Staging deployment failed: $1"
    exit 1
}

send_notification() {
    if [ ! -z "$SLACK_WEBHOOK_URL" ]; then
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"$1\"}" \
            "$SLACK_WEBHOOK_URL" > /dev/null 2>&1 || true
    fi
}

# Enhanced pre-deployment checks for staging
pre_deployment_checks() {
    log "Starting enhanced pre-deployment checks for $ENVIRONMENT..."
    
    # Basic checks
    if [ ! -d "$PROJECT_DIR" ]; then
        error "Project directory $PROJECT_DIR does not exist"
    fi
    
    if [ ! -w "$PROJECT_DIR" ]; then
        error "No write permissions to $PROJECT_DIR"
    fi
    
    # Check backup directory
    if [ ! -d "$BACKUP_DIR" ]; then
        mkdir -p "$BACKUP_DIR"
        log "Created backup directory $BACKUP_DIR"
    fi
    
    # Check required services
    if ! systemctl is-active --quiet postgresql; then
        error "PostgreSQL service is not running"
    fi
    
    if ! systemctl is-active --quiet redis; then
        error "Redis service is not running"
    fi
    
    if ! systemctl is-active --quiet nginx; then
        error "Nginx service is not running"
    fi
    
    # Check disk space (minimum 1GB free)
    AVAILABLE_SPACE=$(df "$PROJECT_DIR" | tail -1 | awk '{print $4}')
    if [ "$AVAILABLE_SPACE" -lt 1048576 ]; then
        error "Insufficient disk space (less than 1GB available)"
    fi
    
    # Check load average
    LOAD_AVERAGE=$(uptime | awk -F'load average:' '{ print $2 }' | awk '{ print $1 }' | sed 's/,//')
    if (( $(echo "$LOAD_AVERAGE > 5.0" | bc -l) )); then
        warning "High load average detected: $LOAD_AVERAGE"
    fi
    
    success "Enhanced pre-deployment checks passed"
}

# Create comprehensive backup
create_backup() {
    log "Creating comprehensive backup..."
    
    BACKUP_NAME="compareware-staging-$(date +%Y%m%d-%H%M%S)"
    BACKUP_PATH="$BACKUP_DIR/$BACKUP_NAME"
    
    mkdir -p "$BACKUP_PATH"
    
    # Backup application files
    tar -czf "$BACKUP_PATH/app.tar.gz" -C "$(dirname $PROJECT_DIR)" "$(basename $PROJECT_DIR)"
    
    # Backup database with schema
    pg_dump -U compareware_staging -h staging-db.compareware.com \
        --schema-only compareware_staging > "$BACKUP_PATH/schema.sql"
    pg_dump -U compareware_staging -h staging-db.compareware.com \
        --data-only compareware_staging > "$BACKUP_PATH/data.sql"
    
    # Backup Redis data
    redis-cli --rdb "$BACKUP_PATH/redis.rdb" > /dev/null
    
    # Keep only last 10 backups
    ls -t "$BACKUP_DIR" | tail -n +11 | xargs -d '\n' rm -rf --
    
    success "Comprehensive backup created at $BACKUP_PATH"
}

# Deploy with zero-downtime
deploy_application() {
    log "Deploying application with zero-downtime strategy..."
    
    cd "$PROJECT_DIR"
    
    # Enable maintenance mode
    php artisan down --message="Deploying new version" --retry=60
    
    # Pull from staging branch with verification
    git fetch origin
    git checkout staging
    
    # Verify we're on the correct commit
    CURRENT_COMMIT=$(git rev-parse HEAD)
    REMOTE_COMMIT=$(git rev-parse origin/staging)
    
    if [ "$CURRENT_COMMIT" != "$REMOTE_COMMIT" ]; then
        git pull origin staging
        log "Updated to commit: $(git rev-parse --short HEAD)"
    else
        log "Already up to date"
    fi
    
    # Copy environment file
    cp ".env.$ENVIRONMENT" ".env"
    
    # Install dependencies with verification
    composer install --no-dev --optimize-autoloader --no-interaction
    composer dump-autoload --optimize
    
    # Run tests before continuing
    if [ -f "phpunit.xml" ]; then
        log "Running tests..."
        php vendor/bin/phpunit --testsuite=Feature --stop-on-failure
        success "Tests passed"
    fi
    
    # Clear all caches
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    php artisan event:clear
    
    # Run migrations safely
    php artisan migrate --force
    
    # Seed database if needed
    php artisan db:seed --class=ProductionSeeder --force
    
    # Warm up caches
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    
    # Disable maintenance mode
    php artisan up
    
    success "Laravel application deployed successfully"
}

# Deploy Node.js API with PM2 ecosystem
deploy_node_api() {
    log "Deploying Node.js API with PM2..."
    
    cd "$PROJECT_DIR/JavaS/api-node"
    
    # Install production dependencies
    npm ci --only=production
    
    # Copy environment configuration
    cp ".env.$ENVIRONMENT" ".env"
    
    # Update PM2 ecosystem
    if [ -f "ecosystem.config.js" ]; then
        pm2 reload ecosystem.config.js --env staging
    else
        pm2 restart compareware-api-staging || \
        pm2 start app.js --name compareware-api-staging \
            --instances 2 --env staging \
            --merge-logs --log-date-format="YYYY-MM-DD HH:mm:ss"
    fi
    
    # Wait for service to be ready
    sleep 10
    
    # Verify API is responding
    for i in {1..5}; do
        if curl -f http://localhost:4000/api/health > /dev/null 2>&1; then
            success "Node.js API is responding"
            break
        fi
        if [ $i -eq 5 ]; then
            error "Node.js API failed to start"
        fi
        sleep 5
    done
    
    success "Node.js API deployed successfully"
}

# Enhanced post-deployment
post_deployment() {
    log "Running enhanced post-deployment tasks..."
    
    cd "$PROJECT_DIR"
    
    # Set optimal permissions
    find storage -type f -exec chmod 644 {} \;
    find storage -type d -exec chmod 755 {} \;
    find bootstrap/cache -type f -exec chmod 644 {} \;
    find bootstrap/cache -type d -exec chmod 755 {} \;
    chown -R www-data:www-data storage bootstrap/cache
    
    # Optimize OPcache
    if php -m | grep -q "Zend OPcache"; then
        php artisan opcache:clear
        # Warm up OPcache
        curl -s http://staging.compareware.com > /dev/null 2>&1 &
    fi
    
    # Restart services gracefully
    systemctl reload nginx
    systemctl restart php8.2-fpm
    
    # Clear and warm up Redis cache
    redis-cli FLUSHDB
    php artisan cache:warm 2>/dev/null || true
    
    # Update sitemap
    php artisan sitemap:generate 2>/dev/null || true
    
    success "Enhanced post-deployment tasks completed"
}

# Comprehensive health check
health_check() {
    log "Performing comprehensive health check..."
    
    # Laravel health check
    cd "$PROJECT_DIR"
    
    if ! php artisan --version > /dev/null 2>&1; then
        error "Laravel application failed health check"
    fi
    
    # Database connectivity with query test
    if ! php artisan tinker --execute="DB::select('SELECT 1');" > /dev/null 2>&1; then
        error "Database health check failed"
    fi
    
    # Redis connectivity
    if ! php artisan tinker --execute="Redis::ping();" > /dev/null 2>&1; then
        error "Redis health check failed"
    fi
    
    # Web server response
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://staging.compareware.com)
    if [ "$HTTP_STATUS" != "200" ]; then
        error "Web server returned status $HTTP_STATUS"
    fi
    
    # Node.js API health
    API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://staging-api.compareware.com/api/health)
    if [ "$API_STATUS" != "200" ]; then
        error "Node.js API returned status $API_STATUS"
    fi
    
    # SSL certificate check
    CERT_DAYS=$(echo | openssl s_client -servername staging.compareware.com \
        -connect staging.compareware.com:443 2>/dev/null | \
        openssl x509 -noout -dates | grep notAfter | cut -d= -f2 | \
        xargs -I {} date -d {} +%s)
    CURRENT_TIME=$(date +%s)
    DAYS_LEFT=$(( (CERT_DAYS - CURRENT_TIME) / 86400 ))
    
    if [ "$DAYS_LEFT" -lt 30 ]; then
        warning "SSL certificate expires in $DAYS_LEFT days"
    fi
    
    success "Comprehensive health check passed"
}

# Intelligent rollback
rollback() {
    log "Starting intelligent rollback..."
    
    send_notification "🔄 Starting rollback for staging environment"
    
    LATEST_BACKUP=$(ls -t "$BACKUP_DIR" | head -n1)
    
    if [ -z "$LATEST_BACKUP" ]; then
        error "No backup found for rollback"
    fi
    
    log "Rolling back to $LATEST_BACKUP..."
    
    # Enable maintenance mode
    cd "$PROJECT_DIR"
    php artisan down --message="Rolling back to previous version"
    
    # Backup current state before rollback
    mv "$PROJECT_DIR" "$PROJECT_DIR.rollback-$(date +%Y%m%d-%H%M%S)"
    
    # Restore application
    mkdir -p "$PROJECT_DIR"
    tar -xzf "$BACKUP_DIR/$LATEST_BACKUP/app.tar.gz" -C "$(dirname $PROJECT_DIR)"
    
    # Restore database
    cd "$PROJECT_DIR"
    psql -U compareware_staging -h staging-db.compareware.com \
        compareware_staging < "$BACKUP_DIR/$LATEST_BACKUP/schema.sql"
    psql -U compareware_staging -h staging-db.compareware.com \
        compareware_staging < "$BACKUP_DIR/$LATEST_BACKUP/data.sql"
    
    # Restore Redis
    redis-cli --rdb "$BACKUP_DIR/$LATEST_BACKUP/redis.rdb"
    
    # Restart services
    pm2 restart all
    systemctl restart php8.2-fpm
    systemctl reload nginx
    
    # Disable maintenance mode
    php artisan up
    
    send_notification "✅ Rollback completed successfully for staging"
    success "Rollback completed successfully"
}

# Main function with enhanced error handling
main() {
    log "Starting deployment process for $ENVIRONMENT environment"
    send_notification "🚀 Starting deployment to staging environment"
    
    case "$1" in
        "deploy")
            pre_deployment_checks
            create_backup
            deploy_application
            deploy_node_api
            post_deployment
            health_check
            send_notification "✅ Staging deployment completed successfully"
            ;;
        "rollback")
            rollback
            ;;
        "health")
            health_check
            ;;
        *)
            echo "Usage: $0 {deploy|rollback|health}"
            exit 1
            ;;
    esac
    
    success "Process completed successfully!"
}

# Execute with error tracking
trap 'error "Script failed at line $LINENO"' ERR
main "$@"