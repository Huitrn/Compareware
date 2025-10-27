const express = require('express');
const router = express.Router();
const perifericoController = require('../controllers/perifericoController');
const { requireAuthentication } = require('../middlewares/auth');
const { requireRole } = require('../middlewares/roles');

// Listar periféricos
router.get('/', perifericoController.list);

// Crear periférico (solo admin)
router.post('/', requireAuthentication, requireRole('admin'), perifericoController.create);

// Actualizar periférico (solo admin)
router.put('/:id', requireAuthentication, requireRole('admin'), perifericoController.update);

// Eliminar periférico (solo admin)
router.delete('/:id', requireAuthentication, requireRole('admin'), perifericoController.remove);

module.exports = router;
