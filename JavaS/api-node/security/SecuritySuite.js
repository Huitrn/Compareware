/**
 * Integración Completa del Sistema de Seguridad Compareware
 * Proyecto: Compareware - Desarrollo Backend
 * Propósito: Integrar todos los componentes de seguridad en una solución completa
 */

const { WebApplicationFirewall } = require('./security/WebApplicationFirewall');
const { sqlValidator } = require('./security/SQLSecurityValidator');
const { createSafeQuery, QuickQueries } = require('./security/SafeQueryBuilder');
const { advancedDetector } = require('./security/AdvancedSQLDetector');
const { securityLogger } = require('./security/SecurityLogger');
const { SQLInjectionPenTester } = require('./security/SQLInjectionPenTester');

/**
 * Configuración central de seguridad
 */
class ComparewareSecuritySuite {
  constructor(options = {}) {
    this.config = {
      wafEnabled: options.wafEnabled !== false,
      strictMode: options.strictMode || false,
      logLevel: options.logLevel || 'INFO',
      enablePenTesting: options.enablePenTesting || false,
      ...options
    };

    // Inicializar componentes
    this.initializeComponents();
  }

  /**
   * Inicializar todos los componentes de seguridad
   */
  initializeComponents() {
    // 1. Web Application Firewall
    if (this.config.wafEnabled) {
      this.waf = new WebApplicationFirewall({
        blockOnHighRisk: true,
        blockOnCriticalRisk: true,
        maxRequestsPerMinute: 60,
        maxFailedAttemptsPerHour: 10,
        logBlockedRequests: true,
        customBlockMessage: 'Request blocked by Compareware Security System'
      });
    }

    // 2. Logger de seguridad ya inicializado como singleton

    // 3. Pen Tester (solo en desarrollo)
    if (this.config.enablePenTesting && process.env.NODE_ENV !== 'production') {
      this.penTester = new SQLInjectionPenTester('http://localhost:3000');
    }

    console.log('🛡️  Compareware Security Suite inicializado exitosamente');
  }

  /**
   * Middleware de seguridad completo para Express
   */
  getSecurityMiddleware() {
    const middlewares = [];

    // 1. Logging de HTTP requests
    middlewares.push((req, res, next) => {
      const startTime = Date.now();
      
      res.on('finish', () => {
        const responseTime = Date.now() - startTime;
        securityLogger.logHTTPAccess(req, res, responseTime);
      });
      
      next();
    });

    // 2. WAF si está habilitado
    if (this.waf) {
      middlewares.push(this.waf.middleware());
    }

    // 3. Validación adicional de SQL en todos los requests
    middlewares.push(async (req, res, next) => {
      try {
        // Validar query params
        if (req.query) {
          for (const [key, value] of Object.entries(req.query)) {
            const analysis = advancedDetector.analyzeInput(value, {
              source: 'query',
              key: key
            });

            if (analysis.severity === 'CRITICAL') {
              securityLogger.logSecurityEvent('SQL_INJECTION_BLOCKED', {
                ip: req.ip,
                route: req.originalUrl,
                message: 'Critical SQL injection attempt in query params',
                details: analysis
              });

              return res.status(403).json({
                success: false,
                message: 'Request contains invalid data',
                error: 'SECURITY_VIOLATION'
              });
            }
          }
        }

        // Validar body
        if (req.body && typeof req.body === 'object') {
          for (const [key, value] of Object.entries(req.body)) {
            if (typeof value === 'string') {
              const analysis = advancedDetector.analyzeInput(value, {
                source: 'body',
                key: key
              });

              if (analysis.severity === 'CRITICAL') {
                securityLogger.logSecurityEvent('SQL_INJECTION_BLOCKED', {
                  ip: req.ip,
                  route: req.originalUrl,
                  message: 'Critical SQL injection attempt in request body',
                  details: analysis
                });

                return res.status(403).json({
                  success: false,
                  message: 'Request contains invalid data',
                  error: 'SECURITY_VIOLATION'
                });
              }
            }
          }
        }

        next();
      } catch (error) {
        securityLogger.logError(error, {
          ip: req.ip,
          route: req.originalUrl
        });
        next();
      }
    });

    return middlewares;
  }

