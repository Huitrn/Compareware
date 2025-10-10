require('dotenv').config();
const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');

const addCustomHeaders = require('./middlewares/customHeaders');
const globalErrorHandler = require('./middlewares/errorHandler');
const rateLimiter = require('./middlewares/rateLimiter');
const { logSecurityEvent } = require('./middlewares/logging');
const pool = require('./config/db');
const routes = require('./routes');

const app = express();

// Middlewares globales
app.use(cors());
app.use(bodyParser.json());
app.use(addCustomHeaders);

// Validar conexión a la base de datos al iniciar
(async () => {
  try {
    console.log('🔍 Validando conexión a PostgreSQL...');
    const client = await pool.connect();
    await client.query('SELECT NOW()');
    client.release();
    console.log('✅ Conexión a PostgreSQL exitosa');
  } catch (error) {
    console.error('❌ Error conectando a PostgreSQL:', error.message);
    logSecurityEvent('DB_CONNECTION_ERROR', {
      ip: 'SERVER',
      route: 'GLOBAL',
      message: 'Error conectando a PostgreSQL',
      data: { error: error.message }
    });
  }
})();

// Rutas principales
app.use('/api', rateLimiter(), routes);

// Manejo global de errores
app.use(globalErrorHandler);

// Inicio del servidor
const PORT = process.env.PORT || 3000;
const server = app.listen(PORT, () => {
  console.log(`🚀 Compareware API corriendo en puerto ${PORT}`);
});

// Manejo de errores no capturados
process.on('uncaughtException', (error) => {
  console.error('🚨 ERROR NO CAPTURADO:', error);
  logSecurityEvent('UNCAUGHT_EXCEPTION', {
    ip: 'SERVER',
    route: 'GLOBAL',
    message: 'Error no capturado en el servidor',
    data: { error: error.message, stack: error.stack, timestamp: new Date().toISOString() }
  });
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('🚨 PROMESA RECHAZADA NO MANEJADA:', reason);
  logSecurityEvent('UNHANDLED_REJECTION', {
    ip: 'SERVER',
    route: 'GLOBAL',
    message: 'Promesa rechazada no manejada',
    data: { reason: reason, timestamp: new Date().toISOString() }
  });
});

// Manejo graceful de cierre del servidor
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