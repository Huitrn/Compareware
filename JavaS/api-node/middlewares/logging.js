const { securityLogger } = require('../security/SecurityLogger');
const { writeToLog } = require('../utils/logger');
const logConfig = require('../config/logConfig');

/**
 * Log de acceso fallido (mejorado con SecurityLogger)
 */
const logFailedAccess = (details) => {
  const { ip, userAgent, route, method, authType, username, reason, statusCode } = details;
  
  // Log original (backward compatibility)
  const logMessage = `FAILED_ACCESS | IP: ${ip} | METHOD: ${method} | ROUTE: ${route} | AUTH_TYPE: ${authType} | USERNAME: ${username || 'N/A'} | REASON: ${reason} | STATUS: ${statusCode} | USER_AGENT: ${userAgent}`;
  writeToLog(logConfig.failedAccessLog, logMessage);
  
  // Nuevo sistema de logging avanzado
  securityLogger.logSecurityEvent('AUTHENTICATION_FAILURE', {
    ip: ip,
    userAgent: userAgent,
    route: route,
    method: method,
    message: `Authentication failed: ${reason}`,
    details: {
      authType: authType,
      username: username || 'UNKNOWN',
      reason: reason,
      statusCode: statusCode
    }
  });
  
  console.log(`🚨 INTENTO FALLIDO: ${ip} -> ${route} (${reason})`);
};

/**
 * Log de evento de seguridad (mejorado)
 */
const logSecurityEvent = (type, details) => {
  const { ip, route, message, data, userId, userAgent, method } = details;
  
  // Log original (backward compatibility)
  const securityMessage = `SECURITY_EVENT | TYPE: ${type} | IP: ${ip || 'N/A'} | ROUTE: ${route || 'N/A'} | MESSAGE: ${message} | DATA: ${JSON.stringify(data || {})}`;
  writeToLog(logConfig.securityLog, securityMessage);
  
  // Nuevo sistema de logging avanzado
  securityLogger.logSecurityEvent(type, {
    ip: ip,
    route: route,
    method: method,
    userAgent: userAgent,
    userId: userId,
    message: message,
    details: data || {}
  });
  
  console.log(`🔒 EVENTO DE SEGURIDAD: ${type} - ${message}`);
};

/**
 * Middleware para logging automático de requests HTTP
 */
const httpLoggingMiddleware = (req, res, next) => {
  const startTime = Date.now();
  
  // Capturar el end original para medir tiempo de respuesta
  const originalEnd = res.end;
  res.end = function(...args) {
    const responseTime = Date.now() - startTime;
    
    // Log del acceso HTTP
    securityLogger.logHTTPAccess(req, res, responseTime);
    
    // Log de performance si es lento
    if (responseTime > 1000) {
      securityLogger.logPerformance('response_time', responseTime, {
        route: req.originalUrl,
        method: req.method,
        userId: req.user?.id
      });
    }
    
    originalEnd.apply(res, args);
  };
  
  next();
};

/**
 * Log de errores de aplicación
 */
const logApplicationError = (error, context = {}) => {
  securityLogger.logError(error, {
    ip: context.ip,
    userId: context.userId,
    route: context.route,
    method: context.method,
    userAgent: context.userAgent
  });
};

/**
 * Log de auditoría para cambios críticos
 */
const logAuditEvent = (action, resource, oldValue, newValue, userId, context = {}) => {
  securityLogger.logAuditEvent(action, resource, oldValue, newValue, userId, {
    ip: context.ip,
    userAgent: context.userAgent,
    sessionId: context.sessionId,
    resourceId: context.resourceId,
    additionalData: context.additionalData
  });
};

/**
 * Obtener estadísticas de logging
 */
const getLoggingStats = () => {
  return securityLogger.getStatistics();
};

/**
 * Middleware de manejo de errores con logging
 */
const errorLoggingMiddleware = (err, req, res, next) => {
  logApplicationError(err, {
    ip: req.ip,
    userId: req.user?.id,
    route: req.originalUrl,
    method: req.method,
    userAgent: req.get('User-Agent')
  });
  
  next(err);
};

module.exports = { 
  logFailedAccess, 
  logSecurityEvent,
  httpLoggingMiddleware,
  logApplicationError,
  logAuditEvent,
  getLoggingStats,
  errorLoggingMiddleware
};
