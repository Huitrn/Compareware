const express = require('express');
const router = express.Router();

// Ruta raíz de la API
router.get('/', (req, res) => {
  res.json({
    message: 'Compareware API',
    version: '1.0.0',
    status: 'running',
    endpoints: {
      auth: '/api/auth (POST register, login)',
      users: '/api/users (GET, POST, PUT, DELETE)',
      perifericos: '/api/perifericos (GET, POST, PUT, DELETE)',
      admin: '/api/admin (GET dashboard)',
      orders: '/api/orders (GET, POST, PUT - Transacciones Distribuidas)',
      audit: '/api/audit (GET - Logs y Auditoría)',
      health: '/api/health'
    },
    features: {
      distributed_transactions: 'Transacciones distribuidas con compensación automática',
      audit_logging: 'Sistema completo de auditoría y logs',
      repository_pattern: 'Capa de abstracción de datos con repositorios',
      transaction_manager: 'Gestor de transacciones con rollback automático'
    },
    timestamp: new Date().toISOString()
  });
});

// Ruta de health check
router.get('/health', (req, res) => {
  res.json({
    status: 'ok',
    message: 'API funcionando correctamente',
    timestamp: new Date().toISOString(),
    uptime: process.uptime()
  });
});

// Rutas modulares
router.use('/auth', require('./auth'));
router.use('/users', require('./users'));
router.use('/perifericos', require('./perifericos'));
router.use('/admin', require('./admin'));

// Historial de comparaciones
router.use('/historial', require('./historial'));

// Nuevas rutas - Transacciones Distribuidas y Auditoría
router.use('/orders', require('./orderRoutes'));
router.use('/audit', require('./auditRoutes'));

module.exports = router;
