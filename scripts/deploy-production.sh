#!/bin/bash

# ========================================
# COMPAREWARE - DEPLOYMENT SCRIPT PRODUCTION
# Maximum security and zero-downtime deployment
# ========================================

set -euo pipefail  # Strict error handling

# Colors for output
readonly RED='\033[0;31m'
readonly GREEN='\033[0;32m'
readonly YELLOW='\033[1;33m'
readonly BLUE='\033[0;34m'
readonly NC='\033[0m'

# Configuration
readonly ENVIRONMENT="production"
readonly PROJECT_DIR="/var/www/compareware-production"
readonly BACKUP_DIR="/var/backups/compareware-production"
readonly LOG_FILE="/var/log/compareware-production-deploy.log"
readonly SLACK_WEBHOOK_URL="${SLACK_WEBHOOK_URL:-}"
readonly DEPLOY_USER="deploy"
readonly WEB_USER="www-data"

# Security checks
check_deployment_user() {
    if [[ "$(whoami)" != "$DEPLOY_USER" ]]; then
        error "Must run as $DEPLOY_USER user"
    fi
}

# Enhanced logging with rotation
setup_logging() {
    # Rotate logs if larger than 100MB
    if [[ -f "$LOG_FILE" && $(stat -f%z "$LOG_FILE" 2>/dev/null || stat -c%s "$LOG_FILE") -gt 104857600 ]]; then
        mv "$LOG_FILE" "${LOG_FILE}.$(date +%Y%m%d)"
        gzip "${LOG_FILE}.$(date +%Y%m%d)"
    fi
    
    # Ensure log file exists
    touch "$LOG_FILE"
    chmod 640 "$LOG_FILE"
}

log() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "${BLUE}[$timestamp] $1${NC}" | tee -a "$LOG_FILE"
}

success() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "${GREEN}[$timestamp] [SUCCESS] $1${NC}" | tee -a "$LOG_FILE"
}

warning() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "${YELLOW}[$timestamp] [WARNING] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "${RED}[$timestamp] [ERROR] $1${NC}" | tee -a "$LOG_FILE"
    send_notification "🚨 PRODUCTION DEPLOYMENT FAILED: $1" "danger"
    exit 1
}

# Enhanced notification system
send_notification() {
    local message="$1"
    local urgency="${2:-info}"
    
    # Slack notification
    if [[ -n "$SLACK_WEBHOOK_URL" ]]; then
        local color
        case "$urgency" in
            "danger") color="#ff0000" ;;
            "warning") color="#ffaa00" ;;
            "success") color="#00ff00" ;;
            *) color="#0099cc" ;;
        esac
        
        curl -X POST -H 'Content-type: application/json' \
            --data "{
                \"attachments\": [{
                    \"color\": \"$color\",
                    \"fields\": [{
                        \"title\": \"CompareWare Production Deployment\",
                        \"value\": \"$message\",
                        \"short\": false
                    }]
                }]
            }" \
            "$SLACK_WEBHOOK_URL" &>/dev/null || true
    fi
    
    # Email notification for critical issues
    if [[ "$urgency" == "danger" ]]; then
        echo "$message" | mail -s "CRITICAL: CompareWare Production Deployment Failed" ops@compareware.com || true
    fi
}

