require('dotenv').config();
const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const { Pool } = require('pg');
const jwt = require('jsonwebtoken');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(cors());
app.use(bodyParser.json());

// =====================================
// PRÁCTICA 2: CABECERAS PERSONALIZADAS
// =====================================

const APP_CONFIG = {
  name: 'Compareware API', 
  version: '2.1.0',
  author: 'Equipo Compareware',
  environment: process.env.NODE_ENV || 'development',
  buildDate: '2025-10-01',
  apiVersion: 'v1'
};

// Paso 65: Configurar cabecera X-App-Version
const addCustomHeaders = (req, res, next) => {
  res.setHeader('X-App-Version', APP_CONFIG.version);
  res.setHeader('X-App-Name', APP_CONFIG.name);
  res.setHeader('X-API-Version', APP_CONFIG.apiVersion);
  res.setHeader('X-Build-Date', APP_CONFIG.buildDate);
  res.setHeader('X-Environment', APP_CONFIG.environment);
  res.setHeader('X-Author', APP_CONFIG.author);
  res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('X-Frame-Options', 'DENY');
  res.setHeader('X-XSS-Protection', '1; mode=block');
  res.setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
  res.setHeader('X-Response-Time', Date.now());
  res.setHeader('X-Request-ID', `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`);
  res.setHeader('X-Powered-By', 'Node.js + Express + PostgreSQL');
  res.setHeader('X-Server-Instance', `compareware-api-${process.pid}`);
  next();
};

app.use(addCustomHeaders);
console.log(`🏷️ Cabeceras personalizadas configuradas para ${APP_CONFIG.name} v${APP_CONFIG.version}`);

// =====================================
// PRÁCTICA 3: SISTEMA DE LOGGING
// =====================================

// Paso 43: Guardar en un log los intentos fallidos
const LOG_CONFIG = {
  logDir: path.join(__dirname, 'logs'),
  failedAccessLog: path.join(__dirname, 'logs', 'failed-access.log'),
  securityLog: path.join(__dirname, 'logs', 'security.log'),
  maxLogSize: 10 * 1024 * 1024,
};

// Crear directorio de logs si no existe
if (!fs.existsSync(LOG_CONFIG.logDir)) {
  fs.mkdirSync(LOG_CONFIG.logDir, { recursive: true });
  console.log('📁 Directorio de logs creado:', LOG_CONFIG.logDir);
}

const writeToLog = (logFile, message) => {
  try {
    const timestamp = new Date().toISOString();
    const logEntry = `[${timestamp}] ${message}\n`;
    fs.appendFile(logFile, logEntry, (err) => {
      if (err) console.error('❌ Error escribiendo log:', err);
    });
  } catch (error) {
    console.error('❌ Error en writeToLog:', error);
  }
};

// Paso 44: Incluir fecha, ruta y usuario
const logFailedAccess = (details) => {
  const { ip, userAgent, route, method, authType, username, reason, statusCode } = details;
  const logMessage = `FAILED_ACCESS | IP: ${ip} | METHOD: ${method} | ROUTE: ${route} | AUTH_TYPE: ${authType} | USERNAME: ${username || 'N/A'} | REASON: ${reason} | STATUS: ${statusCode} | USER_AGENT: ${userAgent}`;
  writeToLog(LOG_CONFIG.failedAccessLog, logMessage);
  const securityMessage = `SECURITY_EVENT | TYPE: FAILED_AUTH | IP: ${ip} | ROUTE: ${route} | USER: ${username || 'UNKNOWN'} | REASON: ${reason}`;
  writeToLog(LOG_CONFIG.securityLog, securityMessage);
  console.log(`🚨 INTENTO FALLIDO: ${ip} -> ${route} (${reason})`);
};

const logSecurityEvent = (type, details) => {
  const { ip, route, message, data } = details;
  const securityMessage = `SECURITY_EVENT | TYPE: ${type} | IP: ${ip || 'N/A'} | ROUTE: ${route || 'N/A'} | MESSAGE: ${message} | DATA: ${JSON.stringify(data || {})}`;
  writeToLog(LOG_CONFIG.securityLog, securityMessage);
  console.log(`🔒 EVENTO DE SEGURIDAD: ${type} - ${message}`);
};

// =====================================
// CONFIGURACIÓN POSTGRESQL
// =====================================

console.log('DB_PASSWORD:', process.env.DB_PASSWORD);
const pool = new Pool({
  host: process.env.DB_HOST,
  port: parseInt(process.env.DB_PORT) || 5432,
  database: process.env.DB_DATABASE,
  user: process.env.DB_USERNAME,
  password: String(process.env.DB_PASSWORD),
});

// =====================================
// MANEJO GLOBAL DE ERRORES
// =====================================

process.on('uncaughtException', (error) => {
  console.error('🚨 ERROR NO CAPTURADO:', error);
  console.error('Stack trace:', error.stack);
  logSecurityEvent('UNCAUGHT_EXCEPTION', {
    ip: 'SERVER',
    route: 'GLOBAL',
    message: 'Error no capturado en el servidor',
    data: { 
      error: error.message, 
      stack: error.stack,
      timestamp: new Date().toISOString()
    }
  });
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('🚨 PROMESA RECHAZADA NO MANEJADA:', reason);
  console.error('Promise:', promise);
  logSecurityEvent('UNHANDLED_REJECTION', {
    ip: 'SERVER',
    route: 'GLOBAL',
    message: 'Promesa rechazada no manejada',
    data: { 
      reason: reason,
      timestamp: new Date().toISOString()
    }
  });
});

const validateDatabaseConnection = async () => {
  try {
    console.log('🔍 Validando conexión a PostgreSQL...');
    const client = await pool.connect();
    await client.query('SELECT NOW()');
    client.release();
    console.log('✅ Conexión a PostgreSQL exitosa');
    return true;
  } catch (error) {
    console.error('❌ Error conectando a PostgreSQL:', error.message);
    return false;
  }
};

const safeDbQuery = async (queryFn) => {
  try {
    return await queryFn();
  } catch (error) {
    console.error('❌ Error en consulta DB:', error);
    throw {
      message: 'Error de base de datos',
      details: error.message,
      code: error.code || 'DB_ERROR'
    };
  }
};

const globalErrorHandler = (err, req, res, next) => {
  console.error('🚨 ERROR EN EXPRESS:', err);
  logSecurityEvent('EXPRESS_ERROR', {
    ip: req.ip || 'unknown',
    route: req.path || 'unknown',
    message: 'Error en middleware de Express',
    data: { 
      error: err.message,
      method: req.method,
      url: req.url,
      stack: err.stack
    }
  });
  
  if (!res.headersSent) {
    res.status(500).json({
      status: 'error',
      message: 'Error interno del servidor',
      error: 'Ha ocurrido un error inesperado',
      timestamp: new Date().toISOString()
    });
  }
};

validateDatabaseConnection();

// =====================================
// PRÁCTICA 4: VALIDACIONES Y CÓDIGOS HTTP
// =====================================

const HTTP_ERRORS = {
  400: { title: 'Bad Request', description: 'La petición contiene datos inválidos o mal formados' },
  401: { title: 'Unauthorized', description: 'Se requiere autenticación para acceder a este recurso' },
  403: { title: 'Forbidden', description: 'No tienes permisos suficientes para acceder a este recurso' },
  404: { title: 'Not Found', description: 'El recurso solicitado no fue encontrado' },
  409: { title: 'Conflict', description: 'La petición entra en conflicto con el estado actual del recurso' },
  500: { title: 'Internal Server Error', description: 'Error interno del servidor' }
};

const isValidEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

const isValidId = (id) => {
  return id && !isNaN(id) && parseInt(id) > 0;
};

// Paso 71: Devolver 400 en datos inválidos
const sendBadRequestError = (res, message = 'Datos inválidos', details = {}, validationErrors = []) => {
  res.setHeader('X-Error-Type', 'VALIDATION_ERROR');
  res.setHeader('X-Error-Code', '400');
  res.setHeader('X-Validation-Failed', 'true');
  
  const errorResponse = {
    status: 'error',
    error: {
      code: 400,
      type: HTTP_ERRORS[400].title,
      message: message,
      description: HTTP_ERRORS[400].description
    },
    details: details,
    validation: {
      failed: true,
      errors: validationErrors,
      count: validationErrors.length
    },
    timestamp: new Date().toISOString(),
    requestId: res.getHeader('X-Request-ID')
  };
  
  console.log('❌ 400 Bad Request:', message, details);
  return res.status(400).json(errorResponse);
};

// Paso 72: Devolver 401 en falta de autenticación
const sendUnauthorizedError = (res, message = 'Autenticación requerida', authType = 'Bearer Token o Basic Auth') => {
  res.setHeader('X-Error-Type', 'AUTHENTICATION_ERROR');
  res.setHeader('X-Error-Code', '401');
  res.setHeader('WWW-Authenticate', authType);
  res.setHeader('X-Auth-Required', 'true');
  
  const errorResponse = {
    status: 'error',
    error: {
      code: 401,
      type: HTTP_ERRORS[401].title,
      message: message,
      description: HTTP_ERRORS[401].description
    },
    authentication: {
      required: true,
      type: authType,
      hint: 'Proporciona credenciales válidas en el header Authorization'
    },
    timestamp: new Date().toISOString(),
    requestId: res.getHeader('X-Request-ID')
  };
  
  console.log('🔐 401 Unauthorized:', message);
  return res.status(401).json(errorResponse);
};

