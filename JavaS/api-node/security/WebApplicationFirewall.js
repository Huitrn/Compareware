/**
 * Web Application Firewall (WAF) para Compareware
 * Proyecto: Compareware - Desarrollo Backend
 * Propósito: Firewall de aplicación web para bloquear ataques SQL injection y otros
 */

const { advancedDetector } = require('./AdvancedSQLDetector');
const { sqlValidator } = require('./SQLSecurityValidator');
const { logSecurityEvent } = require('../middlewares/logging');

class WebApplicationFirewall {
  constructor(options = {}) {
    this.config = {
      // Configuración de bloqueo
      blockOnHighRisk: options.blockOnHighRisk !== false,
      blockOnCriticalRisk: options.blockOnCriticalRisk !== false,
      
      // Límites de rate limiting por IP
      maxRequestsPerMinute: options.maxRequestsPerMinute || 60,
      maxFailedAttemptsPerHour: options.maxFailedAttemptsPerHour || 10,
      
      // Configuración de geo-blocking
      allowedCountries: options.allowedCountries || [], // Vacío = todas permitidas
      blockedCountries: options.blockedCountries || [], // Countries to block
      
      // Configuración de User-Agent
      blockSuspiciousUserAgents: options.blockSuspiciousUserAgents !== false,
      
      // Configuración de logging
      logAllRequests: options.logAllRequests || false,
      logBlockedRequests: options.logBlockedRequests !== false,
      
      // Configuración de response
      customBlockMessage: options.customBlockMessage || 'Request blocked by security policy',
      
      ...options
    };

    // Contadores por IP
    this.ipStats = new Map();
    
    // Lista negra temporal
    this.blacklistedIPs = new Set();
    
    // Lista blanca (IPs siempre permitidas)
    this.whitelistedIPs = new Set(options.whitelistedIPs || []);

    // Patrones de User-Agent sospechosos
    this.suspiciousUserAgents = [
      /sqlmap/i,
      /nikto/i,
      /burp/i,
      /nessus/i,
      /acunetix/i,
      /w3af/i,
      /havij/i,
      /pangolin/i,
      /nmap/i,
      /masscan/i,
      /zap/i, // OWASP ZAP
      /gobuster/i,
      /dirb/i,
      /dirbuster/i,
      /curl.*python/i,
      /wget/i,
      /python-requests/i,
      /bot.*scan/i
    ];

    // Patrones de path sospechosos
    this.suspiciousPaths = [
      /\.php$/i,
      /\.asp$/i,
      /\.jsp$/i,
      /admin/i,
      /config/i,
      /backup/i,
      /\.git/i,
      /\.env/i,
      /wp-admin/i,
      /phpmyadmin/i,
      /dbadmin/i,
      /\.sql$/i,
      /\.bak$/i,
      /test/i
    ];

    // Headers requeridos para seguridad
    this.securityHeaders = {
      'X-Content-Type-Options': 'nosniff',
      'X-Frame-Options': 'DENY',
      'X-XSS-Protection': '1; mode=block',
      'Referrer-Policy': 'strict-origin-when-cross-origin',
      'Content-Security-Policy': "default-src 'self'",
      'Strict-Transport-Security': 'max-age=31536000; includeSubDomains'
    };
  }