# Comprehensive pre-deployment security checks
pre_deployment_checks() {
    log "Starting comprehensive pre-deployment security checks..."
    
    # Basic security checks
    check_deployment_user
    
    # File system checks
    [[ -d "$PROJECT_DIR" ]] || error "Project directory does not exist"
    [[ -w "$PROJECT_DIR" ]] || error "No write permissions to project directory"
    [[ -d "$BACKUP_DIR" ]] || { mkdir -p "$BACKUP_DIR" && chmod 750 "$BACKUP_DIR"; }
    
    # Service availability checks
    local services=("postgresql" "redis" "nginx" "php8.2-fpm")
    for service in "${services[@]}"; do
        systemctl is-active --quiet "$service" || error "$service is not running"
    done
    
    # Resource checks
    local available_space
    available_space=$(df "$PROJECT_DIR" | tail -1 | awk '{print $4}')
    [[ "$available_space" -gt 2097152 ]] || error "Insufficient disk space (less than 2GB)"
    
    local available_memory
    available_memory=$(free -m | awk 'NR==2{print $7}')
    [[ "$available_memory" -gt 512 ]] || warning "Low available memory: ${available_memory}MB"
    
    # Network connectivity checks
    local external_endpoints=("8.8.8.8" "1.1.1.1" "github.com")
    for endpoint in "${external_endpoints[@]}"; do
        ping -c 1 "$endpoint" &>/dev/null || warning "Cannot reach $endpoint"
    done
    
    # SSL certificate validation
    local cert_file="/etc/ssl/certs/compareware.com.pem"
    if [[ -f "$cert_file" ]]; then
        local cert_expiry
        cert_expiry=$(openssl x509 -enddate -noout -in "$cert_file" | cut -d= -f2)
        local expiry_timestamp
        expiry_timestamp=$(date -d "$cert_expiry" +%s)
        local current_timestamp
        current_timestamp=$(date +%s)
        local days_left=$(( (expiry_timestamp - current_timestamp) / 86400 ))
        
        [[ "$days_left" -gt 7 ]] || error "SSL certificate expires in $days_left days"
        [[ "$days_left" -gt 30 ]] || warning "SSL certificate expires in $days_left days"
    fi
    
    # Database connectivity with transaction test
    cd "$PROJECT_DIR"
    php artisan tinker --execute="
        DB::beginTransaction();
        DB::select('SELECT 1 as test');
        DB::rollback();
    " &>/dev/null || error "Database transaction test failed"
    
    # Load balancer health check
    local lb_endpoints=("https://compareware.com/health" "https://www.compareware.com/health")
    for endpoint in "${lb_endpoints[@]}"; do
        local status_code
        status_code=$(curl -s -o /dev/null -w "%{http_code}" "$endpoint" || echo "000")
        [[ "$status_code" == "200" ]] || warning "Load balancer endpoint $endpoint returned $status_code"
    done
    
    success "All pre-deployment security checks passed"
}

# Blue-Green deployment strategy
create_comprehensive_backup() {
    log "Creating comprehensive production backup..."
    
    local backup_name="compareware-prod-$(date +%Y%m%d-%H%M%S)"
    local backup_path="$BACKUP_DIR/$backup_name"
    
    mkdir -p "$backup_path"
    chmod 750 "$backup_path"
    
    # Application backup with checksums
    log "Backing up application files..."
    tar -czf "$backup_path/app.tar.gz" -C "$(dirname "$PROJECT_DIR")" "$(basename "$PROJECT_DIR")"
    sha256sum "$backup_path/app.tar.gz" > "$backup_path/app.tar.gz.sha256"
    
    # Database backup with compression and encryption
    log "Backing up database..."
    pg_dump -U compareware_prod -h prod-master.compareware.com \
        -W compareware_production | \
        gzip | \
        gpg --cipher-algo AES256 --compress-algo 1 \
            --symmetric --output "$backup_path/database.sql.gz.gpg"
    
    # Redis backup
    log "Backing up Redis data..."
    redis-cli -h prod-redis-cluster.compareware.com \
        --rdb "$backup_path/redis.rdb"
    gzip "$backup_path/redis.rdb"
    
    # Configuration backup
    log "Backing up configuration..."
    tar -czf "$backup_path/config.tar.gz" \
        /etc/nginx/sites-available/compareware.com \
        /etc/php/8.2/fpm/pool.d/compareware.conf \
        "$PROJECT_DIR/.env.production" 2>/dev/null || true
    
    # Cleanup old backups (keep last 30 days)
    find "$BACKUP_DIR" -type d -name "compareware-prod-*" -mtime +30 -exec rm -rf {} + || true
    
    success "Comprehensive backup created: $backup_name"
    echo "$backup_name" > /tmp/latest_backup
}