// Paso 73: Devolver 403 en acceso no autorizado
const sendForbiddenError = (res, message = 'Acceso denegado', requiredRole = null, userRole = null) => {
  res.setHeader('X-Error-Type', 'AUTHORIZATION_ERROR');
  res.setHeader('X-Error-Code', '403');
  res.setHeader('X-Access-Denied', 'true');
  if (requiredRole) res.setHeader('X-Required-Role', requiredRole);
  if (userRole) res.setHeader('X-User-Role', userRole);
  
  const errorResponse = {
    status: 'error',
    error: {
      code: 403,
      type: HTTP_ERRORS[403].title,
      message: message,
      description: HTTP_ERRORS[403].description
    },
    authorization: {
      denied: true,
      requiredRole: requiredRole,
      currentRole: userRole,
      hint: requiredRole ? `Se requiere rol: ${requiredRole}` : 'Permisos insuficientes'
    },
    timestamp: new Date().toISOString(),
    requestId: res.getHeader('X-Request-ID')
  };
  
  console.log('🚫 403 Forbidden:', message, { requiredRole, userRole });
  return res.status(403).json(errorResponse);
};

const validateRequiredFields = (requiredFields) => {
  return (req, res, next) => {
    const validationErrors = [];
    const receivedFields = Object.keys(req.body);
    
    requiredFields.forEach(field => {
      if (!req.body[field]) {
        validationErrors.push({
          field: field,
          message: `El campo '${field}' es requerido`,
          received: req.body[field] || null
        });
      }
    });
    
    if (req.body.email && !isValidEmail(req.body.email)) {
      validationErrors.push({
        field: 'email',
        message: 'El formato del email es inválido',
        received: req.body.email
      });
    }
    
    if (req.body.password && req.body.password.length < 6) {
      validationErrors.push({
        field: 'password',
        message: 'La contraseña debe tener al menos 6 caracteres',
        received: `${req.body.password.length} caracteres`
      });
    }
    
    if (validationErrors.length > 0) {
      return sendBadRequestError(res, 'Datos de entrada inválidos', {
        requiredFields: requiredFields,
        receivedFields: receivedFields,
        missingFields: requiredFields.filter(field => !req.body[field])
      }, validationErrors);
    }
    
    next();
  };
};

const requireAuthentication = (req, res, next) => {
  const authHeader = req.headers.authorization;
  
  if (!authHeader) {
    return sendUnauthorizedError(res, 'Header Authorization no proporcionado', 'Bearer <token> o Basic <credentials>');
  }
  
  if (authHeader.startsWith('Basic ')) {
    const base64Credentials = authHeader.split(' ')[1];
    if (!base64Credentials) {
      return sendUnauthorizedError(res, 'Credenciales Basic Auth mal formadas', 'Basic <base64(user:password)>');
    }
    
    try {
      const credentials = Buffer.from(base64Credentials, 'base64').toString('ascii');
      const [email, password] = credentials.split(':');
      
      if (!email || !password) {
        return sendUnauthorizedError(res, 'Credenciales Basic Auth incompletas', 'Basic <base64(email:password)>');
      }
      
      req.auth = { email, password, type: 'basic' };
      next();
      
    } catch (error) {
      return sendUnauthorizedError(res, 'Error decodificando credenciales Basic Auth', 'Basic <base64(email:password)>');
    }
    
  } else if (authHeader.startsWith('Bearer ')) {
    const token = authHeader.split(' ')[1];
    if (!token) {
      return sendUnauthorizedError(res, 'Token JWT no proporcionado', 'Bearer <jwt-token>');
    }
    
    try {
      const decoded = jwt.verify(token, process.env.JWT_SECRET || 'default_secret');
      req.user = decoded;
      req.auth = { type: 'jwt', user: decoded };
      next();
      
    } catch (error) {
      if (error.name === 'TokenExpiredError') {
        return sendUnauthorizedError(res, 'Token JWT expirado', 'Bearer <valid-jwt-token>');
      } else if (error.name === 'JsonWebTokenError') {
        return sendUnauthorizedError(res, 'Token JWT inválido', 'Bearer <valid-jwt-token>');
      } else {
        return sendUnauthorizedError(res, 'Error validando token JWT', 'Bearer <valid-jwt-token>');
      }
    }
    
  } else {
    return sendUnauthorizedError(res, 'Tipo de autenticación no soportado', 'Bearer <token> o Basic <credentials>');
  }
};

const requireRole = (requiredRole) => {
  return (req, res, next) => {
    if (!req.user && !req.auth) {
      return sendUnauthorizedError(res, 'Usuario no autenticado para verificación de roles');
    }
    
    const userRole = req.user?.role || req.auth?.user?.role || 'user';
    
    if (requiredRole && userRole !== requiredRole) {
      return sendForbiddenError(res, 
        `Acceso restringido a usuarios con rol: ${requiredRole}`, 
        requiredRole, 
        userRole
      );
    }
    
    if (requiredRole === 'admin' && userRole !== 'admin') {
      return sendForbiddenError(res, 
        'Esta operación requiere permisos de administrador', 
        'admin', 
        userRole
      );
    }
    
    next();
  };
};

console.log('🔍 Sistema de validaciones y códigos HTTP específicos configurado');
console.log('✅ Códigos implementados: 400 (Bad Request), 401 (Unauthorized), 403 (Forbidden)');

// =====================================
// RATE LIMITING
// =====================================

// Paso 40: Agregar un contador de accesos a la ruta
const requestCounts = new Map();

// Paso 41: Definir un límite (ej. 5 peticiones por minuto)
const RATE_LIMIT_CONFIG = {
  windowMs: 60 * 1000,
  maxRequests: 5,
  message: 'Demasiadas peticiones desde esta IP, intenta nuevamente en 1 minuto'
};

const cleanupExpiredCounts = () => {
  const now = Date.now();
  for (const [key, data] of requestCounts.entries()) {
    if (now - data.resetTime > RATE_LIMIT_CONFIG.windowMs) {
      requestCounts.delete(key);
    }
  }
};

// Paso 42: Bloquear si se excede el límite
const rateLimiter = (routeIdentifier = 'default') => {
  return (req, res, next) => {
    const clientIP = req.ip || req.connection.remoteAddress || req.socket.remoteAddress || 'unknown';
    const key = `${clientIP}:${routeIdentifier}`;
    
    if (Math.random() < 0.1) {
      cleanupExpiredCounts();
    }
    
    const now = Date.now();
    const windowStart = now - RATE_LIMIT_CONFIG.windowMs;
    
    if (!requestCounts.has(key)) {
      requestCounts.set(key, {
        count: 0,
        resetTime: now,
        requests: []
      });
    }
    
    const userData = requestCounts.get(key);
    userData.requests = userData.requests.filter(timestamp => timestamp > windowStart);
    userData.count = userData.requests.length;
    
    if (userData.count >= RATE_LIMIT_CONFIG.maxRequests) {
      const oldestRequest = Math.min(...userData.requests);
      const resetTime = oldestRequest + RATE_LIMIT_CONFIG.windowMs;
      const retryAfter = Math.ceil((resetTime - now) / 1000);
      
      res.set({
        'X-RateLimit-Limit': RATE_LIMIT_CONFIG.maxRequests,
        'X-RateLimit-Remaining': 0,
        'X-RateLimit-Reset': new Date(resetTime).toISOString(),
        'Retry-After': retryAfter
      });
      
      return res.status(429).json({
        error: 'Rate limit exceeded',
        message: RATE_LIMIT_CONFIG.message,
        details: {
          limit: RATE_LIMIT_CONFIG.maxRequests,
          windowMs: RATE_LIMIT_CONFIG.windowMs,
          retryAfter: `${retryAfter} segundos`,
          resetTime: new Date(resetTime).toISOString()
        }
      });
    }
    
    userData.requests.push(now);
    userData.count = userData.requests.length;
    
    res.set({
      'X-RateLimit-Limit': RATE_LIMIT_CONFIG.maxRequests,
      'X-RateLimit-Remaining': RATE_LIMIT_CONFIG.maxRequests - userData.count,
      'X-RateLimit-Reset': new Date(now + RATE_LIMIT_CONFIG.windowMs).toISOString()
    });
    
    next();
  };
};

const globalRateLimit = rateLimiter('global');

// =====================================
// AUTENTICACIÓN BÁSICA
// =====================================

