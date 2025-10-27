const { sqlValidator } = require('../security/SQLSecurityValidator');
const { logSecurityEvent } = require('./logging');

/**
 * Middleware de Seguridad SQL
 * Valida y sanitiza todos los inputs antes de llegar a los controladores
 */

/**
 * Middleware principal de protección SQL
 */
const sqlInjectionProtection = (options = {}) => {
  return (req, res, next) => {
    try {
      const startTime = Date.now();
      
      // Configuración por defecto
      const config = {
        strictMode: options.strictMode || false,
        logAttempts: options.logAttempts !== false, // true por defecto
        blockOnDetection: options.blockOnDetection !== false, // true por defecto
        ...options
      };

      // Información del usuario para logs
      const userInfo = {
        ip: req.ip || req.connection.remoteAddress,
        userAgent: req.get('User-Agent'),
        userId: req.user?.id,
        route: req.originalUrl,
        method: req.method
      };

      // Validar diferentes partes del request
      const validationResults = [];

      // 1. Validar parámetros de query (?param=value)
      if (req.query && Object.keys(req.query).length > 0) {
        const queryValidation = validateRequestPart(
          req.query, 
          'QUERY_PARAMS', 
          config, 
          userInfo
        );
        validationResults.push(queryValidation);
      }

      // 2. Validar parámetros de ruta (/users/:id)
      if (req.params && Object.keys(req.params).length > 0) {
        const paramsValidation = validateRequestPart(
          req.params, 
          'ROUTE_PARAMS', 
          config, 
          userInfo
        );
        validationResults.push(paramsValidation);
      }

      // 3. Validar body (POST/PUT)
      if (req.body && Object.keys(req.body).length > 0) {
        const bodyValidation = validateRequestPart(
          req.body, 
          'REQUEST_BODY', 
          config, 
          userInfo
        );
        validationResults.push(bodyValidation);
      }

      // 4. Validar headers específicos (si es necesario)
      if (config.validateHeaders) {
        const headersValidation = validateHeaders(req.headers, config, userInfo);
        validationResults.push(headersValidation);
      }

      // Verificar si hay alguna validación fallida
      const hasFailures = validationResults.some(result => !result.isValid);

      if (hasFailures && config.blockOnDetection) {
        // Bloquear request malicioso
        const processingTime = Date.now() - startTime;
        
        logSecurityEvent('SQL_INJECTION_BLOCKED', {
          ip: userInfo.ip,
          route: userInfo.route,
          message: 'Request bloqueado por posible inyección SQL',
          data: {
            validationResults: validationResults,
            processingTime: processingTime,
            userInfo: userInfo
          }
        });

        return res.status(400).json({
          success: false,
          message: 'Request contiene datos no válidos',
          error: 'INVALID_INPUT_DATA',
          details: config.strictMode ? 
            'Datos de entrada no cumplen con políticas de seguridad' :
            validationResults.filter(r => !r.isValid).map(r => r.errors).flat()
        });
      }

      // Si hay warnings pero no errores, continuar pero loguear
      const hasWarnings = validationResults.some(result => 
        result.warnings && result.warnings.length > 0
      );

      if (hasWarnings && config.logAttempts) {
        logSecurityEvent('SQL_INJECTION_WARNING', {
          ip: userInfo.ip,
          route: userInfo.route,
          message: 'Request con warnings de seguridad procesado',
          data: {
            warnings: validationResults.map(r => r.warnings).flat(),
            userInfo: userInfo
          }
        });
      }

      // Sanitizar los datos en el request
      sanitizeRequest(req, validationResults);

      // Agregar información de seguridad al request
      req.securityValidation = {
        passed: true,
        results: validationResults,
        processingTime: Date.now() - startTime
      };

      next();

    } catch (error) {
      console.error('Error en middleware de seguridad SQL:', error);
      
      logSecurityEvent('SQL_PROTECTION_ERROR', {
        ip: req.ip,
        route: req.originalUrl,
        message: 'Error en middleware de protección SQL',
        data: { error: error.message, stack: error.stack }
      });

      // En caso de error, continuar (fail-open) pero loguear
      next();
    }
  };
};

/**
 * Validar una parte específica del request
 */