# Zero-downtime deployment
deploy_application() {
    log "Starting zero-downtime deployment..."
    
    send_notification "🔄 Production deployment in progress" "warning"
    
    cd "$PROJECT_DIR"
    
    # Create deployment marker
    touch .deployment_in_progress
    
    # Verify we're on the main branch
    local current_branch
    current_branch=$(git rev-parse --abbrev-ref HEAD)
    [[ "$current_branch" == "main" ]] || error "Not on main branch (currently on $current_branch)"
    
    # Fetch and verify signatures
    git fetch origin --verify-signatures main
    git verify-commit HEAD || warning "Commit signature verification failed"
    
    # Check for new commits
    local current_commit local_commit remote_commit
    current_commit=$(git rev-parse HEAD)
    remote_commit=$(git rev-parse origin/main)
    
    if [[ "$current_commit" == "$remote_commit" ]]; then
        log "Already up to date with remote"
    else
        log "Updating from $current_commit to $remote_commit"
        git pull origin main
    fi
    
    # Environment configuration
    cp ".env.production" ".env"
    chmod 640 ".env"
    
    # Dependency management with verification
    log "Installing dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction --no-suggest
    composer validate --no-check-publish --no-check-all
    composer audit || warning "Composer audit found issues"
    
    # Asset compilation
    log "Compiling assets..."
    npm ci --production --silent
    npm run production
    
    # Pre-deployment tests
    log "Running pre-deployment tests..."
    if [[ -f "phpunit.xml" ]]; then
        php vendor/bin/phpunit --testsuite=Production --stop-on-failure --no-coverage
    fi
    
    # Cache management
    log "Managing caches..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    
    # Database migrations with backup point
    log "Running database migrations..."
    php artisan migrate --force
    
    # Cache warming
    log "Warming caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    
    # Remove deployment marker
    rm -f .deployment_in_progress
    
    success "Application deployed successfully"
}

# High-availability Node.js deployment
deploy_node_api() {
    log "Deploying Node.js API with high availability..."
    
    cd "$PROJECT_DIR/JavaS/api-node"
    
    # Install dependencies
    npm ci --only=production --silent
    npm audit --audit-level moderate || warning "npm audit found issues"
    
    # Environment setup
    cp ".env.production" ".env"
    chmod 640 ".env"
    
    # Rolling deployment with PM2 ecosystem
    if [[ -f "ecosystem.production.js" ]]; then
        pm2 reload ecosystem.production.js --env production
    else
        # Graceful restart of existing instances
        pm2 gracefulReload compareware-api-production || {
            pm2 start app.js \
                --name compareware-api-production \
                --instances max \
                --exec-mode cluster \
                --env production \
                --max-memory-restart 500M \
                --merge-logs \
                --log-date-format="YYYY-MM-DD HH:mm:ss Z"
        }
    fi
    
    # Health check with retries
    local retries=0
    local max_retries=10
    while [[ $retries -lt $max_retries ]]; do
        if curl -f -m 10 https://api.compareware.com/api/health &>/dev/null; then
            success "Node.js API is healthy"
            break
        fi
        
        ((retries++))
        [[ $retries -lt $max_retries ]] || error "Node.js API failed to start after $max_retries attempts"
        
        log "API not ready, waiting... (attempt $retries/$max_retries)"
        sleep 10
    done
    
    success "Node.js API deployed with high availability"
}