const basicAuth = (req, res, next) => {
  const clientIP = req.ip || req.connection.remoteAddress || req.socket.remoteAddress || 'unknown';
  const userAgent = req.headers['user-agent'] || 'unknown';
  const route = req.path;
  const method = req.method;
  
  const authHeader = req.headers.authorization;
  
  if (!authHeader || !authHeader.startsWith('Basic ')) {
    logFailedAccess({
      ip: clientIP, userAgent: userAgent, route: route, method: method,
      authType: 'BASIC_AUTH', username: null, reason: 'NO_AUTH_HEADER', statusCode: 401
    });
    
    res.setHeader('WWW-Authenticate', 'Basic realm="Compareware API"');
    return res.status(401).json({ 
      error: 'Acceso denegado', 
      message: 'Se requieren credenciales de autenticación básica' 
    });
  }

  let username, password;
  try {
    const base64Credentials = authHeader.split(' ')[1];
    const credentials = Buffer.from(base64Credentials, 'base64').toString('ascii');
    [username, password] = credentials.split(':');
  } catch (error) {
    logFailedAccess({
      ip: clientIP, userAgent: userAgent, route: route, method: method,
      authType: 'BASIC_AUTH', username: 'DECODE_ERROR', reason: 'INVALID_BASE64_ENCODING', statusCode: 401
    });
    
    res.setHeader('WWW-Authenticate', 'Basic realm="Compareware API"');
    return res.status(401).json({ 
      error: 'Acceso denegado', 
      message: 'Formato de credenciales inválido' 
    });
  }

  const validUser = process.env.BASIC_AUTH_USER || 'admin';
  const validPassword = process.env.BASIC_AUTH_PASSWORD || '123456';

  if (username === validUser && password === validPassword) {
    logSecurityEvent('SUCCESSFUL_AUTH', {
      ip: clientIP, route: route,
      message: `Autenticación básica exitosa para usuario: ${username}`,
      data: { authType: 'BASIC_AUTH', username }
    });
    next();
  } else {
    logFailedAccess({
      ip: clientIP, userAgent: userAgent, route: route, method: method,
      authType: 'BASIC_AUTH', username: username, reason: 'INVALID_CREDENTIALS', statusCode: 401
    });
    
    res.setHeader('WWW-Authenticate', 'Basic realm="Compareware API"');
    return res.status(401).json({ 
      error: 'Acceso denegado', 
      message: 'Credenciales incorrectas' 
    });
  }
};

// =====================================
// VALIDACIÓN JWT
// =====================================

// Paso 37: Configurar middleware que revise un token en headers
const verifyToken = (req, res, next) => {
  const clientIP = req.ip || req.connection.remoteAddress || req.socket.remoteAddress || 'unknown';
  const userAgent = req.headers['user-agent'] || 'unknown';
  const route = req.path;
  const method = req.method;
  
  const authHeader = req.headers.authorization;
  
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    logFailedAccess({
      ip: clientIP, userAgent: userAgent, route: route, method: method,
      authType: 'JWT_TOKEN', username: null, reason: 'NO_BEARER_TOKEN', statusCode: 401
    });
    
    // Paso 39: Si no, responder con 401
    return res.status(401).json({
      error: 'Token requerido',
      message: 'Se requiere un token de autorización en el header (Bearer token)'
    });
  }

  const token = authHeader.split(' ')[1];
  
  if (!token) {
    logFailedAccess({
      ip: clientIP, userAgent: userAgent, route: route, method: method,
      authType: 'JWT_TOKEN', username: null, reason: 'EMPTY_TOKEN', statusCode: 401
    });
    
    return res.status(401).json({
      error: 'Token inválido',
      message: 'Token no proporcionado correctamente'
    });
  }

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    req.user = decoded;
    
    logSecurityEvent('SUCCESSFUL_AUTH', {
      ip: clientIP, route: route,
      message: `Autenticación JWT exitosa para usuario: ${decoded.email || decoded.id}`,
      data: { authType: 'JWT_TOKEN', userId: decoded.id, email: decoded.email }
    });
    
    // Paso 38: Si el token es válido, permitir acceso
    next();
    
  } catch (error) {
    let errorMessage = 'Token inválido';
    let reason = 'INVALID_TOKEN';
    
    if (error.name === 'TokenExpiredError') {
      errorMessage = 'Token expirado';
      reason = 'EXPIRED_TOKEN';
    } else if (error.name === 'JsonWebTokenError') {
      errorMessage = 'Token malformado';
      reason = 'MALFORMED_TOKEN';
    }
    
    logFailedAccess({
      ip: clientIP, userAgent: userAgent, route: route, method: method,
      authType: 'JWT_TOKEN', username: null, reason: reason, statusCode: 401
    });
    
    return res.status(401).json({
      error: 'Acceso denegado',
      message: errorMessage,
      details: error.message
    });
  }
};

// =====================================
// RUTAS PROTEGIDAS CON JWT
// =====================================

app.get('/api/jwt/profile', rateLimiter('jwt-profile'), verifyToken, async (req, res) => {
  try {
    const userId = req.user.id;
    
    const userResult = await pool.query(
      'SELECT id, name, email, created_at FROM users WHERE id = $1',
      [userId]
    );
    
    if (userResult.rows.length === 0) {
      return res.status(404).json({
        error: 'Usuario no encontrado',
        message: 'El usuario del token no existe en la base de datos'
      });
    }
    
    res.json({
      message: 'Acceso concedido - Token válido',
      user: userResult.rows[0],
      tokenInfo: {
        decoded: req.user,
        timestamp: new Date().toISOString()
      }
    });
    
  } catch (err) {
    console.error('Error en profile JWT:', err);
    res.status(500).json({
      error: 'Error interno del servidor',
      message: 'No se pudo obtener el perfil del usuario'
    });
  }
});

app.get('/api/jwt/protected', rateLimiter('jwt-protected'), verifyToken, (req, res) => {
  res.json({
    message: 'Acceso concedido - Token JWT válido',
    user: req.user,
    timestamp: new Date().toISOString(),
    status: 'success'
  });
});

app.post('/api/generate-token', rateLimiter('generate-token'), async (req, res) => {
  const { email, password } = req.body;
  
  try {
    const userResult = await pool.query(
      'SELECT id, name, email FROM users WHERE email = $1',
      [email]
    );
    
    if (userResult.rows.length === 0) {
      return res.status(401).json({
        error: 'Credenciales incorrectas',
        message: 'Usuario no encontrado'
      });
    }
    
    const user = userResult.rows[0];
    
    const token = jwt.sign(
      { id: user.id, email: user.email, name: user.name },
      process.env.JWT_SECRET,
      { expiresIn: '1h' }
    );
    
    res.json({
      message: 'Token generado exitosamente',
      token: token,
      user: { id: user.id, name: user.name, email: user.email },
      expiresIn: '1 hora'
    });
    
  } catch (err) {
    console.error('Error generando token:', err);
    res.status(500).json({
      error: 'Error interno',
      message: 'No se pudo generar el token'
    });
  }
});

// =====================================
// RUTAS PRIVADAS (AUTENTICACIÓN BÁSICA)
// =====================================

app.get('/api/admin/dashboard', rateLimiter('admin-dashboard'), basicAuth, async (req, res) => {
  try {
    const statsResult = await pool.query(`
      SELECT 
        'usuarios' as tabla, COUNT(*) as total FROM users
      UNION ALL
      SELECT 
        'marcas' as tabla, COUNT(*) as total FROM marcas
      UNION ALL
      SELECT 
        'perifericos' as tabla, COUNT(*) as total FROM perifericos
    `);

    res.json({
      message: 'Acceso concedido al panel de administración',
      user: 'admin',
      timestamp: new Date().toISOString(),
      statistics: statsResult.rows,
      status: 'authenticated'
    });
  } catch (err) {
    console.error('Error en dashboard admin:', err);
    res.status(500).json({ 
      error: 'Error interno del servidor',
      message: 'No se pudieron obtener las estadísticas'
    });
  }
});

app.get('/api/private/test', basicAuth, (req, res) => {
  res.json({
    message: 'Acceso concedido a ruta privada',
    timestamp: new Date().toISOString(),
    status: 'success'
  });
});

// =====================================
// RUTAS PÚBLICAS - MARCAS
// =====================================

app.get('/api/marcas', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM marcas');
    res.json(result.rows);
  } catch (err) {
    console.error('Error al obtener marcas:', err);
    res.status(500).json({ error: 'Error al obtener marcas.', detalle: err.message });
  }
});

app.post('/api/marcas', async (req, res) => {
  const { nombre } = req.body;
  try {
    const result = await pool.query(
      'INSERT INTO marcas (nombre, created_at, updated_at) VALUES ($1, NOW(), NOW()) RETURNING *',
      [nombre]
    );
    res.status(201).json(result.rows[0]);
  } catch (err) {
    console.error('Error al crear marca:', err);
    res.status(500).json({ error: 'Error al crear marca.', detalle: err.message });
  }
});

app.put('/api/marcas/:id', async (req, res) => {
  const { id } = req.params;
  const { nombre } = req.body;
  try {
    const result = await pool.query(
      'UPDATE marcas SET nombre=$1, updated_at=NOW() WHERE id=$2 RETURNING *',
      [nombre, id]
    );
    res.json(result.rows[0]);
  } catch (err) {
    console.error('Error al actualizar marca:', err);
    res.status(500).json({ error: 'Error al actualizar marca.', detalle: err.message });
  }
});

app.delete('/api/marcas/:id', async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query('DELETE FROM marcas WHERE id=$1', [id]);
    res.json({ message: 'Marca eliminada' });
  } catch (err) {
    console.error('Error al eliminar marca:', err);
    res.status(500).json({ error: 'Error al eliminar marca.', detalle: err.message });
  }
});

