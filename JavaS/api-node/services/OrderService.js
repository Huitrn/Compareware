const TransactionManager = require('./TransactionManager');
const UserRepository = require('../repositories/UserRepository');
const OrderRepository = require('../repositories/OrderRepository');
const AuditLogRepository = require('../repositories/AuditLogRepository');

class OrderService {
  constructor() {
    this.userRepository = new UserRepository();
    this.orderRepository = new OrderRepository();
    this.auditLogRepository = new AuditLogRepository();
    this.transactionManager = TransactionManager;
  }

  /**
   * Crear pedido con transacción distribuida
   * Implementa el patrón Saga con compensación automática
   */
  async createOrderWithTransaction(userId, orderData, orderItems, requestInfo = {}) {
    const transactionId = this.transactionManager.generateTransactionId();
    
    try {
      // Definir las operaciones de la transacción distribuida
      const operations = [
        // Operación 1: Validar usuario
        {
          name: 'VALIDATE_USER',
          type: 'VALIDATION',
          data: { userId },
          execute: async (client, txnId) => {
            const user = await this.userRepository.findByIdSecure(userId, client);
            if (!user) {
              throw new Error(`Usuario ${userId} no encontrado`);
            }
            
            // Log de validación exitosa
            await this.auditLogRepository.createAuditLog({
              transaction_id: txnId,
              user_id: userId,
              action: 'USER_VALIDATED',
              entity_type: 'USER',
              entity_id: userId,
              ip_address: requestInfo.ip || 'UNKNOWN',
              user_agent: requestInfo.userAgent,
              status: 'SUCCESS'
            }, client);
            
            return user;
          }
        },
        
        // Operación 2: Validar stock de productos (simulado)
        {
          name: 'VALIDATE_STOCK',
          type: 'VALIDATION',
          data: { items: orderItems },
          execute: async (client, txnId) => {
            for (const item of orderItems) {
              // Simulación de validación de stock
              const stockQuery = `
                SELECT id, name, stock_quantity 
                FROM products 
                WHERE id = $1 AND stock_quantity >= $2
              `;
              
              const stockResult = await client.query(stockQuery, [item.product_id, item.quantity]);
              
              if (stockResult.rows.length === 0) {
                throw new Error(`Stock insuficiente para producto ${item.product_id}`);
              }
              
              // Log de validación de stock
              await this.auditLogRepository.createAuditLog({
                transaction_id: txnId,
                user_id: userId,
                action: 'STOCK_VALIDATED',
                entity_type: 'PRODUCT',
                entity_id: item.product_id,
                new_values: { 
                  requested_quantity: item.quantity,
                  available_stock: stockResult.rows[0].stock_quantity
                },
                ip_address: requestInfo.ip || 'UNKNOWN',
                status: 'SUCCESS'
              }, client);
            }
            
            return { validated: true, itemsCount: orderItems.length };
          }
        },
        
        // Operación 3: Reservar stock
        {
          name: 'RESERVE_STOCK',
          type: 'UPDATE',
          data: { items: orderItems },
          execute: async (client, txnId) => {
            const reservedItems = [];
            
            for (const item of orderItems) {
              const reserveQuery = `
                UPDATE products 
                SET stock_quantity = stock_quantity - $1,
                    reserved_quantity = COALESCE(reserved_quantity, 0) + $1,
                    updated_at = NOW()
                WHERE id = $2 AND stock_quantity >= $1
                RETURNING id, name, stock_quantity, reserved_quantity
              `;
              
              const reserveResult = await client.query(reserveQuery, [item.quantity, item.product_id]);
              
              if (reserveResult.rows.length === 0) {
                throw new Error(`Error reservando stock para producto ${item.product_id}`);
              }
              
              reservedItems.push(reserveResult.rows[0]);
              
              // Log de reserva de stock
              await this.auditLogRepository.createAuditLog({
                transaction_id: txnId,
                user_id: userId,
                action: 'STOCK_RESERVED',
                entity_type: 'PRODUCT',
                entity_id: item.product_id,
                new_values: {
                  reserved_quantity: item.quantity,
                  new_stock: reserveResult.rows[0].stock_quantity
                },
                ip_address: requestInfo.ip || 'UNKNOWN',
                status: 'SUCCESS'
              }, client);
            }
            
            return reservedItems;
          }
        },
        
        // Operación 4: Crear la orden
        {
          name: 'CREATE_ORDER',
          type: 'INSERT',
          data: { orderData, orderItems },
          execute: async (client, txnId) => {
            const orderWithItems = await this.orderRepository.createOrder(orderData, orderItems, client);
            
            // Log de creación de orden
            await this.auditLogRepository.createAuditLog({
              transaction_id: txnId,
              user_id: userId,
              action: 'ORDER_CREATED',
              entity_type: 'ORDER',
              entity_id: orderWithItems.order.id,
              new_values: {
                order_id: orderWithItems.order.id,
                total_amount: orderWithItems.order.total_amount,
                items_count: orderWithItems.items.length
              },
              ip_address: requestInfo.ip || 'UNKNOWN',
              user_agent: requestInfo.userAgent,
              status: 'SUCCESS'
            }, client);
            
            return orderWithItems;
          }
        },
        
        // Operación 5: Procesar pago (simulado)
        {
          name: 'PROCESS_PAYMENT',
          type: 'EXTERNAL',
          data: { 
            amount: orderData.total_amount,
            paymentMethod: orderData.payment_method
          },
          execute: async (client, txnId) => {
            // Simulación de procesamiento de pago
            const paymentResult = await this.simulatePaymentProcessing(
              orderData.total_amount, 
              orderData.payment_method
            );
            
            if (!paymentResult.success) {
              throw new Error(`Error procesando pago: ${paymentResult.error}`);
            }
            
            // Log de pago procesado
            await this.auditLogRepository.createAuditLog({
              transaction_id: txnId,
              user_id: userId,
              action: 'PAYMENT_PROCESSED',
              entity_type: 'PAYMENT',
              new_values: {
                amount: orderData.total_amount,
                payment_method: orderData.payment_method,
                payment_id: paymentResult.paymentId,
                status: 'APPROVED'
              },
              ip_address: requestInfo.ip || 'UNKNOWN',
              status: 'SUCCESS'
            }, client);
            
            return paymentResult;
          }
        }
      ];

      // Ejecutar transacción distribuida
      console.log(`🔄 Iniciando transacción distribuida: ${transactionId}`);
      const result = await this.transactionManager.executeDistributedTransaction(operations);
      
      if (result.success) {
        console.log(`✅ Transacción distribuida completada: ${transactionId}`);
        
        // Log final de éxito
        await this.auditLogRepository.createSystemLog(
          'DISTRIBUTED_TRANSACTION_SUCCESS',
          {
            transactionId: transactionId,
            operationsCompleted: operations.length,
            orderId: result.results[3]?.order?.id,
            userId: userId,
            totalAmount: orderData.total_amount
          }
        );
        
        return {
          success: true,
          transactionId: transactionId,
          order: result.results[3],
          payment: result.results[4],
          message: 'Pedido creado exitosamente con transacción distribuida'
        };
      } else {
        console.log(`❌ Transacción distribuida falló: ${transactionId}`);
        
        // Log final de error
        await this.auditLogRepository.createSystemLog(
          'DISTRIBUTED_TRANSACTION_FAILED',
          {
            transactionId: transactionId,
            error: result.error,
            userId: userId,
            orderData: orderData
          },
          'ERROR'
        );
        
        return {
          success: false,
          transactionId: transactionId,
          error: result.error,
          message: 'Error creando pedido. Todas las operaciones han sido revertidas.'
        };
      }
      
    } catch (error) {
      console.error(`💥 Error en transacción distribuida ${transactionId}:`, error.message);
      
      // Log de error crítico
      await this.auditLogRepository.createSystemLog(
        'DISTRIBUTED_TRANSACTION_CRITICAL_ERROR',
        {
          transactionId: transactionId,
          error: error.message,
          stack: error.stack,
          userId: userId
        },
        'ERROR'
      );
      
      throw error;
    }
  }

