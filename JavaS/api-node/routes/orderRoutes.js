const express = require('express');
const orderController = require('../controllers/orderController');
const { authenticateToken } = require('../middlewares/auth');
const rateLimiter = require('../middlewares/rateLimiter');
const { strictSQLProtection, readOnlySQLProtection } = require('../middlewares/sqlSecurityMiddleware');

const router = express.Router();

/**
 * @route POST /api/orders
 * @desc Crear nuevo pedido con transacción distribuida
 * @access Private
 */
router.post('/', 
  strictSQLProtection,  // 🛡️ Protección SQL estricta para creación
  authenticateToken, 
  rateLimiter(5, 1), // 5 requests por minuto
  orderController.createOrder
);

/**
 * @route GET /api/orders/:orderId/history
 * @desc Obtener pedido con historial completo de auditoría
 * @access Private
 */
router.get('/:orderId/history', 
  readOnlySQLProtection, // 🛡️ Protección SQL para lectura
  authenticateToken,
  orderController.getOrderWithHistory
);

/**
 * @route PUT /api/orders/:orderId/cancel
 * @desc Cancelar pedido con transacción distribuida
 * @access Private
 */
router.put('/:orderId/cancel', 
  authenticateToken,
  rateLimiter(3, 1), // 3 requests por minuto
  orderController.cancelOrder
);

/**
 * @route GET /api/orders/user/me
 * @desc Obtener órdenes del usuario autenticado
 * @access Private
 */
router.get('/user/me', 
  authenticateToken,
  orderController.getUserOrders
);

/**
 * @route GET /api/orders/user/:userId
 * @desc Obtener órdenes de un usuario específico
 * @access Private
 */
router.get('/user/:userId', 
  authenticateToken,
  orderController.getUserOrders
);

/**
 * @route GET /api/orders/status/:status
 * @desc Obtener órdenes por estado
 * @access Private
 */
router.get('/status/:status', 
  authenticateToken,
  orderController.getOrdersByStatus
);

/**
 * @route GET /api/orders/stats/transactions
 * @desc Obtener estadísticas de transacciones distribuidas
 * @access Private (Admin)
 */
router.get('/stats/transactions', 
  authenticateToken,
  // Aquí podrías agregar middleware para verificar rol de admin
  orderController.getTransactionStats
);

module.exports = router;