// =====================================
// RUTAS PÚBLICAS - PERIFÉRICOS
// =====================================

app.get('/api/perifericos', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM perifericos');
    res.json(result.rows);
  } catch (err) {
    console.error('Error al obtener periféricos:', err);
    res.status(500).json({ error: 'Error al obtener periféricos.', detalle: err.message });
  }
});

app.post('/api/perifericos', async (req, res) => {
  const { nombre, modelo, precio, tipo_conectividad, marca_id, categoria_id } = req.body;
  try {
    const result = await pool.query(
      'INSERT INTO perifericos (nombre, modelo, precio, tipo_conectividad, marca_id, categoria_id) VALUES ($1, $2, $3, $4, $5, $6) RETURNING *',
      [nombre, modelo, precio, tipo_conectividad, marca_id, categoria_id]
    );
    res.status(201).json(result.rows[0]);
  } catch (err) {
    console.error('Error al crear periférico:', err);
    res.status(500).json({ error: 'Error al crear periférico.', detalle: err.message });
  }
});

app.put('/api/perifericos/:id', async (req, res) => {
  const { id } = req.params;
  const { nombre, modelo, precio, tipo_conectividad, marca_id, categoria_id } = req.body;
  try {
    const result = await pool.query(
      'UPDATE perifericos SET nombre=$1, modelo=$2, precio=$3, tipo_conectividad=$4, marca_id=$5, categoria_id=$6 WHERE id=$7 RETURNING *',
      [nombre, modelo, precio, tipo_conectividad, marca_id, categoria_id, id]
    );
    res.json(result.rows[0]);
  } catch (err) {
    res.status(500).json({ error: 'Error al actualizar periférico.' });
  }
});

app.delete('/api/perifericos/:id', async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query('DELETE FROM perifericos WHERE id=$1', [id]);
    res.json({ message: 'Periférico eliminado' });
  } catch (err) {
    res.status(500).json({ error: 'Error al eliminar periférico.' });
  }
});

// =====================================
// RUTAS DE AUTENTICACIÓN
// =====================================

app.post('/api/register', async (req, res) => {
  const { name, email, password, role } = req.body;
  try {
    const userExists = await pool.query('SELECT * FROM users WHERE email = $1', [email]);
    if (userExists.rows.length > 0) {
      return res.status(400).json({ error: 'El correo ya está registrado.' });
    }
    await pool.query(
      'INSERT INTO users (name, email, password, role, created_at) VALUES ($1, $2, $3, $4, NOW())',
      [name, email, password, role || 'user']
    );
    res.status(201).json({ message: 'Usuario registrado correctamente.' });
  } catch (err) {
    res.status(500).json({ error: 'Error en el servidor.' });
  }
});

app.post('/api/login', async (req, res) => {
  try {
    const { email, password } = req.body;
    
    if (!email || !password) {
      return res.status(400).json({ 
        error: 'Datos incompletos',
        message: 'Email y password son requeridos'
      });
    }
    
    const result = await safeDbQuery(async () => {
      return await pool.query(
        'SELECT id, name, email, role FROM users WHERE email = $1 AND password = $2', 
        [email, password]
      );
    });
    
    if (result.rows.length === 0) {
      return res.status(401).json({ 
        error: 'Credenciales incorrectas',
        message: 'Email o password incorrecto'
      });
    }
    
    const user = result.rows[0];
    
    res.json({ 
      message: 'Login exitoso', 
      user: {
        id: user.id,
        name: user.name,
        email: user.email,
        role: user.role
      },
      timestamp: new Date().toISOString()
    });
    
  } catch (err) {
    console.error('❌ Error en login:', err);
    res.status(500).json({ 
      error: 'Error interno del servidor',
      message: 'No se pudo procesar el login'
    });
  }
});

// =====================================
// PRÁCTICA 1: OPERACIONES CRUD - USUARIOS
// =====================================

// Pasos 46-48: GET para listar recursos
app.get('/api/usuarios', async (req, res) => {
  console.log('📋 GET /api/usuarios - Obteniendo lista de usuarios...');
  
  try {
    // Paso 47: Retornar lista en formato JSON
    const result = await safeDbQuery(async () => {
      return await pool.query(
        'SELECT id, name, email, role, created_at, updated_at FROM users ORDER BY created_at DESC'
      );
    });
    
    console.log('✅ Lista de usuarios obtenida:', result.rows.length, 'usuarios');
    
    res.setHeader('X-Operation-Type', 'READ');
    res.setHeader('X-Resource-Type', 'users');
    res.setHeader('X-Query-Count', result.rows.length.toString());
    
    res.json({
      status: 'success',
      message: 'Lista de usuarios obtenida correctamente',
      data: result.rows,
      count: result.rows.length,
      source: 'PostgreSQL tabla users',
      timestamp: new Date().toISOString()
    });
    
  } catch (err) {
    console.error('❌ Error obteniendo usuarios:', err);
    
    res.status(500).json({
      status: 'error',
      message: 'Error interno del servidor al obtener usuarios',
      error: err.message,
      operation: 'GET_USERS',
      timestamp: new Date().toISOString()
    });
  }
});

// Pasos 50-52: POST para crear recurso
app.post('/api/usuarios', 
  validateRequiredFields(['name', 'email', 'password']),
  async (req, res) => {
    console.log('📝 POST /api/usuarios - Creando nuevo usuario...');
    console.log('📦 Datos recibidos:', req.body);
    
    res.setHeader('X-Operation-Type', 'CREATE');
    res.setHeader('X-Resource-Type', 'users');
    
    try {
      const { name, email, password, role } = req.body;
      
      const userExists = await safeDbQuery(async () => {
        return await pool.query(
          'SELECT id, email FROM users WHERE email = $1', 
          [email]
        );
      });
      
      if (userExists.rows.length > 0) {
        return res.status(409).json({
          status: 'error',
          error: {
            code: 409,
            type: 'Conflict',
            message: 'El usuario ya existe',
            description: 'El email proporcionado ya está registrado en el sistema'
          },
          conflict: {
            field: 'email',
            value: email,
            existingUserId: userExists.rows[0].id
          },
          timestamp: new Date().toISOString()
        });
      }
      
      // Paso 51: Recibir datos en el cuerpo de la petición
      const result = await safeDbQuery(async () => {
        return await pool.query(
          `INSERT INTO users (name, email, password, role, created_at, updated_at) 
           VALUES ($1, $2, $3, $4, NOW(), NOW()) 
           RETURNING id, name, email, role, created_at`,
          [name, email, password, role || 'user']
        );
      });
      
      const newUser = result.rows[0];
      res.setHeader('X-Resource-ID', newUser.id.toString());
      
      console.log('✅ Usuario creado exitosamente:', newUser.id);
      
      // Paso 52: Retornar confirmación
      res.status(201).json({
        status: 'success',
        message: 'Usuario creado correctamente',
        data: {
          id: newUser.id,
          name: newUser.name,
          email: newUser.email,
          role: newUser.role,
          created_at: newUser.created_at
        },
        operation: 'CREATE_USER',
        timestamp: new Date().toISOString()
      });
      
    } catch (err) {
      console.error('❌ Error creando usuario:', err);
      
      res.status(500).json({
        status: 'error',
        error: {
          code: 500,
          type: 'Internal Server Error',
          message: 'Error interno del servidor al crear usuario',
          description: 'Se produjo un error inesperado en el servidor'
        },
        operation: 'CREATE_USER',
        timestamp: new Date().toISOString()
      });
    }
  }
);