# Production-grade post-deployment
post_deployment() {
    log "Running production-grade post-deployment tasks..."
    
    cd "$PROJECT_DIR"
    
    # Security hardening
    find . -type f -name "*.php" -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    find storage -type f -exec chmod 644 {} \;
    find storage -type d -exec chmod 755 {} \;
    find bootstrap/cache -type f -exec chmod 644 {} \;
    find bootstrap/cache -type d -exec chmod 755 {} \;
    
    chown -R "$WEB_USER:$WEB_USER" storage bootstrap/cache
    chown "$WEB_USER:$WEB_USER" .env
    
    # Remove sensitive files
    rm -f .env.example .gitignore .editorconfig
    
    # OPcache optimization
    if php -m | grep -q "Zend OPcache"; then
        php artisan opcache:clear
        # Warm up OPcache by hitting key endpoints
        local endpoints=("/" "/api/health" "/comparadora")
        for endpoint in "${endpoints[@]}"; do
            curl -s "https://compareware.com$endpoint" &>/dev/null &
        done
    fi
    
    # Service management
    systemctl reload nginx
    systemctl restart php8.2-fpm
    
    # Clear and optimize Redis
    redis-cli -h prod-redis-cluster.compareware.com FLUSHDB
    php artisan cache:warm 2>/dev/null || true
    
    # Update search index
    php artisan scout:import "App\\Models\\Product" 2>/dev/null || true
    
    # Generate sitemap
    php artisan sitemap:generate 2>/dev/null || true
    
    # CDN cache invalidation
    if command -v aws &>/dev/null; then
        aws cloudfront create-invalidation \
            --distribution-id E1234567890ABC \
            --paths "/*" &>/dev/null || true
    fi
    
    success "Production post-deployment tasks completed"
}