  /**
   * Middleware principal del WAF
   */
  middleware() {
    return async (req, res, next) => {
      const startTime = Date.now();
      
      try {
        // Obtener información del cliente
        const clientInfo = this.extractClientInfo(req);
        
        // 1. Verificar lista blanca
        if (this.whitelistedIPs.has(clientInfo.ip)) {
          return next();
        }

        // 2. Verificar lista negra
        if (this.blacklistedIPs.has(clientInfo.ip)) {
          return this.blockRequest(req, res, 'IP_BLACKLISTED', clientInfo);
        }

        // 3. Rate Limiting
        const rateLimitCheck = this.checkRateLimit(clientInfo.ip);
        if (!rateLimitCheck.allowed) {
          return this.blockRequest(req, res, 'RATE_LIMIT_EXCEEDED', clientInfo, {
            details: rateLimitCheck
          });
        }

        // 4. Verificar User-Agent sospechoso
        const userAgentCheck = this.checkUserAgent(req.get('User-Agent'));
        if (!userAgentCheck.allowed) {
          return this.blockRequest(req, res, 'SUSPICIOUS_USER_AGENT', clientInfo, {
            details: userAgentCheck
          });
        }

        // 5. Verificar path sospechoso
        const pathCheck = this.checkSuspiciousPath(req.path);
        if (!pathCheck.allowed) {
          return this.blockRequest(req, res, 'SUSPICIOUS_PATH', clientInfo, {
            details: pathCheck
          });
        }

        // 6. Análisis de payload SQL injection
        const sqlAnalysis = await this.analyzeSQLInjection(req, clientInfo);
        if (!sqlAnalysis.allowed) {
          // Registrar intento de ataque
          this.registerAttackAttempt(clientInfo.ip, sqlAnalysis);
          
          return this.blockRequest(req, res, 'SQL_INJECTION_ATTEMPT', clientInfo, {
            details: sqlAnalysis
          });
        }

        // 7. Verificar headers de seguridad
        this.addSecurityHeaders(res);

        // 8. Log request si está habilitado
        if (this.config.logAllRequests) {
          this.logRequest(req, clientInfo, 'ALLOWED');
        }

        // Continuar con el siguiente middleware
        next();

      } catch (error) {
        console.error('Error en WAF:', error);
        
        // En caso de error, permitir request pero loguear
        logSecurityEvent('WAF_ERROR', {
          ip: req.ip,
          error: error.message,
          route: req.originalUrl
        });
        
        next();
      } finally {
        // Log tiempo de procesamiento
        const processingTime = Date.now() - startTime;
        if (processingTime > 100) { // Si toma más de 100ms
          console.warn(`WAF procesamiento lento: ${processingTime}ms para ${req.originalUrl}`);
        }
      }
    };
  }

  /**
   * Extraer información del cliente
   */
  extractClientInfo(req) {
    return {
      ip: req.ip || req.connection.remoteAddress || req.socket.remoteAddress,
      userAgent: req.get('User-Agent') || 'UNKNOWN',
      method: req.method,
      path: req.path,
      query: req.query,
      body: req.body,
      headers: req.headers,
      timestamp: new Date().toISOString()
    };
  }

  /**
   * Verificar rate limiting
   */
  checkRateLimit(ip) {
    const now = Date.now();
    const minute = Math.floor(now / 60000); // Ventana de 1 minuto
    const hour = Math.floor(now / 3600000); // Ventana de 1 hora
    
    if (!this.ipStats.has(ip)) {
      this.ipStats.set(ip, {
        requestsThisMinute: { minute, count: 0 },
        failedAttemptsThisHour: { hour, count: 0 },
        totalRequests: 0,
        firstSeen: now,
        lastSeen: now
      });
    }

    const stats = this.ipStats.get(ip);
    stats.lastSeen = now;
    stats.totalRequests++;

    // Reset counter si es un nuevo minuto
    if (stats.requestsThisMinute.minute !== minute) {
      stats.requestsThisMinute = { minute, count: 1 };
    } else {
      stats.requestsThisMinute.count++;
    }

    // Verificar límite por minuto
    if (stats.requestsThisMinute.count > this.config.maxRequestsPerMinute) {
      return {
        allowed: false,
        reason: 'RATE_LIMIT_EXCEEDED',
        current: stats.requestsThisMinute.count,
        limit: this.config.maxRequestsPerMinute,
        resetTime: (minute + 1) * 60000
      };
    }

    return { allowed: true };
  }