// Pasos 53-55: PUT para actualizar recurso
app.put('/api/usuarios/:id', async (req, res) => {
  console.log('✏️ PUT /api/usuarios/:id - Actualizando usuario...');
  console.log('🆔 ID recibido:', req.params.id);
  console.log('📦 Datos recibidos:', req.body);
  
  try {
    // Paso 54: Recibir id y datos en el cuerpo
    const { id } = req.params;
    const { name, email, password, role } = req.body;
    
    if (!id || isNaN(id)) {
      return res.status(400).json({
        status: 'error',
        message: 'ID inválido',
        details: 'El ID debe ser un número válido',
        received: { id },
        timestamp: new Date().toISOString()
      });
    }
    
    if (!name && !email && !password && !role) {
      return res.status(400).json({
        status: 'error',
        message: 'No hay campos para actualizar',
        details: 'Debe proporcionar al menos uno: name, email, password, role',
        received: req.body,
        timestamp: new Date().toISOString()
      });
    }
    
    const userExists = await safeDbQuery(async () => {
      return await pool.query(
        'SELECT id, name, email, role FROM users WHERE id = $1',
        [id]
      );
    });
    
    if (userExists.rows.length === 0) {
      return res.status(404).json({
        status: 'error',
        message: 'Usuario no encontrado',
        details: `No existe un usuario con ID: ${id}`,
        id: parseInt(id),
        timestamp: new Date().toISOString()
      });
    }
    
    const currentUser = userExists.rows[0];
    
    if (email && email !== currentUser.email) {
      const emailExists = await safeDbQuery(async () => {
        return await pool.query(
          'SELECT id, email FROM users WHERE email = $1 AND id != $2',
          [email, id]
        );
      });
      
      if (emailExists.rows.length > 0) {
        return res.status(409).json({
          status: 'error',
          message: 'El email ya está en uso',
          conflict: 'Email ya registrado por otro usuario',
          email: email,
          conflictingUserId: emailExists.rows[0].id,
          timestamp: new Date().toISOString()
        });
      }
    }
    
    const updateFields = [];
    const updateValues = [];
    let paramCounter = 1;
    
    if (name) {
      updateFields.push(`name = $${paramCounter}`);
      updateValues.push(name);
      paramCounter++;
    }
    
    if (email) {
      updateFields.push(`email = $${paramCounter}`);
      updateValues.push(email);
      paramCounter++;
    }
    
    if (password) {
      updateFields.push(`password = $${paramCounter}`);
      updateValues.push(password);
      paramCounter++;
    }
    
    if (role) {
      updateFields.push(`role = $${paramCounter}`);
      updateValues.push(role);
      paramCounter++;
    }
    
    updateFields.push(`updated_at = NOW()`);
    updateValues.push(id);
    
    const updateQuery = `
      UPDATE users 
      SET ${updateFields.join(', ')}
      WHERE id = $${paramCounter}
      RETURNING id, name, email, role, created_at, updated_at
    `;
    
    const result = await safeDbQuery(async () => {
      return await pool.query(updateQuery, updateValues);
    });
    
    const updatedUser = result.rows[0];
    
    console.log('✅ Usuario actualizado exitosamente:', updatedUser.id);
    
    // Paso 55: Responder con recurso actualizado
    res.setHeader('X-Operation-Type', 'UPDATE');
    res.setHeader('X-Resource-Type', 'users');
    res.setHeader('X-Resource-ID', updatedUser.id.toString());
    res.setHeader('X-Fields-Modified', Object.keys(req.body).join(','));
    
    res.json({
      status: 'success',
      message: 'Usuario actualizado correctamente',
      data: {
        before: currentUser,
        after: {
          id: updatedUser.id,
          name: updatedUser.name,
          email: updatedUser.email,
          role: updatedUser.role,
          created_at: updatedUser.created_at,
          updated_at: updatedUser.updated_at
        }
      },
      changes: {
        name: name ? { from: currentUser.name, to: name } : 'unchanged',
        email: email ? { from: currentUser.email, to: email } : 'unchanged',
        password: password ? 'updated' : 'unchanged',
        role: role ? { from: currentUser.role, to: role } : 'unchanged'
      },
      operation: 'UPDATE_USER',
      timestamp: new Date().toISOString()
    });
    
  } catch (err) {
    console.error('❌ Error actualizando usuario:', err);
    
    res.status(500).json({
      status: 'error',
      message: 'Error interno del servidor al actualizar usuario',
      error: err.message,
      operation: 'UPDATE_USER',
      timestamp: new Date().toISOString()
    });
  }
});

// Pasos 56-58: DELETE para eliminar recurso
app.delete('/api/usuarios/:id', async (req, res) => {
  console.log('🗑️ DELETE /api/usuarios/:id - Eliminando usuario...');
  console.log('🆔 ID recibido:', req.params.id);
  
  try {
    // Paso 56: Definir ruta DELETE /usuarios/:id
    const { id } = req.params;
    
    if (!id || isNaN(id)) {
      return res.status(400).json({
        status: 'error',
        message: 'ID inválido',
        details: 'El ID debe ser un número válido',
        received: { id },
        timestamp: new Date().toISOString()
      });
    }
    
    const userExists = await safeDbQuery(async () => {
      return await pool.query(
        'SELECT id, name, email, role, created_at FROM users WHERE id = $1',
        [id]
      );
    });
    
    if (userExists.rows.length === 0) {
      return res.status(404).json({
        status: 'error',
        message: 'Usuario no encontrado',
        details: `No existe un usuario con ID: ${id}`,
        id: parseInt(id),
        operation: 'DELETE_USER',
        timestamp: new Date().toISOString()
      });
    }
    
    const userToDelete = userExists.rows[0];
    
    // Paso 57: Eliminar recurso de la lista/datos
    const deleteResult = await safeDbQuery(async () => {
      return await pool.query(
        'DELETE FROM users WHERE id = $1 RETURNING id',
        [id]
      );
    });
    
    if (deleteResult.rowCount === 0) {
      return res.status(500).json({
        status: 'error',
        message: 'No se pudo eliminar el usuario',
        details: 'La operación de eliminación falló',
        id: parseInt(id),
        operation: 'DELETE_USER',
        timestamp: new Date().toISOString()
      });
    }
    
    console.log('✅ Usuario eliminado exitosamente:', userToDelete.id);
    
    logSecurityEvent('USER_DELETED', {
      ip: req.ip || req.connection.remoteAddress || 'unknown',
      route: req.path,
      message: `Usuario eliminado: ${userToDelete.name} (${userToDelete.email})`,
      data: { 
        deletedUserId: userToDelete.id, 
        userName: userToDelete.name,
        userEmail: userToDelete.email
      }
    });
    
    // Paso 58: Responder con mensaje de confirmación
    res.setHeader('X-Operation-Type', 'DELETE');
    res.setHeader('X-Resource-Type', 'users');
    res.setHeader('X-Resource-ID', userToDelete.id.toString());
    res.setHeader('X-Deletion-Confirmed', 'true');
    
    res.json({
      status: 'success',
      message: 'Usuario eliminado correctamente',
      data: {
        deletedUser: {
          id: userToDelete.id,
          name: userToDelete.name,
          email: userToDelete.email,
          role: userToDelete.role,
          created_at: userToDelete.created_at
        }
      },
      operation: 'DELETE_USER',
      timestamp: new Date().toISOString()
    });
    
  } catch (err) {
    console.error('❌ Error eliminando usuario:', err);
    
    logSecurityEvent('DELETE_ERROR', {
      ip: req.ip || req.connection.remoteAddress || 'unknown',
      route: req.path,
      message: `Error al eliminar usuario ID: ${req.params.id}`,
      data: { error: err.message, userId: req.params.id }
    });
    
    res.status(500).json({
      status: 'error',
      message: 'Error interno del servidor al eliminar usuario',
      error: err.message,
      operation: 'DELETE_USER',
      timestamp: new Date().toISOString()
    });
  }
});

// =====================================
// ENDPOINTS DE MONITOREO
// =====================================

// Paso 45: Verificar archivo de logs
app.get('/api/logs/failed-access', (req, res) => {
  try {
    if (fs.existsSync(LOG_CONFIG.failedAccessLog)) {
      const logs = fs.readFileSync(LOG_CONFIG.failedAccessLog, 'utf8');
      const logLines = logs.split('\n').filter(line => line.trim()).slice(-50);
      
      res.json({
        status: 'success',
        message: 'Logs de intentos fallidos',
        data: logLines,
        count: logLines.length,
        file: LOG_CONFIG.failedAccessLog
      });
    } else {
      res.json({
        status: 'success',
        message: 'No hay logs de intentos fallidos',
        data: [],
        count: 0
      });
    }
  } catch (error) {
    console.error('Error leyendo logs:', error);
    res.status(500).json({
      status: 'error',
      message: 'Error al leer logs',
      error: error.message
    });
  }
});

app.get('/api/logs/security', (req, res) => {
  try {
    if (fs.existsSync(LOG_CONFIG.securityLog)) {
      const logs = fs.readFileSync(LOG_CONFIG.securityLog, 'utf8');
      const logLines = logs.split('\n').filter(line => line.trim()).slice(-50);
      
      res.json({
        status: 'success',
        message: 'Logs de seguridad',
        data: logLines,
        count: logLines.length,
        file: LOG_CONFIG.securityLog
      });
    } else {
      res.json({
        status: 'success',
        message: 'No hay logs de seguridad',
        data: [],
        count: 0
      });
    }
  } catch (error) {
    console.error('Error leyendo logs de seguridad:', error);
    res.status(500).json({
      status: 'error',
      message: 'Error al leer logs de seguridad',
      error: error.message
    });
  }
});

app.get('/api/health', async (req, res) => {
  try {
    const dbStatus = await validateDatabaseConnection();
    
    res.json({
      status: 'success',
      message: 'Servidor funcionando correctamente',
      services: {
        api: 'running',
        database: dbStatus ? 'connected' : 'disconnected',
        logs: fs.existsSync(LOG_CONFIG.logDir) ? 'available' : 'unavailable'
      },
      timestamp: new Date().toISOString(),
      uptime: process.uptime()
    });
  } catch (error) {
    res.status(500).json({
      status: 'error',
      message: 'Error en check de salud',
      error: error.message,
      timestamp: new Date().toISOString()
    });
  }
});

// =====================================
// PRÁCTICA 2: ENDPOINTS DE PRUEBA DE CABECERAS
// =====================================

