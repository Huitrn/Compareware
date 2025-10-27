const BaseRepository = require('./BaseRepository');

class OrderRepository extends BaseRepository {
  constructor() {
    super('orders');
  }

  /**
   * Crear orden completa con items
   */
  async createOrder(orderData, orderItems, client = null) {
    try {
      // Crear la orden principal
      const orderToCreate = {
        user_id: orderData.user_id,
        total_amount: orderData.total_amount,
        status: orderData.status || 'PENDING',
        shipping_address: orderData.shipping_address,
        billing_address: orderData.billing_address,
        payment_method: orderData.payment_method,
        notes: orderData.notes || null,
        created_at: new Date(),
        updated_at: new Date()
      };

      const order = await this.create(orderToCreate, client);
      
      // Crear los items de la orden
      const createdItems = [];
      for (const item of orderItems) {
        const orderItemQuery = `
          INSERT INTO order_items (
            order_id, product_id, quantity, unit_price, subtotal, created_at
          ) VALUES ($1, $2, $3, $4, $5, NOW())
          RETURNING *
        `;
        
        const itemResult = await this.executeQuery(orderItemQuery, [
          order.id,
          item.product_id,
          item.quantity,
          item.unit_price,
          item.subtotal
        ], client);
        
        createdItems.push(itemResult.rows[0]);
      }

      this.logOperation('ORDER_CREATED_WITH_ITEMS', {
        orderId: order.id,
        userId: order.user_id,
        itemsCount: createdItems.length,
        totalAmount: order.total_amount
      });

      return {
        order: order,
        items: createdItems
      };
    } catch (error) {
      this.logOperation('ORDER_CREATION_ERROR', {
        userId: orderData.user_id,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Buscar orden con sus items
   */
  async findOrderWithItems(orderId, client = null) {
    try {
      // Buscar la orden
      const orderQuery = `
        SELECT o.*, u.name as user_name, u.email as user_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = $1
      `;
      
      const orderResult = await this.executeQuery(orderQuery, [orderId], client);
      
      if (orderResult.rows.length === 0) {
        return null;
      }

      const order = orderResult.rows[0];

      // Buscar los items de la orden
      const itemsQuery = `
        SELECT oi.*, p.name as product_name, p.description as product_description
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = $1
        ORDER BY oi.created_at
      `;
      
      const itemsResult = await this.executeQuery(itemsQuery, [orderId], client);

      this.logOperation('ORDER_WITH_ITEMS_FETCHED', {
        orderId: orderId,
        itemsCount: itemsResult.rows.length
      });

      return {
        order: order,
        items: itemsResult.rows
      };
    } catch (error) {
      this.logOperation('ORDER_FETCH_ERROR', {
        orderId: orderId,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Actualizar estado de orden
   */
  async updateStatus(orderId, newStatus, client = null) {
    try {
      const query = `
        UPDATE orders 
        SET status = $1, updated_at = NOW()
        WHERE id = $2
        RETURNING *
      `;
      
      const result = await this.executeQuery(query, [newStatus, orderId], client);

      if (result.rows.length > 0) {
        this.logOperation('ORDER_STATUS_UPDATED', {
          orderId: orderId,
          newStatus: newStatus,
          previousStatus: result.rows[0].status
        });
      }

      return result.rows[0] || null;
    } catch (error) {
      this.logOperation('ORDER_STATUS_UPDATE_ERROR', {
        orderId: orderId,
        newStatus: newStatus,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Buscar órdenes por usuario
   */
  async findByUserId(userId, page = 1, limit = 10, client = null) {
    try {
      const offset = (page - 1) * limit;
      
      const query = `
        SELECT o.*, 
               COUNT(oi.id) as items_count,
               SUM(oi.quantity) as total_items
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = $1
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT $2 OFFSET $3
      `;
      
      const result = await this.executeQuery(query, [userId, limit, offset], client);

      this.logOperation('USER_ORDERS_FETCHED', {
        userId: userId,
        page: page,
        count: result.rows.length
      });

      return result.rows;
    } catch (error) {
      this.logOperation('USER_ORDERS_FETCH_ERROR', {
        userId: userId,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Buscar órdenes por estado
   */
  async findByStatus(status, page = 1, limit = 10, client = null) {
    try {
      const offset = (page - 1) * limit;
      
      const query = `
        SELECT o.*, u.name as user_name, u.email as user_email,
               COUNT(oi.id) as items_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.status = $1
        GROUP BY o.id, u.id
        ORDER BY o.created_at DESC
        LIMIT $2 OFFSET $3
      `;
      
      const result = await this.executeQuery(query, [status, limit, offset], client);

      this.logOperation('ORDERS_BY_STATUS_FETCHED', {
        status: status,
        page: page,
        count: result.rows.length
      });

      return result.rows;
    } catch (error) {
      this.logOperation('ORDERS_BY_STATUS_FETCH_ERROR', {
        status: status,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Cancelar orden
   */
  async cancelOrder(orderId, reason, client = null) {
    try {
      const query = `
        UPDATE orders 
        SET status = 'CANCELLED', 
            cancellation_reason = $1,
            cancelled_at = NOW(),
            updated_at = NOW()
        WHERE id = $2 AND status IN ('PENDING', 'PROCESSING')
        RETURNING *
      `;
      
      const result = await this.executeQuery(query, [reason, orderId], client);

      if (result.rows.length > 0) {
        this.logOperation('ORDER_CANCELLED', {
          orderId: orderId,
          reason: reason,
          previousStatus: result.rows[0].status
        });
      }

      return result.rows[0] || null;
    } catch (error) {
      this.logOperation('ORDER_CANCELLATION_ERROR', {
        orderId: orderId,
        reason: reason,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Obtener estadísticas de órdenes
   */
  async getOrderStats(client = null) {
    try {
      const query = `
        SELECT 
          status,
          COUNT(*) as count,
          SUM(total_amount) as total_amount,
          AVG(total_amount) as avg_amount
        FROM orders
        WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'
        GROUP BY status
        ORDER BY count DESC
      `;
      
      const result = await this.executeQuery(query, [], client);

      this.logOperation('ORDER_STATS_FETCHED', {
        statsCount: result.rows.length
      });

      return result.rows;
    } catch (error) {
      this.logOperation('ORDER_STATS_FETCH_ERROR', {
        error: error.message
      });
      throw error;
    }
  }
}

module.exports = OrderRepository;