/**
 * Sistema Avanzado de Logging de Seguridad
 * Proyecto: Compareware - Desarrollo Backend
 * Propósito: Logging comprehensivo para auditoría y análisis forense de seguridad
 */

const fs = require('fs');
const path = require('path');
const { createHash } = require('crypto');

class SecurityLogger {
  constructor(options = {}) {
    this.config = {
      logDirectory: options.logDirectory || path.join(__dirname, '../logs'),
      maxFileSize: options.maxFileSize || 10 * 1024 * 1024, // 10MB
      maxFiles: options.maxFiles || 30, // 30 archivos de rotación
      logLevel: options.logLevel || 'INFO',
      enableConsole: options.enableConsole !== false,
      enableFile: options.enableFile !== false,
      enableDatabase: options.enableDatabase || false,
      ...options
    };

    // Niveles de log
    this.logLevels = {
      'TRACE': 0,
      'DEBUG': 1,
      'INFO': 2,
      'WARN': 3,
      'ERROR': 4,
      'FATAL': 5
    };

    // Tipos de eventos de seguridad
    this.securityEventTypes = {
      'SQL_INJECTION_ATTEMPT': 'CRITICAL',
      'SQL_INJECTION_BLOCKED': 'HIGH',
      'AUTHENTICATION_FAILURE': 'MEDIUM',
      'AUTHORIZATION_FAILURE': 'MEDIUM',
      'RATE_LIMIT_EXCEEDED': 'MEDIUM',
      'SUSPICIOUS_USER_AGENT': 'MEDIUM',
      'BRUTE_FORCE_ATTEMPT': 'HIGH',
      'DATA_BREACH_ATTEMPT': 'CRITICAL',
      'PRIVILEGE_ESCALATION': 'CRITICAL',
      'MALICIOUS_FILE_UPLOAD': 'HIGH',
      'XSS_ATTEMPT': 'HIGH',
      'CSRF_ATTEMPT': 'HIGH',
      'DIRECTORY_TRAVERSAL': 'HIGH',
      'COMMAND_INJECTION': 'CRITICAL',
      'SESSION_HIJACKING': 'HIGH',
      'IP_BLACKLISTED': 'MEDIUM',
      'WAF_BYPASS_ATTEMPT': 'HIGH',
      'ANOMALOUS_BEHAVIOR': 'MEDIUM',
      'SECURITY_SCAN_DETECTED': 'MEDIUM',
      'ADMIN_ACCESS': 'INFO',
      'CONFIGURATION_CHANGE': 'INFO'
    };

    // Estadísticas en memoria
    this.stats = {
      totalEvents: 0,
      eventsByType: new Map(),
      eventsByLevel: new Map(),
      eventsByIP: new Map(),
      startTime: new Date()
    };

    // Asegurar que el directorio de logs existe
    this.ensureLogDirectory();

    // Inicializar archivos de log
    this.initializeLogFiles();
  }

  /**
   * Asegurar que el directorio de logs existe
   */
  ensureLogDirectory() {
    if (!fs.existsSync(this.config.logDirectory)) {
      fs.mkdirSync(this.config.logDirectory, { recursive: true });
    }
  }

  /**
   * Inicializar archivos de log
   */
  initializeLogFiles() {
    const today = new Date().toISOString().split('T')[0];
    
    this.logFiles = {
      security: path.join(this.config.logDirectory, `security_${today}.log`),
      access: path.join(this.config.logDirectory, `access_${today}.log`),
      error: path.join(this.config.logDirectory, `error_${today}.log`),
      audit: path.join(this.config.logDirectory, `audit_${today}.log`),
      performance: path.join(this.config.logDirectory, `performance_${today}.log`)
    };
  }