# Multi-layer health check
comprehensive_health_check() {
    log "Performing comprehensive production health check..."
    
    local checks_passed=0
    local total_checks=0
    
    # Application layer
    ((total_checks++))
    cd "$PROJECT_DIR"
    if php artisan --version &>/dev/null; then
        ((checks_passed++))
        success "✓ Laravel application layer"
    else
        error "✗ Laravel application layer failed"
    fi
    
    # Database layer
    ((total_checks++))
    if php artisan tinker --execute="
        \$start = microtime(true);
        DB::select('SELECT COUNT(*) FROM products');
        \$time = (microtime(true) - \$start) * 1000;
        echo \$time < 100 ? 'OK' : 'SLOW';
    " 2>/dev/null | grep -q "OK"; then
        ((checks_passed++))
        success "✓ Database layer performance"
    else
        error "✗ Database layer performance check failed"
    fi
    
    # Cache layer
    ((total_checks++))
    if php artisan tinker --execute="
        Cache::put('health_check', 'ok', 60);
        echo Cache::get('health_check') === 'ok' ? 'OK' : 'FAIL';
    " 2>/dev/null | grep -q "OK"; then
        ((checks_passed++))
        success "✓ Cache layer"
    else
        error "✗ Cache layer failed"
    fi
    
    # Web server layer
    ((total_checks++))
    local response_time
    response_time=$(curl -o /dev/null -s -w "%{time_total}" https://compareware.com/)
    if (( $(echo "$response_time < 2.0" | bc -l) )); then
        ((checks_passed++))
        success "✓ Web server response time: ${response_time}s"
    else
        error "✗ Web server response time too slow: ${response_time}s"
    fi
    
    # API layer
    ((total_checks++))
    local api_response
    api_response=$(curl -s https://api.compareware.com/api/health)
    if echo "$api_response" | jq -e '.status == "ok"' &>/dev/null; then
        ((checks_passed++))
        success "✓ API layer"
    else
        error "✗ API layer failed"
    fi
    
    # SSL/Security layer
    ((total_checks++))
    local ssl_grade
    ssl_grade=$(curl -s "https://api.ssllabs.com/api/v3/analyze?host=compareware.com&publish=off&maxAge=1" | \
        jq -r '.endpoints[0].grade // "Unknown"' 2>/dev/null || echo "Unknown")
    if [[ "$ssl_grade" =~ ^[AB] ]]; then
        ((checks_passed++))
        success "✓ SSL grade: $ssl_grade"
    else
        warning "⚠ SSL grade: $ssl_grade"
    fi
    
    # Performance monitoring
    ((total_checks++))
    local load_avg
    load_avg=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | tr -d ',')
    if (( $(echo "$load_avg < 2.0" | bc -l) )); then
        ((checks_passed++))
        success "✓ System load: $load_avg"
    else
        warning "⚠ High system load: $load_avg"
    fi
    
    # Final assessment
    local success_rate
    success_rate=$((checks_passed * 100 / total_checks))
    log "Health check results: $checks_passed/$total_checks checks passed ($success_rate%)"
    
    if [[ $success_rate -ge 80 ]]; then
        success "Comprehensive health check passed"
        return 0
    else
        error "Health check failed - success rate below threshold"
        return 1
    fi
}

# Production rollback with safeguards
production_rollback() {
    log "Initiating production rollback with safeguards..."
    
    send_notification "🚨 PRODUCTION ROLLBACK INITIATED" "danger"
    
    # Confirmation prompt
    echo -e "${RED}WARNING: This will rollback the production environment!${NC}"
    read -p "Type 'ROLLBACK' to confirm: " -r
    if [[ $REPLY != "ROLLBACK" ]]; then
        log "Rollback cancelled"
        exit 1
    fi
    
    local backup_name
    if [[ -f /tmp/latest_backup ]]; then
        backup_name=$(cat /tmp/latest_backup)
    else
        backup_name=$(ls -t "$BACKUP_DIR" | head -n1)
    fi
    
    [[ -n "$backup_name" ]] || error "No backup found for rollback"
    
    log "Rolling back to: $backup_name"
    
    # Enable maintenance mode
    cd "$PROJECT_DIR" && php artisan down --message="Emergency rollback in progress"
    
    # Create emergency backup of current state
    local emergency_backup="emergency-$(date +%Y%m%d-%H%M%S)"
    tar -czf "$BACKUP_DIR/$emergency_backup.tar.gz" -C "$(dirname "$PROJECT_DIR")" "$(basename "$PROJECT_DIR")"
    
    # Restore application
    rm -rf "$PROJECT_DIR.rollback"
    mv "$PROJECT_DIR" "$PROJECT_DIR.rollback"
    mkdir -p "$PROJECT_DIR"
    tar -xzf "$BACKUP_DIR/$backup_name/app.tar.gz" -C "$(dirname "$PROJECT_DIR")"
    
    # Restore database
    cd "$PROJECT_DIR"
    if [[ -f "$BACKUP_DIR/$backup_name/database.sql.gz.gpg" ]]; then
        gpg --decrypt "$BACKUP_DIR/$backup_name/database.sql.gz.gpg" | \
        gunzip | \
        psql -U compareware_prod -h prod-master.compareware.com compareware_production
    fi
    
    # Restart all services
    pm2 restart all
    systemctl restart php8.2-fpm nginx redis
    
    # Disable maintenance mode
    php artisan up
    
    # Immediate health check
    if comprehensive_health_check; then
        send_notification "✅ Production rollback completed successfully" "success"
        success "Rollback completed successfully"
    else
        send_notification "🚨 Rollback completed but health check failed" "danger"
        error "Rollback completed but system is unhealthy"
    fi
}

# Main orchestration function
main() {
    setup_logging
    log "=== CompareWare Production Deployment Started ==="
    
    case "${1:-}" in
        "deploy")
            pre_deployment_checks
            create_comprehensive_backup
            deploy_application
            deploy_node_api
            post_deployment
            comprehensive_health_check
            send_notification "🎉 Production deployment completed successfully!" "success"
            ;;
        "rollback")
            production_rollback
            ;;
        "health")
            comprehensive_health_check
            ;;
        "backup")
            create_comprehensive_backup
            ;;
        *)
            echo "Usage: $0 {deploy|rollback|health|backup}"
            echo ""
            echo "Commands:"
            echo "  deploy   - Deploy to production with full safety checks"
            echo "  rollback - Emergency rollback to previous version"
            echo "  health   - Comprehensive health and performance check"
            echo "  backup   - Create full system backup"
            exit 1
            ;;
    esac
    
    log "=== Process completed successfully ==="
}

# Error handling and cleanup
cleanup() {
    local exit_code=$?
    if [[ -f "$PROJECT_DIR/.deployment_in_progress" ]]; then
        rm -f "$PROJECT_DIR/.deployment_in_progress"
    fi
    
    if [[ $exit_code -ne 0 ]]; then
        send_notification "💥 Production deployment script failed with exit code $exit_code" "danger"
    fi
    
    exit $exit_code
}

trap cleanup EXIT
trap 'error "Deployment interrupted by signal"' INT TERM

# Execute with maximum security
umask 027
main "$@"