// Paso 66: Probar con cliente HTTP y ver cabecera
app.get('/api/headers-info', (req, res) => {
  console.log('🏷️ GET /api/headers-info - Mostrando información de cabeceras...');
  
  const headersInfo = {
    status: 'success',
    message: 'Información de cabeceras personalizadas',
    appHeaders: {
      'X-App-Version': APP_CONFIG.version,
      'X-App-Name': APP_CONFIG.name,
      'X-API-Version': APP_CONFIG.apiVersion,
      'X-Build-Date': APP_CONFIG.buildDate,
      'X-Environment': APP_CONFIG.environment,
      'X-Author': APP_CONFIG.author
    },
    securityHeaders: {
      'X-Content-Type-Options': 'nosniff',
      'X-Frame-Options': 'DENY',
      'X-XSS-Protection': '1; mode=block',
      'Referrer-Policy': 'strict-origin-when-cross-origin'
    },
    responseHeaders: {
      'X-Response-Time': res.getHeader('X-Response-Time'),
      'X-Request-ID': res.getHeader('X-Request-ID'),
      'X-Powered-By': 'Node.js + Express + PostgreSQL',
      'X-Server-Instance': res.getHeader('X-Server-Instance')
    },
    requestInfo: {
      method: req.method,
      url: req.url,
      userAgent: req.headers['user-agent'],
      ip: req.ip || req.connection.remoteAddress || 'unknown',
      timestamp: new Date().toISOString()
    },
    documentation: {
      description: 'Todas las respuestas de esta API incluyen cabeceras personalizadas',
      howToView: 'Usar herramientas como Postman, curl, o Developer Tools del navegador',
      mainHeader: 'X-App-Version - Versión actual de la aplicación',
      purpose: 'Proporcionar información útil para debugging y monitoreo'
    }
  };
  
  res.setHeader('X-Headers-Documented', 'true');
  res.setHeader('X-Documentation-Version', '1.0');
  
  res.json(headersInfo);
});

app.get('/api/test-headers', (req, res) => {
  console.log('🧪 GET /api/test-headers - Testing cabeceras personalizadas...');
  
  res.setHeader('X-Test-Route', 'true');
  res.setHeader('X-Test-Timestamp', new Date().toISOString());
  
  res.json({
    message: 'Ruta de prueba para cabeceras personalizadas',
    instruction: 'Revisa las cabeceras HTTP de esta respuesta',
    tip: 'En Postman: pestaña Headers después de hacer Send',
    version: APP_CONFIG.version,
    timestamp: new Date().toISOString()
  });
});

// =====================================
// PRÁCTICA 4: RUTAS DE DEMOSTRACIÓN DE CÓDIGOS HTTP
// =====================================

// Paso 71: Demostración de error 400
app.post('/api/demo/400-bad-request', (req, res) => {
  console.log('🧪 Demo 400: Testing validación de datos inválidos');
  
  const { name, email, age } = req.body;
  const validationErrors = [];
  
  // Validaciones específicas para demostrar 400
  if (!name) {
    validationErrors.push({
      field: 'name',
      message: 'El nombre es requerido',
      received: name || null
    });
  }
  
  if (!email) {
    validationErrors.push({
      field: 'email', 
      message: 'El email es requerido',
      received: email || null
    });
  } else if (!isValidEmail(email)) {
    validationErrors.push({
      field: 'email',
      message: 'El formato del email es inválido',
      received: email
    });
  }
  
  if (age && (isNaN(age) || age < 0 || age > 120)) {
    validationErrors.push({
      field: 'age',
      message: 'La edad debe ser un número entre 0 y 120',
      received: age
    });
  }
  
  // Si hay errores de validación, devolver 400
  if (validationErrors.length > 0) {
    return sendBadRequestError(res, 
      'Los datos proporcionados son inválidos', 
      {
        expectedFormat: {
          name: 'string (requerido)',
          email: 'string con formato válido (requerido)',
          age: 'number entre 0-120 (opcional)'
        }
      }, 
      validationErrors
    );
  }
  
  // Si todo está correcto
  res.json({
    status: 'success',
    message: 'Datos válidos recibidos correctamente',
    data: { name, email, age },
    timestamp: new Date().toISOString()
  });
});

// Paso 72: Demostración de error 401 - Unauthorized  
app.get('/api/demo/401-unauthorized', (req, res) => {
  console.log('🧪 Demo 401: Testing falta de autenticación');
  
  const authHeader = req.headers.authorization;
  
  if (!authHeader) {
    return sendUnauthorizedError(res, 
      'Se requiere autenticación para acceder a este recurso',
      'Bearer <valid-jwt-token> o Basic <base64(email:password)>'
    );
  }
  
  if (authHeader.startsWith('Bearer ')) {
    const token = authHeader.split(' ')[1];
    if (!token || token === 'invalid_token') {
      return sendUnauthorizedError(res, 
        'Token JWT inválido o expirado',
        'Bearer <valid-jwt-token>'
      );
    }
  }
  
  if (authHeader.startsWith('Basic ')) {
    const base64Creds = authHeader.split(' ')[1];
    if (!base64Creds || base64Creds === 'invalid_credentials') {
      return sendUnauthorizedError(res, 
        'Credenciales Basic Auth inválidas',
        'Basic <base64(email:password)>'
      );
    }
  }
  
  // Si la autenticación es válida
  res.json({
    status: 'success',
    message: 'Acceso autorizado - autenticación válida',
    data: {
      authType: authHeader.split(' ')[0],
      message: 'Usuario autenticado correctamente'
    },
    timestamp: new Date().toISOString()
  });
});

// Paso 73: Demostración de error 403 - Forbidden
app.get('/api/demo/403-forbidden', (req, res) => {
  console.log('🧪 Demo 403: Testing acceso no autorizado');
  
  const authHeader = req.headers.authorization;
  const userRole = req.headers['x-user-role'] || 'user'; // Simular rol del usuario
  
  // Primero verificar autenticación
  if (!authHeader) {
    return sendUnauthorizedError(res, 'Autenticación requerida antes de verificar permisos');
  }
  
  // Verificar permisos específicos
  const requiredRole = 'admin';
  
  if (userRole !== requiredRole) {
    return sendForbiddenError(res, 
      'Esta operación está restringida a administradores',
      requiredRole,
      userRole
    );
  }
  
  // Si tiene permisos correctos
  res.json({
    status: 'success',
    message: 'Acceso autorizado - permisos suficientes',
    data: {
      userRole: userRole,
      requiredRole: requiredRole,
      message: 'Usuario con permisos de administrador'
    },
    timestamp: new Date().toISOString()
  });
});

/**
 * Ruta combinada que demuestra todos los códigos
 */
app.post('/api/demo/validation-complete', 
  validateRequiredFields(['name', 'email']),  // Puede generar 400
  requireAuthentication,                       // Puede generar 401
  requireRole('admin'),                       // Puede generar 403
  async (req, res) => {
    console.log('🧪 Demo completo: Validaciones 400, 401, 403 pasadas');
    
    try {
      const { name, email, password } = req.body;
      
      // Simular creación de usuario admin
      res.status(201).json({
        status: 'success',
        message: 'Usuario administrador creado exitosamente',
        data: {
          name,
          email,
          role: 'admin',
          createdBy: req.user?.email || req.auth?.email || 'sistema'
        },
        validations: {
          dataValidation: '✅ Datos válidos (400 evitado)',
          authentication: '✅ Usuario autenticado (401 evitado)', 
          authorization: '✅ Permisos suficientes (403 evitado)'
        },
        timestamp: new Date().toISOString()
      });
      
    } catch (error) {
      console.error('Error en demo completo:', error);
      res.status(500).json({
        status: 'error',
        message: 'Error interno en demostración',
        error: error.message,
        timestamp: new Date().toISOString()
      });
    }
  }
);

// =====================================
// PRÁCTICA 5: LOG DE RESPUESTAS CON CÓDIGO DE ESTADO
// =====================================
// Configuración extendida para logs de peticiones HTTP
const HTTP_LOG_CONFIG = {
  ...LOG_CONFIG,
  httpRequestsLog: path.join(__dirname, 'logs', 'http-requests.log'),
  httpResponsesLog: path.join(__dirname, 'logs', 'http-responses.log'),
  httpAccessLog: path.join(__dirname, 'logs', 'access.log'),
  httpErrorsLog: path.join(__dirname, 'logs', 'http-errors.log'),
  maxLogFileSize: 50 * 1024 * 1024, // 50MB por archivo
  archiveOldLogs: true
};

/**
 * Función para rotar logs cuando excedan el tamaño máximo
 * @param {string} logFile - Ruta del archivo de log
 */
const rotateLogIfNeeded = (logFile) => {
  try {
    if (fs.existsSync(logFile)) {
      const stats = fs.statSync(logFile);
      if (stats.size > HTTP_LOG_CONFIG.maxLogFileSize) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const archivedFile = logFile.replace('.log', `-${timestamp}.log`);
        
        // Mover archivo actual a archivo archivado
        fs.renameSync(logFile, archivedFile);
        console.log(`📁 Log rotado: ${path.basename(archivedFile)}`);
      }
    }
  } catch (error) {
    console.error('❌ Error rotando log:', error);
  }
};

/**
 * Función mejorada para escribir logs HTTP
 * @param {string} logFile - Archivo de destino
 * @param {object} requestData - Datos de la petición
 */