  /**
   * Log principal de eventos de seguridad
   */
  logSecurityEvent(eventType, data = {}) {
    const eventLevel = this.securityEventTypes[eventType] || 'INFO';
    
    const logEntry = {
      timestamp: new Date().toISOString(),
      eventType: eventType,
      level: eventLevel,
      severity: this.mapLevelToSeverity(eventLevel),
      eventId: this.generateEventId(),
      sessionId: data.sessionId || null,
      userId: data.userId || null,
      ip: data.ip || 'UNKNOWN',
      userAgent: data.userAgent || null,
      route: data.route || null,
      method: data.method || null,
      message: data.message || '',
      details: data.details || {},
      stackTrace: data.stackTrace || null,
      correlationId: data.correlationId || null,
      geolocation: data.geolocation || null,
      riskScore: this.calculateRiskScore(eventType, data),
      tags: this.generateTags(eventType, data)
    };

    // Actualizar estadísticas
    this.updateStats(logEntry);

    // Escribir a diferentes destinos
    this.writeToConsole(logEntry);
    this.writeToFile(logEntry, 'security');
    
    if (eventLevel === 'CRITICAL' || eventLevel === 'HIGH') {
      this.writeToFile(logEntry, 'audit');
      this.triggerAlert(logEntry);
    }

    return logEntry;
  }

  /**
   * Log de acceso HTTP
   */
  logHTTPAccess(req, res, responseTime) {
    const logEntry = {
      timestamp: new Date().toISOString(),
      type: 'HTTP_ACCESS',
      method: req.method,
      url: req.originalUrl || req.url,
      ip: req.ip || req.connection?.remoteAddress,
      userAgent: req.get('User-Agent'),
      referer: req.get('Referer'),
      statusCode: res.statusCode,
      responseTime: responseTime,
      contentLength: res.get('content-length') || 0,
      userId: req.user?.id || null,
      sessionId: req.sessionID || null
    };

    this.writeToFile(logEntry, 'access');

    // Log accesos sospechosos
    if (this.isSuspiciousAccess(logEntry)) {
      this.logSecurityEvent('SUSPICIOUS_ACCESS', {
        ip: logEntry.ip,
        route: logEntry.url,
        userAgent: logEntry.userAgent,
        details: logEntry
      });
    }
  }

  /**
   * Log de errores de aplicación
   */
  logError(error, context = {}) {
    const logEntry = {
      timestamp: new Date().toISOString(),
      type: 'APPLICATION_ERROR',
      level: 'ERROR',
      message: error.message,
      stack: error.stack,
      name: error.name,
      code: error.code || null,
      context: context,
      ip: context.ip || null,
      userId: context.userId || null,
      route: context.route || null
    };

    this.writeToFile(logEntry, 'error');
    this.writeToConsole(logEntry);

    // Si el error parece relacionado con seguridad, loguearlo también como evento de seguridad
    if (this.isSecurityRelatedError(error)) {
      this.logSecurityEvent('SECURITY_ERROR', {
        message: error.message,
        details: logEntry,
        ip: context.ip
      });
    }
  }

  /**
   * Log de auditoría para cambios importantes
   */
  logAuditEvent(action, resource, oldValue, newValue, userId, context = {}) {
    const logEntry = {
      timestamp: new Date().toISOString(),
      type: 'AUDIT_EVENT',
      action: action,
      resource: resource,
      resourceId: context.resourceId || null,
      userId: userId,
      ip: context.ip || null,
      userAgent: context.userAgent || null,
      oldValue: oldValue ? this.sanitizeForLogging(oldValue) : null,
      newValue: newValue ? this.sanitizeForLogging(newValue) : null,
      changeHash: this.generateChangeHash(oldValue, newValue),
      sessionId: context.sessionId || null,
      additionalData: context.additionalData || {}
    };

    this.writeToFile(logEntry, 'audit');
    
    // Log como evento de seguridad si es un cambio crítico
    if (this.isCriticalChange(action, resource)) {
      this.logSecurityEvent('CRITICAL_CHANGE', {
        message: `${action} performed on ${resource}`,
        userId: userId,
        ip: context.ip,
        details: logEntry
      });
    }
  }