  /**
   * Verificar User-Agent sospechoso
   */
  checkUserAgent(userAgent) {
    if (!userAgent || userAgent.trim().length === 0) {
      return {
        allowed: false,
        reason: 'EMPTY_USER_AGENT',
        userAgent: userAgent
      };
    }

    // Verificar patrones sospechosos
    for (const pattern of this.suspiciousUserAgents) {
      if (pattern.test(userAgent)) {
        return {
          allowed: false,
          reason: 'SUSPICIOUS_USER_AGENT_PATTERN',
          userAgent: userAgent,
          pattern: pattern.source
        };
      }
    }

    return { allowed: true };
  }

  /**
   * Verificar path sospechoso
   */
  checkSuspiciousPath(path) {
    for (const pattern of this.suspiciousPaths) {
      if (pattern.test(path)) {
        return {
          allowed: false,
          reason: 'SUSPICIOUS_PATH_PATTERN',
          path: path,
          pattern: pattern.source
        };
      }
    }

    return { allowed: true };
  }

  /**
   * Análisis completo de SQL injection
   */
  async analyzeSQLInjection(req, clientInfo) {
    const payloads = [];

    // Recopilar todos los payloads para analizar
    if (req.query) {
      Object.entries(req.query).forEach(([key, value]) => {
        payloads.push({ source: 'query', key, value });
      });
    }

    if (req.body && typeof req.body === 'object') {
      Object.entries(req.body).forEach(([key, value]) => {
        if (typeof value === 'string') {
          payloads.push({ source: 'body', key, value });
        }
      });
    }

    if (req.params) {
      Object.entries(req.params).forEach(([key, value]) => {
        payloads.push({ source: 'params', key, value });
      });
    }

    // Analizar cada payload
    const detections = [];
    let maxRiskScore = 0;
    
    for (const payload of payloads) {
      const analysis = advancedDetector.analyzeInput(payload.value, {
        source: payload.source,
        key: payload.key,
        ip: clientInfo.ip
      });

      if (analysis.detectedAttacks.length > 0) {
        detections.push({
          payload: payload,
          analysis: analysis
        });

        maxRiskScore = Math.max(maxRiskScore, analysis.riskScore);
      }
    }

    // Determinar si bloquear
    let blocked = false;
    let reason = '';

    if (maxRiskScore >= 15) {
      blocked = this.config.blockOnCriticalRisk;
      reason = 'CRITICAL_RISK_SQL_INJECTION';
    } else if (maxRiskScore >= 10) {
      blocked = this.config.blockOnHighRisk;
      reason = 'HIGH_RISK_SQL_INJECTION';
    }

    return {
      allowed: !blocked,
      reason: reason,
      detections: detections,
      maxRiskScore: maxRiskScore,
      totalPayloads: payloads.length
    };
  }

  /**
   * Registrar intento de ataque
   */
  registerAttackAttempt(ip, analysis) {
    const stats = this.ipStats.get(ip);
    if (stats) {
      const hour = Math.floor(Date.now() / 3600000);
      
      // Reset counter si es una nueva hora
      if (stats.failedAttemptsThisHour.hour !== hour) {
        stats.failedAttemptsThisHour = { hour, count: 1 };
      } else {
        stats.failedAttemptsThisHour.count++;
      }

      // Bloquear IP si excede límite de intentos fallidos
      if (stats.failedAttemptsThisHour.count >= this.config.maxFailedAttemptsPerHour) {
        this.blacklistedIPs.add(ip);
        
        logSecurityEvent('IP_AUTO_BLACKLISTED', {
          ip: ip,
          attempts: stats.failedAttemptsThisHour.count,
          reason: 'Excedió límite de intentos de ataque por hora'
        });

        // Auto-remover de blacklist después de 1 hora
        setTimeout(() => {
          this.blacklistedIPs.delete(ip);
          console.log(`IP ${ip} removida automáticamente de blacklist`);
        }, 3600000);
      }
    }
  }