const writeHttpLog = (logFile, requestData) => {
  try {
    // Rotar log si es necesario
    rotateLogIfNeeded(logFile);
    
    const {
      timestamp,
      method,
      url,
      statusCode,
      responseTime,
      ip,
      userAgent,
      requestId,
      contentLength,
      requestSize,
      referrer,
      userId,
      errorMessage
    } = requestData;
    
    // Formato de log estructurado (tipo Apache Combined Log + campos personalizados)
    const logEntry = [
      `[${timestamp}]`,
      `${ip}`,
      `"${method} ${url} HTTP/1.1"`,
      `${statusCode}`,
      `${contentLength || '-'}`,
      `"${referrer || '-'}"`,
      `"${userAgent || '-'}"`,
      `${responseTime}ms`,
      `req_id:${requestId}`,
      userId ? `user:${userId}` : 'user:-',
      errorMessage ? `error:"${errorMessage}"` : 'error:-'
    ].join(' ') + '\n';
    
    // Escribir de forma asíncrona
    fs.appendFile(logFile, logEntry, (err) => {
      if (err) {
        console.error('❌ Error escribiendo log HTTP:', err);
      }
    });
    
  } catch (error) {
    console.error('❌ Error en writeHttpLog:', error);
  }
};

/**
 * Paso 74: Middleware para registrar cada petición HTTP
 * Se ejecuta ANTES de procesar la petición
 */
const httpRequestLogger = (req, res, next) => {
  // Paso 74: Registrar información de la petición entrante
  const startTime = Date.now();
  const requestTimestamp = new Date().toISOString();
  
  // Información básica de la petición
  const requestInfo = {
    timestamp: requestTimestamp,
    method: req.method,
    url: req.originalUrl || req.url,
    ip: req.ip || req.connection.remoteAddress || req.socket.remoteAddress || 'unknown',
    userAgent: req.headers['user-agent'] || 'unknown',
    referrer: req.headers.referer || req.headers.referrer || null,
    requestId: res.getHeader('X-Request-ID'),
    contentType: req.headers['content-type'] || null,
    contentLength: req.headers['content-length'] || null,
    authorization: req.headers.authorization ? 'present' : 'none',
    query: Object.keys(req.query).length > 0 ? JSON.stringify(req.query) : null,
    requestSize: JSON.stringify(req.body).length
  };
  
  // Log de petición entrante
  const incomingLogEntry = `INCOMING | ${requestInfo.timestamp} | ${requestInfo.method} ${requestInfo.url} | IP: ${requestInfo.ip} | UA: ${requestInfo.userAgent} | REQ_ID: ${requestInfo.requestId}`;
  
  writeToLog(HTTP_LOG_CONFIG.httpRequestsLog, incomingLogEntry);
  console.log(`📥 ${requestInfo.method} ${requestInfo.url} - ${requestInfo.ip}`);
  
  // Interceptar la función res.end para capturar la respuesta
  const originalEnd = res.end;
  const originalSend = res.send;
  const originalJson = res.json;
  
  let responseBody = null;
  let responseSent = false;
  
  // Interceptar res.send
  res.send = function(data) {
    if (!responseSent) {
      responseBody = data;
      responseSent = true;
    }
    return originalSend.call(this, data);
  };
  
  // Interceptar res.json
  res.json = function(data) {
    if (!responseSent) {
      responseBody = JSON.stringify(data);
      responseSent = true;
    }
    return originalJson.call(this, data);
  };
  
  // Interceptar res.end
  res.end = function(chunk, encoding) {
    if (!responseSent && chunk) {
      responseBody = chunk;
      responseSent = true;
    }
    
    // Paso 75: Calcular tiempo de respuesta y registrar información completa
    const endTime = Date.now();
    const responseTime = endTime - startTime;
    const responseTimestamp = new Date().toISOString();
    
    // Información completa de la respuesta
    const responseInfo = {
      timestamp: responseTimestamp,
      method: req.method,
      url: req.originalUrl || req.url,
      statusCode: res.statusCode,
      responseTime: responseTime,
      ip: requestInfo.ip,
      userAgent: requestInfo.userAgent,
      requestId: requestInfo.requestId,
      contentLength: res.getHeader('content-length') || (responseBody ? responseBody.length : 0),
      requestSize: requestInfo.requestSize,
      referrer: requestInfo.referrer,
      userId: req.user?.id || req.auth?.email || null,
      errorMessage: res.statusCode >= 400 ? (responseBody ? JSON.parse(responseBody)?.message || JSON.parse(responseBody)?.error : null) : null
    };
    
    // Escribir en log principal de acceso (formato estándar)
    writeHttpLog(HTTP_LOG_CONFIG.httpAccessLog, responseInfo);
    
    // Log específico de respuestas
    const responseLogEntry = `RESPONSE | ${responseInfo.timestamp} | ${responseInfo.method} ${responseInfo.url} | STATUS: ${responseInfo.statusCode} | TIME: ${responseInfo.responseTime}ms | IP: ${responseInfo.ip} | SIZE: ${responseInfo.contentLength}b | REQ_ID: ${responseInfo.requestId}`;
    
    writeToLog(HTTP_LOG_CONFIG.httpResponsesLog, responseLogEntry);
    
    // Log de errores HTTP (códigos 4xx y 5xx)
    if (responseInfo.statusCode >= 400) {
      const errorLogEntry = `HTTP_ERROR | ${responseInfo.timestamp} | ${responseInfo.method} ${responseInfo.url} | STATUS: ${responseInfo.statusCode} | TIME: ${responseInfo.responseTime}ms | IP: ${responseInfo.ip} | ERROR: ${responseInfo.errorMessage || 'No message'} | REQ_ID: ${responseInfo.requestId}`;
      
      writeToLog(HTTP_LOG_CONFIG.httpErrorsLog, errorLogEntry);
    }
    
    // Console log con colores según el código de estado
    const statusEmoji = getStatusEmoji(responseInfo.statusCode);
    const timeColor = responseTime > 1000 ? '🐌' : responseTime > 500 ? '⚡' : '⚡';
    
    console.log(`${statusEmoji} ${responseInfo.statusCode} | ${responseInfo.method} ${responseInfo.url} | ${timeColor} ${responseInfo.responseTime}ms | ${responseInfo.ip}`);
    
    // Llamar al método original
    return originalEnd.call(this, chunk, encoding);
  };
  
  // Continuar con el siguiente middleware
  next();
};

/**
 * Función para obtener emoji según código de estado HTTP
 * @param {number} statusCode - Código de estado HTTP
 * @returns {string} Emoji correspondiente
 */
const getStatusEmoji = (statusCode) => {
  if (statusCode >= 200 && statusCode < 300) return '✅'; // 2xx Success
  if (statusCode >= 300 && statusCode < 400) return '↗️';  // 3xx Redirection
  if (statusCode >= 400 && statusCode < 500) return '❌'; // 4xx Client Error
  if (statusCode >= 500) return '💥';                     // 5xx Server Error
  return '❓'; // Unknown
};

/**
 * Middleware adicional para logs detallados por tipo de operación
 */
const logOperationDetails = (operationType) => {
  return (req, res, next) => {
    const operationStart = Date.now();
    
    // Log específico de la operación
    const operationLogEntry = `OPERATION_START | ${new Date().toISOString()} | TYPE: ${operationType} | ${req.method} ${req.url} | IP: ${req.ip || 'unknown'} | REQ_ID: ${res.getHeader('X-Request-ID')}`;
    
    writeToLog(HTTP_LOG_CONFIG.httpRequestsLog, operationLogEntry);
    
    // Interceptar la respuesta para log de finalización
    const originalJson = res.json;
    res.json = function(data) {
      const operationEnd = Date.now();
      const operationTime = operationEnd - operationStart;
      
      const operationCompleteEntry = `OPERATION_END | ${new Date().toISOString()} | TYPE: ${operationType} | ${req.method} ${req.url} | STATUS: ${res.statusCode} | TIME: ${operationTime}ms | SUCCESS: ${res.statusCode < 400} | REQ_ID: ${res.getHeader('X-Request-ID')}`;
      
      writeToLog(HTTP_LOG_CONFIG.httpRequestsLog, operationCompleteEntry);
      
      return originalJson.call(this, data);
    };
    
    next();
  };
};

// Aplicar el middleware de logging HTTP GLOBALMENTE
app.use(httpRequestLogger);

console.log('📊 Sistema de logging HTTP configurado:');
console.log(`   📄 Peticiones: ${HTTP_LOG_CONFIG.httpRequestsLog}`);
console.log(`   📄 Respuestas: ${HTTP_LOG_CONFIG.httpResponsesLog}`);
console.log(`   📄 Acceso: ${HTTP_LOG_CONFIG.httpAccessLog}`);
console.log(`   📄 Errores HTTP: ${HTTP_LOG_CONFIG.httpErrorsLog}`);

// =====================================
// ENDPOINTS PARA CONSULTAR LOGS HTTP (PRÁCTICA 5)
// =====================================

/**
 * Paso 76: Endpoint para verificar logs de peticiones HTTP
 */