  /**
   * Log de métricas de performance
   */
  logPerformance(metric, value, context = {}) {
    const logEntry = {
      timestamp: new Date().toISOString(),
      type: 'PERFORMANCE_METRIC',
      metric: metric,
      value: value,
      unit: context.unit || 'ms',
      route: context.route || null,
      method: context.method || null,
      userId: context.userId || null,
      additionalMetrics: context.additionalMetrics || {}
    };

    this.writeToFile(logEntry, 'performance');

    // Alertar si la performance es anómalamente lenta (posible DoS)
    if (this.isAnomalousPerformance(metric, value)) {
      this.logSecurityEvent('PERFORMANCE_ANOMALY', {
        message: `Anomalous performance detected: ${metric} = ${value}`,
        details: logEntry
      });
    }
  }

  /**
   * Escribir a consola
   */
  writeToConsole(logEntry) {
    if (!this.config.enableConsole) return;

    const level = logEntry.level || 'INFO';
    const timestamp = logEntry.timestamp;
    const message = logEntry.message || logEntry.type || 'No message';

    let emoji = '📝';
    let color = '\x1b[37m'; // Blanco

    switch (level) {
      case 'CRITICAL':
        emoji = '🚨';
        color = '\x1b[41m\x1b[37m'; // Fondo rojo, texto blanco
        break;
      case 'HIGH':
        emoji = '⚠️ ';
        color = '\x1b[31m'; // Rojo
        break;
      case 'MEDIUM':
        emoji = '🟡';
        color = '\x1b[33m'; // Amarillo
        break;
      case 'INFO':
        emoji = '📄';
        color = '\x1b[36m'; // Cian
        break;
      case 'ERROR':
        emoji = '❌';
        color = '\x1b[31m'; // Rojo
        break;
    }

    console.log(`${color}${emoji} [${timestamp}] ${level}: ${message}\x1b[0m`);
    
    if (logEntry.ip) {
      console.log(`  IP: ${logEntry.ip}`);
    }
    
    if (logEntry.route) {
      console.log(`  Route: ${logEntry.route}`);
    }
  }

  /**
   * Escribir a archivo
   */
  writeToFile(logEntry, fileType = 'security') {
    if (!this.config.enableFile) return;

    try {
      const logFile = this.logFiles[fileType];
      const logLine = JSON.stringify(logEntry) + '\n';

      // Verificar rotación de archivos
      this.checkFileRotation(logFile);

      fs.appendFileSync(logFile, logLine);
    } catch (error) {
      console.error('Error writing to log file:', error);
    }
  }

  /**
   * Verificar si necesita rotar archivos de log
   */
  checkFileRotation(logFile) {
    try {
      if (fs.existsSync(logFile)) {
        const stats = fs.statSync(logFile);
        if (stats.size > this.config.maxFileSize) {
          this.rotateLogFile(logFile);
        }
      }
    } catch (error) {
      console.error('Error checking file rotation:', error);
    }
  }

  /**
   * Rotar archivo de log
   */
  rotateLogFile(logFile) {
    try {
      const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
      const rotatedFile = logFile.replace('.log', `_${timestamp}.log`);
      
      fs.renameSync(logFile, rotatedFile);
      
      // Comprimir archivo rotado si es posible
      this.compressLogFile(rotatedFile);
      
      // Limpiar archivos antiguos
      this.cleanOldLogFiles();
      
    } catch (error) {
      console.error('Error rotating log file:', error);
    }
  }

  /**
   * Comprimir archivo de log (requiere zlib)
   */
  compressLogFile(logFile) {
    try {
      const zlib = require('zlib');
      const gzip = zlib.createGzip();
      const source = fs.createReadStream(logFile);
      const destination = fs.createWriteStream(logFile + '.gz');
      
      source.pipe(gzip).pipe(destination);
      
      destination.on('close', () => {
        fs.unlinkSync(logFile); // Eliminar archivo original
      });
    } catch (error) {
      console.error('Error compressing log file:', error);
    }
  }

