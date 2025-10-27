const express = require('express');
const router = express.Router();
const { getHistorialComparaciones } = require('../controllers/historialController');
const { guardarComparacion } = require('../controllers/historialController');
const { requireAuthentication } = require('../middlewares/auth');

// Endpoint para obtener historial de comparaciones del usuario autenticado
router.get('/comparaciones', requireAuthentication, getHistorialComparaciones);

// Endpoint para guardar una comparación en el historial
router.post('/comparaciones', requireAuthentication, guardarComparacion);

module.exports = router;