function validateRequestPart(data, source, config, userInfo) {
  const result = {
    source: source,
    isValid: true,
    sanitized: {},
    errors: [],
    warnings: []
  };

  // Esquemas de validación por ruta
  const schema = getValidationSchema(userInfo.route, source);

  for (const [key, value] of Object.entries(data)) {
    const fieldSchema = schema[key] || { type: 'default' };
    
    const validation = sqlValidator.validateAndSanitize(
      value,
      fieldSchema.type,
      { ...fieldSchema.options, strictMode: config.strictMode }
    );

    result.sanitized[key] = validation.sanitized;

    if (!validation.isValid) {
      result.isValid = false;
      result.errors.push(`${key}: ${validation.errors.join(', ')}`);
      
      // Loguear intento de inyección
      if (config.logAttempts) {
        sqlValidator.logSQLInjectionAttempt(value, `${source}.${key}`, userInfo);
      }
    }

    if (validation.warnings.length > 0) {
      result.warnings.push(`${key}: ${validation.warnings.join(', ')}`);
    }
  }

  return result;
}

/**
 * Obtener esquema de validación según la ruta
 */
function getValidationSchema(route, source) {
  // Esquemas específicos por endpoint
  const schemas = {
    '/api/auth/register': {
      'REQUEST_BODY': {
        name: { type: 'name', options: { required: true } },
        email: { type: 'email', options: { required: true } },
        password: { type: 'default', options: { required: true } }
      }
    },
    '/api/auth/login': {
      'REQUEST_BODY': {
        email: { type: 'email', options: { required: true } },
        password: { type: 'default', options: { required: true } }
      }
    },
    '/api/orders': {
      'REQUEST_BODY': {
        'orderData.user_id': { type: 'id', options: { required: true } },
        'orderData.total_amount': { type: 'default', options: { required: true } },
        'orderData.shipping_address': { type: 'address', options: { required: true } },
        'orderData.payment_method': { type: 'name', options: { required: true } }
      }
    }
  };

  // Buscar esquema específico o usar genérico
  for (const [routePattern, routeSchema] of Object.entries(schemas)) {
    if (route.includes(routePattern.replace('/api', ''))) {
      return routeSchema[source] || {};
    }
  }

  // Esquemas genéricos por tipo de parámetro
  const genericSchemas = {
    'ROUTE_PARAMS': {
      id: { type: 'id', options: { required: true } },
      userId: { type: 'id', options: { required: true } },
      orderId: { type: 'id', options: { required: true } },
      transactionId: { type: 'name', options: { required: true } }
    },
    'QUERY_PARAMS': {
      page: { type: 'id', options: { required: false } },
      limit: { type: 'id', options: { required: false } },
      search: { type: 'name', options: { required: false } },
      filter: { type: 'name', options: { required: false } }
    }
  };

  return genericSchemas[source] || {};
}

/**
 * Validar headers específicos
 */
function validateHeaders(headers, config, userInfo) {
  const result = {
    source: 'HEADERS',
    isValid: true,
    errors: [],
    warnings: []
  };

  // Validar headers que pueden ser peligrosos
  const dangerousHeaders = [
    'x-forwarded-for', 'x-real-ip', 'x-custom-header'
  ];

  for (const header of dangerousHeaders) {
    if (headers[header]) {
      const validation = sqlValidator.validateAndSanitize(
        headers[header],
        'default',
        { strictMode: config.strictMode }
      );

      if (!validation.isValid) {
        result.isValid = false;
        result.errors.push(`Header ${header}: ${validation.errors.join(', ')}`);
      }
    }
  }

  return result;
}

/**
 * Sanitizar datos en el request original
 */
function sanitizeRequest(req, validationResults) {
  for (const result of validationResults) {
    if (result.sanitized) {
      switch (result.source) {
        case 'QUERY_PARAMS':
          Object.assign(req.query, result.sanitized);
          break;
        case 'ROUTE_PARAMS':
          Object.assign(req.params, result.sanitized);
          break;
        case 'REQUEST_BODY':
          Object.assign(req.body, result.sanitized);
          break;
      }
    }
  }
}

/**
 * Middleware específico para rutas sensibles
 */
const strictSQLProtection = sqlInjectionProtection({
  strictMode: true,
  logAttempts: true,
  blockOnDetection: true,
  validateHeaders: true
});

/**
 * Middleware para rutas de solo lectura
 */
const readOnlySQLProtection = sqlInjectionProtection({
  strictMode: false,
  logAttempts: true,
  blockOnDetection: false
});

module.exports = {
  sqlInjectionProtection,
  strictSQLProtection,
  readOnlySQLProtection
};