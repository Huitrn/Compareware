const express = require('express');
const router = express.Router();
const userController = require('../controllers/userController');
const requireAuthentication = require('../middlewares/auth');
const { requireRole } = require('../middlewares/roles');

// Obtener perfil del usuario autenticado
router.get('/me', requireAuthentication, userController.getProfile);

// Listar todos los usuarios (solo admin)
router.get('/', requireAuthentication, requireRole('admin'), userController.listUsers);

module.exports = router;