  /**
   * Simulación de procesamiento de pago externo
   */
  async simulatePaymentProcessing(amount, paymentMethod) {
    return new Promise((resolve) => {
      setTimeout(() => {
        // Simular 5% de fallos en pagos
        const success = Math.random() > 0.05;
        
        if (success) {
          resolve({
            success: true,
            paymentId: `pay_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            amount: amount,
            method: paymentMethod,
            status: 'APPROVED',
            processedAt: new Date().toISOString()
          });
        } else {
          resolve({
            success: false,
            error: 'Payment gateway timeout',
            amount: amount,
            method: paymentMethod,
            status: 'DECLINED',
            processedAt: new Date().toISOString()
          });
        }
      }, 100); // Simular latencia de red
    });
  }

  /**
   * Obtener orden con historial completo
   */
  async getOrderWithHistory(orderId, requestInfo = {}) {
    try {
      // Buscar la orden con items
      const orderData = await this.orderRepository.findOrderWithItems(orderId);
      
      if (!orderData) {
        return null;
      }

      // Buscar historial de auditoría
      const auditLogs = await this.auditLogRepository.findByEntity('ORDER', orderId);
      
      // Log de consulta
      await this.auditLogRepository.createAuditLog({
        transaction_id: `query_${Date.now()}`,
        action: 'ORDER_QUERIED',
        entity_type: 'ORDER',
        entity_id: orderId,
        ip_address: requestInfo.ip || 'UNKNOWN',
        user_agent: requestInfo.userAgent,
        status: 'SUCCESS'
      });

      return {
        order: orderData.order,
        items: orderData.items,
        history: auditLogs
      };
    } catch (error) {
      console.error('Error obteniendo orden con historial:', error.message);
      throw error;
    }
  }

  /**
   * Cancelar orden con transacción distribuida
   */
  async cancelOrderWithTransaction(orderId, reason, userId, requestInfo = {}) {
    const transactionId = this.transactionManager.generateTransactionId();
    
    try {
      const operations = [
        // Operación 1: Validar que la orden se puede cancelar
        {
          name: 'VALIDATE_CANCELLATION',
          type: 'VALIDATION',
          data: { orderId, reason },
          execute: async (client, txnId) => {
            const order = await this.orderRepository.findById(orderId, client);
            
            if (!order) {
              throw new Error(`Orden ${orderId} no encontrada`);
            }
            
            if (!['PENDING', 'PROCESSING'].includes(order.status)) {
              throw new Error(`Orden ${orderId} no se puede cancelar. Estado actual: ${order.status}`);
            }
            
            return order;
          }
        },
        
        // Operación 2: Cancelar la orden
        {
          name: 'CANCEL_ORDER',
          type: 'UPDATE',
          data: { orderId, reason },
          execute: async (client, txnId) => {
            const cancelledOrder = await this.orderRepository.cancelOrder(orderId, reason, client);
            
            await this.auditLogRepository.createAuditLog({
              transaction_id: txnId,
              user_id: userId,
              action: 'ORDER_CANCELLED',
              entity_type: 'ORDER',
              entity_id: orderId,
              new_values: { status: 'CANCELLED', reason: reason },
              ip_address: requestInfo.ip || 'UNKNOWN',
              status: 'SUCCESS'
            }, client);
            
            return cancelledOrder;
          }
        },
        
        // Operación 3: Liberar stock reservado
        {
          name: 'RELEASE_STOCK',
          type: 'UPDATE',
          data: { orderId },
          execute: async (client, txnId) => {
            // Buscar items de la orden para liberar stock
            const itemsQuery = `
              SELECT product_id, quantity 
              FROM order_items 
              WHERE order_id = $1
            `;
            
            const itemsResult = await client.query(itemsQuery, [orderId]);
            
            for (const item of itemsResult.rows) {
              const releaseQuery = `
                UPDATE products 
                SET stock_quantity = stock_quantity + $1,
                    reserved_quantity = COALESCE(reserved_quantity, 0) - $1,
                    updated_at = NOW()
                WHERE id = $2
                RETURNING id, name, stock_quantity
              `;
              
              await client.query(releaseQuery, [item.quantity, item.product_id]);
              
              await this.auditLogRepository.createAuditLog({
                transaction_id: txnId,
                user_id: userId,
                action: 'STOCK_RELEASED',
                entity_type: 'PRODUCT',
                entity_id: item.product_id,
                new_values: { released_quantity: item.quantity },
                ip_address: requestInfo.ip || 'UNKNOWN',
                status: 'SUCCESS'
              }, client);
            }
            
            return { releasedItems: itemsResult.rows.length };
          }
        }
      ];

      const result = await this.transactionManager.executeDistributedTransaction(operations);
      
      if (result.success) {
        await this.auditLogRepository.createSystemLog(
          'ORDER_CANCELLATION_SUCCESS',
          {
            transactionId: transactionId,
            orderId: orderId,
            reason: reason,
            userId: userId
          }
        );
      }
      
      return result;
      
    } catch (error) {
      await this.auditLogRepository.createSystemLog(
        'ORDER_CANCELLATION_ERROR',
        {
          transactionId: transactionId,
          orderId: orderId,
          error: error.message,
          userId: userId
        },
        'ERROR'
      );
      
      throw error;
    }
  }

  /**
   * Obtener estadísticas de transacciones
   */
  async getTransactionStats() {
    try {
      const stats = await this.auditLogRepository.getAuditStats(7);
      const failedTransactions = await this.auditLogRepository.findFailedTransactions(24);
      
      return {
        weekly_stats: stats,
        failed_transactions_last_24h: failedTransactions,
        active_transactions: this.transactionManager.getActiveTransactions()
      };
    } catch (error) {
      console.error('Error obteniendo estadísticas:', error.message);
      throw error;
    }
  }
}

module.exports = OrderService;