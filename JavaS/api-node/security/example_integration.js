/**
 * Ejemplo de Integración del Sistema de Seguridad en Express
 * Proyecto: Compareware - Desarrollo Backend
 * 
 * Este archivo muestra cómo integrar completamente el sistema de seguridad
 * en tu aplicación Express.js
 */

const express = require('express');
const { securitySuite } = require('./security/SecuritySuite');

const app = express();

// ========================================
// 1. CONFIGURACIÓN BÁSICA DE SEGURIDAD
// ========================================

// Middleware de parsing (debe ir antes de la seguridad)
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Headers de seguridad básicos
app.use((req, res, next) => {
  res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('X-Frame-Options', 'DENY');
  res.setHeader('X-XSS-Protection', '1; mode=block');
  res.setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
  next();
});

// ========================================
// 2. SISTEMA DE SEGURIDAD COMPAREWARE
// ========================================

// Aplicar todos los middlewares de seguridad
const securityMiddlewares = securitySuite.getSecurityMiddleware();
securityMiddlewares.forEach(middleware => {
  app.use(middleware);
});

// ========================================
// 3. RUTAS DE MONITOREO DE SEGURIDAD
// ========================================

// Status de seguridad (solo para admins)
app.get('/security/status', securitySuite.getSecurityStatusEndpoint());

// Penetration testing (solo en desarrollo)
app.post('/security/pentest', securitySuite.getPenTestingEndpoint());

// ========================================
// 4. RUTAS DE LA APLICACIÓN (EJEMPLOS)
// ========================================

// Ejemplo de conexión segura a base de datos
const db = require('./database/connection'); // Tu conexión de BD
const secureDb = securitySuite.createSecureDbConnection(db);

// Ruta de ejemplo usando query builder seguro
app.get('/api/users/:id', async (req, res) => {
  try {
    const { id } = req.params;
    
    // Usar el query builder seguro
    const result = await secureDb.safeQuery()
      .select(['id', 'nombre', 'email', 'created_at'])
      .from('usuarios')
      .where('id', '=', id)
      .limit(1)
      .execute();

    if (result.rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Usuario no encontrado'
      });
    }

    res.json({
      success: true,
      data: result.rows[0]
    });

  } catch (error) {
    console.error('Error en /api/users/:id:', error);
    res.status(500).json({
      success: false,
      message: 'Error interno del servidor'
    });
  }
});

// Ruta de búsqueda con validación avanzada
app.get('/api/users/search', async (req, res) => {
  try {
    const { query: searchQuery, limit = 10 } = req.query;
    
    if (!searchQuery) {
      return res.status(400).json({
        success: false,
        message: 'Parámetro de búsqueda requerido'
      });
    }

    // Usar queries rápidas predefinidas
    const result = await secureDb.safeQuery()
      .select(['id', 'nombre', 'email'])
      .from('usuarios')
      .where('nombre', 'ILIKE', `%${searchQuery}%`)
      .limit(parseInt(limit))
      .execute();

    res.json({
      success: true,
      data: result.rows,
      count: result.rows.length
    });

  } catch (error) {
    console.error('Error en /api/users/search:', error);
    res.status(500).json({
      success: false,
      message: 'Error interno del servidor'
    });
  }
});

// Ruta de login con logging de seguridad
app.post('/api/auth/login', async (req, res) => {
  try {
    const { email, password } = req.body;

    // Validación básica
    if (!email || !password) {
      return res.status(400).json({
        success: false,
        message: 'Email y contraseña requeridos'
      });
    }

    // Buscar usuario de forma segura
    const user = await secureDb.quickQueries.findUserByEmail(email);

    if (!user.rows.length) {
      // Log de intento de login fallido
      const { logFailedAccess } = require('./middlewares/logging');
      logFailedAccess({
        ip: req.ip,
        userAgent: req.get('User-Agent'),
        route: req.originalUrl,
        method: req.method,
        authType: 'EMAIL_PASSWORD',
        username: email,
        reason: 'USER_NOT_FOUND',
        statusCode: 401
      });

      return res.status(401).json({
        success: false,
        message: 'Credenciales inválidas'
      });
    }

    // Aquí iría la verificación de contraseña (bcrypt, etc.)
    const validPassword = true; // Placeholder
    
    if (!validPassword) {
      const { logFailedAccess } = require('./middlewares/logging');
      logFailedAccess({
        ip: req.ip,
        userAgent: req.get('User-Agent'),
        route: req.originalUrl,
        method: req.method,
        authType: 'EMAIL_PASSWORD',
        username: email,
        reason: 'INVALID_PASSWORD',
        statusCode: 401
      });

      return res.status(401).json({
        success: false,
        message: 'Credenciales inválidas'
      });
    }

    // Login exitoso
    const { logSecurityEvent } = require('./middlewares/logging');
    logSecurityEvent('SUCCESSFUL_LOGIN', {
      ip: req.ip,
      route: req.originalUrl,
      message: 'Usuario autenticado exitosamente',
      userId: user.rows[0].id,
      userAgent: req.get('User-Agent')
    });

    res.json({
      success: true,
      message: 'Login exitoso',
      // Aquí irían tokens, etc.
    });

  } catch (error) {
    console.error('Error en /api/auth/login:', error);
    res.status(500).json({
      success: false,
      message: 'Error interno del servidor'
    });
  }
});

// ========================================
// 5. MIDDLEWARE DE MANEJO DE ERRORES
// ========================================

const { errorLoggingMiddleware } = require('./middlewares/logging');
app.use(errorLoggingMiddleware);

app.use((err, req, res, next) => {
  console.error('Error no manejado:', err);
  
  res.status(500).json({
    success: false,
    message: 'Error interno del servidor',
    ...(process.env.NODE_ENV === 'development' && { error: err.message })
  });
});

// ========================================
// 6. CONFIGURACIÓN DE SERVIDOR
// ========================================

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log(`🚀 Servidor Compareware ejecutándose en puerto ${PORT}`);
  console.log(`🛡️  Sistema de seguridad activo y monitoreando`);
  
  // Configurar cleanup automático
  securitySuite.setupAutomaticCleanup();
  
  console.log('📊 Endpoints de monitoreo disponibles:');
  console.log(`   GET  http://localhost:${PORT}/security/status`);
  if (process.env.NODE_ENV !== 'production') {
    console.log(`   POST http://localhost:${PORT}/security/pentest`);
  }
});

module.exports = app;