  /**
   * Crear conexión segura a base de datos
   */
  createSecureDbConnection(dbConnection) {
    return {
      // Query builder seguro
      safeQuery: () => createSafeQuery(dbConnection),
      
      // Queries rápidas
      quickQueries: new QuickQueries(dbConnection),
      
      // Conexión original (para casos especiales)
      raw: dbConnection,
      
      // Método de validación antes de query
      validateAndExecute: async (query, params = []) => {
        // Validar query
        const queryValidation = sqlValidator.validateQuery(query, params);
        if (!queryValidation.isValid) {
          throw new Error(`Invalid query: ${queryValidation.errors.join(', ')}`);
        }

        // Validar parámetros
        for (const param of params) {
          const paramValidation = sqlValidator.validateAdvanced(param);
          if (!paramValidation.isValid) {
            throw new Error(`Invalid parameter: ${paramValidation.errors.join(', ')}`);
          }
        }

        // Ejecutar query
        return await dbConnection.query(query, params);
      }
    };
  }

  /**
   * Endpoint de monitoreo de seguridad
   */
  getSecurityStatusEndpoint() {
    return (req, res) => {
      try {
        const stats = {
          timestamp: new Date().toISOString(),
          system: 'Compareware Security Suite',
          version: '1.0.0',
          status: 'ACTIVE',
          
          // Estadísticas del logger
          logging: securityLogger.getStatistics(),
          
          // Estadísticas del WAF
          waf: this.waf ? this.waf.getStatistics() : { enabled: false },
          
          // Estadísticas del detector
          detector: advancedDetector.getAttackStatistics(),
          
          // Configuración activa
          configuration: {
            wafEnabled: this.config.wafEnabled,
            strictMode: this.config.strictMode,
            logLevel: this.config.logLevel
          }
        };

        res.json({
          success: true,
          data: stats
        });
      } catch (error) {
        securityLogger.logError(error);
        res.status(500).json({
          success: false,
          message: 'Error retrieving security status'
        });
      }
    };
  }

  /**
   * Endpoint de testing de seguridad (solo desarrollo)
   */
  getPenTestingEndpoint() {
    if (process.env.NODE_ENV === 'production' || !this.config.enablePenTesting) {
      return (req, res) => {
        res.status(403).json({
          success: false,
          message: 'Penetration testing not available in production'
        });
      };
    }

    return async (req, res) => {
      try {
        const { testType = 'quick', target = 'http://localhost:3000' } = req.body;

        if (testType === 'quick') {
          const result = await this.penTester.quickTest('/api/test', "' OR '1'='1'--");
          res.json({
            success: true,
            testType: 'quick',
            result: result
          });
        } else if (testType === 'full') {
          // Ejecutar en background y retornar ID de job
          const jobId = Date.now().toString();
          
          setImmediate(async () => {
            try {
              await this.penTester.runFullPenTest();
              console.log(`✅ Penetration test job ${jobId} completed`);
            } catch (error) {
              console.error(`❌ Penetration test job ${jobId} failed:`, error);
            }
          });

          res.json({
            success: true,
            testType: 'full',
            jobId: jobId,
            message: 'Full penetration test started in background'
          });
        } else {
          res.status(400).json({
            success: false,
            message: 'Invalid test type. Use "quick" or "full"'
          });
        }
      } catch (error) {
        securityLogger.logError(error);
        res.status(500).json({
          success: false,
          message: 'Error running penetration test'
        });
      }
    };
  }

  /**
   * Obtener validador SQL para uso manual
   */
  getValidator() {
    return {
      basic: sqlValidator,
      advanced: advancedDetector
    };
  }

  /**
   * Cleanup de recursos
   */
  cleanup() {
    if (this.waf) {
      this.waf.cleanup();
    }
    
    advancedDetector.cleanup();
    
    console.log('🧹 Compareware Security Suite cleanup completed');
  }

  /**
   * Configurar cleanup automático
   */
  setupAutomaticCleanup() {
    // Cleanup cada hora
    setInterval(() => {
      this.cleanup();
    }, 3600000); // 1 hora

    // Cleanup al cerrar aplicación
    process.on('SIGINT', () => {
      this.cleanup();
      process.exit(0);
    });

    process.on('SIGTERM', () => {
      this.cleanup();
      process.exit(0);
    });
  }
}

// Instancia singleton
const securitySuite = new ComparewareSecuritySuite();

module.exports = {
  ComparewareSecuritySuite,
  securitySuite
};