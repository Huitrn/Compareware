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
      health: '/api/health'
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

module.exports = router;