  /**
   * Limpiar archivos de log antiguos
   */
  cleanOldLogFiles() {
    try {
      const files = fs.readdirSync(this.config.logDirectory)
        .filter(file => file.endsWith('.log') || file.endsWith('.log.gz'))
        .map(file => ({
          name: file,
          time: fs.statSync(path.join(this.config.logDirectory, file)).mtime
        }))
        .sort((a, b) => b.time - a.time);

      // Eliminar archivos que excedan el límite
      if (files.length > this.config.maxFiles) {
        const filesToDelete = files.slice(this.config.maxFiles);
        filesToDelete.forEach(file => {
          fs.unlinkSync(path.join(this.config.logDirectory, file.name));
        });
      }
    } catch (error) {
      console.error('Error cleaning old log files:', error);
    }
  }

  /**
   * Actualizar estadísticas
   */
  updateStats(logEntry) {
    this.stats.totalEvents++;

    // Por tipo de evento
    const eventType = logEntry.eventType || logEntry.type;
    this.stats.eventsByType.set(eventType, 
      (this.stats.eventsByType.get(eventType) || 0) + 1
    );

    // Por nivel
    const level = logEntry.level;
    this.stats.eventsByLevel.set(level, 
      (this.stats.eventsByLevel.get(level) || 0) + 1
    );

    // Por IP (solo para eventos de seguridad)
    if (logEntry.ip && logEntry.eventType) {
      this.stats.eventsByIP.set(logEntry.ip, 
        (this.stats.eventsByIP.get(logEntry.ip) || 0) + 1
      );
    }
  }

  /**
   * Calcular score de riesgo
   */
  calculateRiskScore(eventType, data) {
    let score = 0;

    // Score base por tipo de evento
    const baseScores = {
      'SQL_INJECTION_ATTEMPT': 10,
      'BRUTE_FORCE_ATTEMPT': 8,
      'DATA_BREACH_ATTEMPT': 10,
      'PRIVILEGE_ESCALATION': 10,
      'AUTHENTICATION_FAILURE': 3,
      'RATE_LIMIT_EXCEEDED': 2
    };

    score += baseScores[eventType] || 1;

    // Modificadores basados en contexto
    if (data.details?.riskScore) {
      score += data.details.riskScore;
    }

    if (data.ip && this.stats.eventsByIP.get(data.ip) > 5) {
      score += 3; // IP con historial de ataques
    }

    return Math.min(score, 10); // Máximo 10
  }

  /**
   * Generar tags para clasificación
   */
  generateTags(eventType, data) {
    const tags = ['security'];

    if (eventType.includes('SQL')) tags.push('sql-injection');
    if (eventType.includes('AUTH')) tags.push('authentication');
    if (eventType.includes('RATE')) tags.push('rate-limiting');
    if (eventType.includes('BRUTE')) tags.push('brute-force');
    if (data.ip) tags.push(`ip-${data.ip.replace(/\./g, '-')}`);
    if (data.userAgent && data.userAgent.includes('bot')) tags.push('bot');

    return tags;
  }

  /**
   * Detectar acceso sospechoso
   */
  isSuspiciousAccess(logEntry) {
    // User-Agents sospechosos
    const suspiciousUA = [
      'sqlmap', 'nikto', 'burp', 'nessus', 'acunetix'
    ];

    const userAgent = (logEntry.userAgent || '').toLowerCase();
    if (suspiciousUA.some(ua => userAgent.includes(ua))) {
      return true;
    }

    // Códigos de estado sospechosos frecuentes
    if ([403, 404, 500].includes(logEntry.statusCode) && 
        logEntry.responseTime > 5000) {
      return true;
    }

    // URLs sospechosas
    const suspiciousPaths = [
      'admin', 'config', 'backup', '.git', '.env', 'phpmyadmin'
    ];

    const url = logEntry.url || '';
    if (suspiciousPaths.some(path => url.includes(path))) {
      return true;
    }

    return false;
  }

