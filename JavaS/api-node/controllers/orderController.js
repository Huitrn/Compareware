const OrderService = require('../services/OrderService');
const { logSecurityEvent } = require('../middlewares/logging');

class OrderController {
  constructor() {
    this.orderService = new OrderService();
  }

  /**
   * Crear nuevo pedido con transacción distribuida
   */
  async createOrder(req, res) {
    try {
      const { orderData, orderItems } = req.body;
      const userId = req.user?.id || orderData.user_id;
      
      // Validaciones básicas
      if (!orderData || !orderItems || !Array.isArray(orderItems) || orderItems.length === 0) {
        return res.status(400).json({
          success: false,
          message: 'Datos de orden e items son requeridos',
          error: 'INVALID_REQUEST_DATA'
        });
      }

      if (!userId) {
        return res.status(400).json({
          success: false,
          message: 'ID de usuario es requerido',
          error: 'USER_ID_REQUIRED'
        });
      }

      // Calcular total si no viene calculado
      if (!orderData.total_amount) {
        orderData.total_amount = orderItems.reduce((total, item) => {
          return total + (item.unit_price * item.quantity);
        }, 0);
      }

      // Información de la request para auditoría
      const requestInfo = {
        ip: req.ip || req.connection.remoteAddress,
        userAgent: req.get('User-Agent'),
        method: req.method,
        url: req.originalUrl
      };

      console.log(`🛒 Creando pedido para usuario ${userId} con ${orderItems.length} items`);
      
      // Crear orden con transacción distribuida
      const result = await this.orderService.createOrderWithTransaction(
        userId, 
        orderData, 
        orderItems, 
        requestInfo
      );

      if (result.success) {
        logSecurityEvent('ORDER_CREATED_SUCCESS', {
          ip: requestInfo.ip,
          route: req.originalUrl,
          message: 'Pedido creado exitosamente',
          data: {
            orderId: result.order.order.id,
            userId: userId,
            transactionId: result.transactionId,
            totalAmount: orderData.total_amount
          }
        });

        res.status(201).json({
          success: true,
          message: result.message,
          data: {
            transactionId: result.transactionId,
            order: result.order,
            payment: result.payment
          }
        });
      } else {
        logSecurityEvent('ORDER_CREATED_FAILED', {
          ip: requestInfo.ip,
          route: req.originalUrl,
          message: 'Error creando pedido',
          data: {
            userId: userId,
            transactionId: result.transactionId,
            error: result.error
          }
        });

        res.status(400).json({
          success: false,
          message: result.message,
          error: result.error,
          transactionId: result.transactionId
        });
      }

    } catch (error) {
      console.error('💥 Error en createOrder:', error.message);
      
      logSecurityEvent('ORDER_CREATION_ERROR', {
        ip: req.ip,
        route: req.originalUrl,
        message: 'Error crítico creando pedido',
        data: {
          error: error.message,
          stack: error.stack,
          body: req.body
        }
      });

      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }

  /**
   * Obtener pedido con historial completo
   */
  async getOrderWithHistory(req, res) {
    try {
      const { orderId } = req.params;
      
      if (!orderId || isNaN(orderId)) {
        return res.status(400).json({
          success: false,
          message: 'ID de orden válido es requerido',
          error: 'INVALID_ORDER_ID'
        });
      }

      const requestInfo = {
        ip: req.ip || req.connection.remoteAddress,
        userAgent: req.get('User-Agent')
      };

      const orderData = await this.orderService.getOrderWithHistory(orderId, requestInfo);
      
      if (!orderData) {
        return res.status(404).json({
          success: false,
          message: 'Orden no encontrada',
          error: 'ORDER_NOT_FOUND'
        });
      }

      logSecurityEvent('ORDER_RETRIEVED', {
        ip: requestInfo.ip,
        route: req.originalUrl,
        message: 'Orden consultada exitosamente',
        data: {
          orderId: orderId,
          userId: req.user?.id
        }
      });

      res.status(200).json({
        success: true,
        message: 'Orden obtenida exitosamente',
        data: orderData
      });

    } catch (error) {
      console.error('Error obteniendo orden:', error.message);
      
      logSecurityEvent('ORDER_RETRIEVAL_ERROR', {
        ip: req.ip,
        route: req.originalUrl,
        message: 'Error obteniendo orden',
        data: {
          orderId: req.params.orderId,
          error: error.message
        }
      });

      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }

  /**
   * Cancelar pedido con transacción distribuida
   */
  async cancelOrder(req, res) {
    try {
      const { orderId } = req.params;
      const { reason } = req.body;
      const userId = req.user?.id;

      if (!orderId || isNaN(orderId)) {
        return res.status(400).json({
          success: false,
          message: 'ID de orden válido es requerido',
          error: 'INVALID_ORDER_ID'
        });
      }

      if (!reason || reason.trim().length === 0) {
        return res.status(400).json({
          success: false,
          message: 'Razón de cancelación es requerida',
          error: 'CANCELLATION_REASON_REQUIRED'
        });
      }

      const requestInfo = {
        ip: req.ip || req.connection.remoteAddress,
        userAgent: req.get('User-Agent')
      };

      console.log(`🚫 Cancelando orden ${orderId} por usuario ${userId}`);

      const result = await this.orderService.cancelOrderWithTransaction(
        orderId, 
        reason, 
        userId, 
        requestInfo
      );

      if (result.success) {
        logSecurityEvent('ORDER_CANCELLED_SUCCESS', {
          ip: requestInfo.ip,
          route: req.originalUrl,
          message: 'Orden cancelada exitosamente',
          data: {
            orderId: orderId,
            userId: userId,
            reason: reason,
            transactionId: result.transactionId
          }
        });

        res.status(200).json({
          success: true,
          message: 'Orden cancelada exitosamente',
          data: {
            transactionId: result.transactionId,
            order: result.results[1]
          }
        });
      } else {
        res.status(400).json({
          success: false,
          message: 'Error cancelando orden',
          error: result.error,
          transactionId: result.transactionId
        });
      }

    } catch (error) {
      console.error('Error cancelando orden:', error.message);
      
      logSecurityEvent('ORDER_CANCELLATION_ERROR', {
        ip: req.ip,
        route: req.originalUrl,
        message: 'Error cancelando orden',
        data: {
          orderId: req.params.orderId,
          error: error.message
        }
      });

      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }

  /**
   * Obtener estadísticas de transacciones
   */
  async getTransactionStats(req, res) {
    try {
      const stats = await this.orderService.getTransactionStats();

      logSecurityEvent('TRANSACTION_STATS_RETRIEVED', {
        ip: req.ip,
        route: req.originalUrl,
        message: 'Estadísticas de transacciones consultadas',
        data: {
          userId: req.user?.id,
          activeTransactions: stats.active_transactions.length
        }
      });

      res.status(200).json({
        success: true,
        message: 'Estadísticas obtenidas exitosamente',
        data: stats
      });

    } catch (error) {
      console.error('Error obteniendo estadísticas:', error.message);
      
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }

  /**
   * Listar órdenes de un usuario
   */
  async getUserOrders(req, res) {
    try {
      let userId;
      
      // Si el parámetro es 'me', usar el usuario autenticado
      if (req.params.userId === 'me' || !req.params.userId) {
        userId = req.user?.id;
      } else {
        userId = parseInt(req.params.userId);
      }
      
      const page = parseInt(req.query.page) || 1;
      const limit = parseInt(req.query.limit) || 10;

      if (!userId) {
        return res.status(400).json({
          success: false,
          message: 'ID de usuario es requerido',
          error: 'USER_ID_REQUIRED'
        });
      }

      const orders = await this.orderService.orderRepository.findByUserId(userId, page, limit);

      res.status(200).json({
        success: true,
        message: 'Órdenes del usuario obtenidas exitosamente',
        data: {
          orders: orders,
          pagination: {
            page: page,
            limit: limit,
            total: orders.length
          }
        }
      });

    } catch (error) {
      console.error('Error obteniendo órdenes del usuario:', error.message);
      
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }

  /**
   * Listar órdenes por estado
   */
  async getOrdersByStatus(req, res) {
    try {
      const { status } = req.params;
      const page = parseInt(req.query.page) || 1;
      const limit = parseInt(req.query.limit) || 10;

      const validStatuses = ['PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED'];
      
      if (!validStatuses.includes(status.toUpperCase())) {
        return res.status(400).json({
          success: false,
          message: 'Estado de orden inválido',
          error: 'INVALID_ORDER_STATUS',
          validStatuses: validStatuses
        });
      }

      const orders = await this.orderService.orderRepository.findByStatus(status.toUpperCase(), page, limit);

      res.status(200).json({
        success: true,
        message: `Órdenes con estado ${status} obtenidas exitosamente`,
        data: {
          orders: orders,
          status: status.toUpperCase(),
          pagination: {
            page: page,
            limit: limit,
            total: orders.length
          }
        }
      });

    } catch (error) {
      console.error('Error obteniendo órdenes por estado:', error.message);
      
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }
}

module.exports = new OrderController();