  /**
   * Bloquear request
   */
  blockRequest(req, res, reason, clientInfo, additionalInfo = {}) {
    const blockDetails = {
      timestamp: new Date().toISOString(),
      ip: clientInfo.ip,
      userAgent: clientInfo.userAgent,
      method: req.method,
      path: req.originalUrl,
      reason: reason,
      ...additionalInfo
    };

    // Log del bloqueo
    if (this.config.logBlockedRequests) {
      logSecurityEvent('REQUEST_BLOCKED', blockDetails);
    }

    console.warn('🚫 WAF BLOQUEÓ REQUEST:', {
      ip: clientInfo.ip,
      reason: reason,
      path: req.originalUrl
    });

    // Response de bloqueo
    return res.status(403).json({
      success: false,
      message: this.config.customBlockMessage,
      error: 'ACCESS_DENIED',
      requestId: this.generateRequestId(),
      timestamp: blockDetails.timestamp
    });
  }

  /**
   * Agregar headers de seguridad
   */
  addSecurityHeaders(res) {
    Object.entries(this.securityHeaders).forEach(([header, value]) => {
      res.setHeader(header, value);
    });
  }

  /**
   * Log de request
   */
  logRequest(req, clientInfo, status) {
    const logData = {
      timestamp: new Date().toISOString(),
      ip: clientInfo.ip,
      method: req.method,
      path: req.originalUrl,
      userAgent: clientInfo.userAgent,
      status: status,
      contentLength: req.get('content-length') || 0
    };

    console.log('📝 WAF LOG:', logData);
  }

  /**
   * Generar ID único de request
   */
  generateRequestId() {
    return Math.random().toString(36).substring(2, 15) + 
           Math.random().toString(36).substring(2, 15);
  }

  /**
   * Obtener estadísticas del WAF
   */
  getStatistics() {
    const now = Date.now();
    const stats = {
      totalIPs: this.ipStats.size,
      blacklistedIPs: this.blacklistedIPs.size,
      whitelistedIPs: this.whitelistedIPs.size,
      activeIPs: 0,
      totalRequests: 0,
      recentActivity: []
    };

    // Calcular estadísticas
    for (const [ip, ipData] of this.ipStats) {
      stats.totalRequests += ipData.totalRequests;
      
      // IPs activas en la última hora
      if (now - ipData.lastSeen < 3600000) {
        stats.activeIPs++;
        stats.recentActivity.push({
          ip: ip,
          requests: ipData.totalRequests,
          lastSeen: ipData.lastSeen,
          isBlacklisted: this.blacklistedIPs.has(ip)
        });
      }
    }

    // Ordenar por actividad reciente
    stats.recentActivity.sort((a, b) => b.lastSeen - a.lastSeen);
    stats.recentActivity = stats.recentActivity.slice(0, 10); // Top 10

    return stats;
  }

  /**
   * Limpiar estadísticas antiguas
   */
  cleanup() {
    const hourAgo = Date.now() - 3600000;
    
    for (const [ip, stats] of this.ipStats) {
      if (stats.lastSeen < hourAgo) {
        this.ipStats.delete(ip);
      }
    }

    console.log(`WAF cleanup: ${this.ipStats.size} IPs activas`);
  }

  /**
   * Agregar IP a whitelist
   */
  addToWhitelist(ip) {
    this.whitelistedIPs.add(ip);
    console.log(`IP ${ip} agregada a whitelist`);
  }

  /**
   * Agregar IP a blacklist
   */
  addToBlacklist(ip, duration = 3600000) {
    this.blacklistedIPs.add(ip);
    console.log(`IP ${ip} agregada a blacklist por ${duration}ms`);

    // Auto-remover después del tiempo especificado
    if (duration > 0) {
      setTimeout(() => {
        this.blacklistedIPs.delete(ip);
        console.log(`IP ${ip} removida automáticamente de blacklist`);
      }, duration);
    }
  }

  /**
   * Remover IP de blacklist
   */
  removeFromBlacklist(ip) {
    const removed = this.blacklistedIPs.delete(ip);
    if (removed) {
      console.log(`IP ${ip} removida manualmente de blacklist`);
    }
    return removed;
  }
}

module.exports = {
  WebApplicationFirewall
};