  /**
   * Detectar errores relacionados con seguridad
   */
  isSecurityRelatedError(error) {
    const securityKeywords = [
      'sql', 'injection', 'unauthorized', 'forbidden', 
      'authentication', 'authorization', 'token', 'session'
    ];

    const message = error.message.toLowerCase();
    return securityKeywords.some(keyword => message.includes(keyword));
  }

  /**
   * Detectar cambios críticos
   */
  isCriticalChange(action, resource) {
    const criticalActions = ['DELETE', 'DROP', 'GRANT', 'REVOKE'];
    const criticalResources = ['users', 'permissions', 'config', 'admin'];

    return criticalActions.includes(action.toUpperCase()) ||
           criticalResources.some(res => resource.toLowerCase().includes(res));
  }

  /**
   * Detectar performance anómala
   */
  isAnomalousPerformance(metric, value) {
    const thresholds = {
      'response_time': 10000, // 10 segundos
      'db_query_time': 5000,  // 5 segundos
      'memory_usage': 500 * 1024 * 1024, // 500MB
      'cpu_usage': 80 // 80%
    };

    return value > (thresholds[metric] || Infinity);
  }

  /**
   * Generar ID único de evento
   */
  generateEventId() {
    const timestamp = Date.now().toString();
    const random = Math.random().toString(36).substring(2);
    return `${timestamp}_${random}`;
  }

  /**
   * Generar hash de cambio
   */
  generateChangeHash(oldValue, newValue) {
    const data = JSON.stringify({ old: oldValue, new: newValue });
    return createHash('md5').update(data).digest('hex');
  }

  /**
   * Mapear nivel a severidad numérica
   */
  mapLevelToSeverity(level) {
    const severityMap = {
      'INFO': 1,
      'LOW': 2,
      'MEDIUM': 3,
      'HIGH': 4,
      'CRITICAL': 5
    };

    return severityMap[level] || 1;
  }

  /**
   * Sanitizar datos para logging (remover info sensible)
   */
  sanitizeForLogging(data) {
    if (typeof data !== 'object' || data === null) {
      return data;
    }

    const sensitiveFields = [
      'password', 'token', 'secret', 'key', 'auth', 
      'credit_card', 'ssn', 'social_security'
    ];

    const sanitized = { ...data };

    for (const field of sensitiveFields) {
      if (field in sanitized) {
        sanitized[field] = '[REDACTED]';
      }
    }

    return sanitized;
  }

  /**
   * Trigger de alertas para eventos críticos
   */
  triggerAlert(logEntry) {
    // Aquí puedes implementar notificaciones (email, Slack, SMS, etc.)
    console.log('🚨 ALERTA DE SEGURIDAD:', {
      type: logEntry.eventType,
      level: logEntry.level,
      ip: logEntry.ip,
      message: logEntry.message
    });

    // Ejemplo: enviar a webhook de notificaciones
    // this.sendWebhookAlert(logEntry);
  }

  /**
   * Obtener estadísticas actuales
   */
  getStatistics() {
    const uptime = Date.now() - this.stats.startTime.getTime();
    
    return {
      uptime: uptime,
      totalEvents: this.stats.totalEvents,
      eventsPerMinute: (this.stats.totalEvents / (uptime / 60000)).toFixed(2),
      eventsByType: Object.fromEntries(this.stats.eventsByType),
      eventsByLevel: Object.fromEntries(this.stats.eventsByLevel),
      topIPs: Array.from(this.stats.eventsByIP.entries())
        .sort((a, b) => b[1] - a[1])
        .slice(0, 10)
        .map(([ip, count]) => ({ ip, count }))
    };
  }

  /**
   * Búsqueda en logs
   */
  searchLogs(criteria = {}) {
    // Esta función implementaría búsqueda en los archivos de log
    // Por simplicidad, aquí solo retornamos un placeholder
    return {
      message: 'Log search functionality - implement based on your needs',
      criteria: criteria
    };
  }
}

// Instancia singleton
const securityLogger = new SecurityLogger();

module.exports = {
  SecurityLogger,
  securityLogger
};