app.get('/api/logs/http-requests', (req, res) => {
  try {
    const { lines = 50, filter } = req.query;
    
    if (fs.existsSync(HTTP_LOG_CONFIG.httpRequestsLog)) {
      let logs = fs.readFileSync(HTTP_LOG_CONFIG.httpRequestsLog, 'utf8');
      let logLines = logs.split('\n').filter(line => line.trim());
      
      if (filter) {
        logLines = logLines.filter(line => 
          line.toLowerCase().includes(filter.toLowerCase())
        );
      }
      
      const recentLines = logLines.slice(-parseInt(lines));
      
      res.json({
        status: 'success',
        message: 'Logs de peticiones HTTP',
        data: recentLines,
        count: recentLines.length,
        totalLines: logLines.length,
        filter: filter || 'none',
        file: HTTP_LOG_CONFIG.httpRequestsLog,
        timestamp: new Date().toISOString()
      });
    } else {
      res.json({
        status: 'success',
        message: 'No hay logs de peticiones HTTP',
        data: [],
        count: 0
      });
    }
  } catch (error) {
    console.error('Error leyendo logs HTTP:', error);
    res.status(500).json({
      status: 'error',
      message: 'Error al leer logs de peticiones HTTP',
      error: error.message
    });
  }
});

/**
 * Paso 76: Endpoint para verificar logs de respuestas con códigos de estado
 */
app.get('/api/logs/http-responses', (req, res) => {
  try {
    const { lines = 50, status_code, method } = req.query;
    
    if (fs.existsSync(HTTP_LOG_CONFIG.httpResponsesLog)) {
      let logs = fs.readFileSync(HTTP_LOG_CONFIG.httpResponsesLog, 'utf8');
      let logLines = logs.split('\n').filter(line => line.trim());
      
      // Filtrar por código de estado específico
      if (status_code) {
        logLines = logLines.filter(line => 
          line.includes(`STATUS: ${status_code}`)
        );
      }
      
      // Filtrar por método HTTP específico
      if (method) {
        logLines = logLines.filter(line => 
          line.includes(`${method.toUpperCase()} `)
        );
      }
      
      const recentLines = logLines.slice(-parseInt(lines));
      
      res.json({
        status: 'success',
        message: 'Logs de respuestas HTTP con códigos de estado',
        data: recentLines,
        count: recentLines.length,
        totalLines: logLines.length,
        filters: {
          statusCode: status_code || 'all',
          method: method || 'all'
        },
        file: HTTP_LOG_CONFIG.httpResponsesLog,
        timestamp: new Date().toISOString()
      });
    } else {
      res.json({
        status: 'success',
        message: 'No hay logs de respuestas HTTP',
        data: [],
        count: 0
      });
    }
  } catch (error) {
    console.error('Error leyendo logs de respuestas:', error);
    res.status(500).json({
      status: 'error',
      message: 'Error al leer logs de respuestas HTTP',
      error: error.message
    });
  }
});

/**
 * Paso 76: Endpoint para verificar log de acceso estilo Apache
 */
app.get('/api/logs/access', (req, res) => {
  try {
    const { lines = 50 } = req.query;
    
    if (fs.existsSync(HTTP_LOG_CONFIG.httpAccessLog)) {
      const logs = fs.readFileSync(HTTP_LOG_CONFIG.httpAccessLog, 'utf8');
      const logLines = logs.split('\n').filter(line => line.trim()).slice(-parseInt(lines));
      
      res.json({
        status: 'success',
        message: 'Log de acceso HTTP (formato Apache Combined)',
        data: logLines,
        count: logLines.length,
        format: 'Apache Combined Log Format + Custom Fields',
        file: HTTP_LOG_CONFIG.httpAccessLog,
        timestamp: new Date().toISOString()
      });
    } else {
      res.json({
        status: 'success',
        message: 'No hay logs de acceso',
        data: [],
        count: 0
      });
    }
  } catch (error) {
    console.error('Error leyendo log de acceso:', error);
    res.status(500).json({
      status: 'error',
      message: 'Error al leer log de acceso',
      error: error.message
    });
  }
});

/**
 * Paso 76: Endpoint para verificar logs de errores HTTP únicamente
 */
app.get('/api/logs/http-errors', (req, res) => {
  try {
    const { lines = 50, status_range } = req.query;
    
    if (fs.existsSync(HTTP_LOG_CONFIG.httpErrorsLog)) {
      let logs = fs.readFileSync(HTTP_LOG_CONFIG.httpErrorsLog, 'utf8');
      let logLines = logs.split('\n').filter(line => line.trim());
      
      // Filtrar por rango de códigos de estado (4xx o 5xx)
      if (status_range) {
        if (status_range === '4xx') {
          logLines = logLines.filter(line => 
            /STATUS: 4\d\d/.test(line)
          );
        } else if (status_range === '5xx') {
          logLines = logLines.filter(line => 
            /STATUS: 5\d\d/.test(line)
          );
        }
      }
      
      const recentLines = logLines.slice(-parseInt(lines));
      
      res.json({
        status: 'success',
        message: 'Logs de errores HTTP (códigos 4xx y 5xx)',
        data: recentLines,
        count: recentLines.length,
        totalErrors: logLines.length,
        filter: status_range || 'all_errors',
        file: HTTP_LOG_CONFIG.httpErrorsLog,
        timestamp: new Date().toISOString()
      });
    } else {
      res.json({
        status: 'success',
        message: 'No hay logs de errores HTTP',
        data: [],
        count: 0
      });
    }
  } catch (error) {
    console.error('Error leyendo logs de errores:', error);
    res.status(500).json({
      status: 'error',
      message: 'Error al leer logs de errores HTTP',
      error: error.message
    });
  }
});

/**
 * Endpoint para estadísticas generales de logs HTTP
 */
app.get('/api/logs/http-stats', (req, res) => {
  try {
    const stats = {
      logFiles: {},
      summary: {
        totalRequests: 0,
        successfulResponses: 0,
        clientErrors: 0,
        serverErrors: 0,
        averageResponseTime: 0
      }
    };
    
    // Verificar existencia y tamaño de archivos de log
    Object.keys(HTTP_LOG_CONFIG).forEach(key => {
      if (key.includes('Log')) {
        const filePath = HTTP_LOG_CONFIG[key];
        if (fs.existsSync(filePath)) {
          const fileStats = fs.statSync(filePath);
          stats.logFiles[key] = {
            path: filePath,
            size: `${(fileStats.size / 1024).toFixed(2)} KB`,
            lastModified: fileStats.mtime.toISOString(),
            exists: true
          };
        } else {
          stats.logFiles[key] = {
            path: filePath,
            exists: false
          };
        }
      }
    });
    
    // Analizar log de respuestas para generar estadísticas
    if (fs.existsSync(HTTP_LOG_CONFIG.httpResponsesLog)) {
      const logs = fs.readFileSync(HTTP_LOG_CONFIG.httpResponsesLog, 'utf8');
      const logLines = logs.split('\n').filter(line => line.trim());
      
      stats.summary.totalRequests = logLines.length;
      
      let totalTime = 0;
      let timeCount = 0;
      
      logLines.forEach(line => {
        // Extraer código de estado HTTP del log
        const statusMatch = line.match(/STATUS: (\d+)/);
        if (statusMatch) {
          const status = parseInt(statusMatch[1]);
          if (status >= 200 && status < 400) {
            stats.summary.successfulResponses++;
          } else if (status >= 400 && status < 500) {
            stats.summary.clientErrors++;
          } else if (status >= 500) {
            stats.summary.serverErrors++;
          }
        }
        
        // Extraer tiempo de respuesta del log
        const timeMatch = line.match(/TIME: (\d+)ms/);
        if (timeMatch) {
          totalTime += parseInt(timeMatch[1]);
          timeCount++;
        }
      });
      
      if (timeCount > 0) {
        stats.summary.averageResponseTime = Math.round(totalTime / timeCount);
      }
    }
    
    res.json({
      status: 'success',
      message: 'Estadísticas de logs HTTP',
      data: stats,
      timestamp: new Date().toISOString()
    });
    
  } catch (error) {
    console.error('Error obteniendo estadísticas de logs:', error);
    res.status(500).json({
      status: 'error',
      message: 'Error al obtener estadísticas de logs',
      error: error.message
    });
  }
});

// =====================================
// MIDDLEWARE GLOBAL DE ERRORES
// =====================================
// ⚠️ IMPORTANTE: Este middleware debe ir al final de todas las rutas
app.use(globalErrorHandler);

// =====================================
// INICIO DEL SERVIDOR
// =====================================
const PORT = process.env.PORT || 4000;
const server = app.listen(PORT, () => {
  console.log(`🚀 API Node.js corriendo en http://localhost:${PORT}`);
  console.log(`📁 Directorio de logs: ${LOG_CONFIG.logDir}`);
  console.log(`🔒 Manejo robusto de errores activado`);
  console.log(`🛡️ El servidor no se detendrá por errores inesperados`);
});

// =====================================
// MANEJO GRACEFUL DE CIERRE DEL SERVIDOR
// =====================================

process.on('SIGTERM', () => {
  console.log('🛑 SIGTERM recibido, cerrando servidor gracefully...');
  server.close(() => {
    console.log('✅ Servidor cerrado correctamente');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  console.log('🛑 SIGINT recibido (Ctrl+C), cerrando servidor gracefully...');
  server.close(() => {
    console.log('✅ Servidor cerrado correctamente');
    process.exit(0);
  });
});
