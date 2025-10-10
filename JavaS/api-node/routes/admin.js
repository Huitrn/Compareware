const express = require('express');
const router = express.Router();
const requireAuthentication = require('../middlewares/auth');
const { requireRole } = require('../middlewares/roles');

// Panel de administración (solo admin)
router.get('/', requireAuthentication, requireRole('admin'), (req, res) => {
  res.json({
    mensaje: 'Panel de administración',
    usuario: req.user,
    funciones_admin: [
      'Gestionar usuarios',
      'Moderar comentarios',
      'Estadísticas del sistema',
      'Configuración global'
    ],
    nivel_acceso: 'Administrador'
  });
});

module.exports = router;
