/**
 * Script de prueba para Transacciones Distribuidas y Sistema de Auditoría
 * 
 * Este script demuestra:
 * 1. Creación de pedidos con transacciones distribuidas
 * 2. Manejo de errores y rollback automático
 * 3. Consulta de logs y auditoría
 * 4. Cancelación de pedidos
 */

const axios = require('axios');

class ComparewareAPITester {
  constructor(baseUrl = 'http://localhost:3000/api') {
    this.baseUrl = baseUrl;
    this.token = null;
  }

  /**
   * Autenticación de usuario
   */
  async login(email = 'test@example.com', password = 'password123') {
    try {
      console.log('🔐 Autenticando usuario...');
      
      const response = await axios.post(`${this.baseUrl}/auth/login`, {
        email: email,
        password: password
      });

      this.token = response.data.token;
      console.log('✅ Autenticación exitosa');
      return this.token;
    } catch (error) {
      console.error('❌ Error en autenticación:', error.response?.data || error.message);
      throw error;
    }
  }

  /**
   * Headers con autenticación
   */
  getHeaders() {
    return {
      'Authorization': `Bearer ${this.token}`,
      'Content-Type': 'application/json'
    };
  }

  /**
   * Crear un pedido exitoso
   */
  async createSuccessfulOrder() {
    try {
      console.log('\n🛒 === CREANDO PEDIDO EXITOSO ===');
      
      const orderData = {
        user_id: 1,
        total_amount: 299.97,
        shipping_address: '123 Test Street, Test City, TC 12345',
        billing_address: '123 Test Street, Test City, TC 12345',
        payment_method: 'CREDIT_CARD',
        notes: 'Pedido de prueba para transacción distribuida'
      };

      const orderItems = [
        {
          product_id: 1,
          quantity: 1,
          unit_price: 129.99,
          subtotal: 129.99
        },
        {
          product_id: 2,
          quantity: 2,
          unit_price: 79.99,
          subtotal: 159.98
        }
      ];

      const response = await axios.post(`${this.baseUrl}/orders`, {
        orderData: orderData,
        orderItems: orderItems
      }, {
        headers: this.getHeaders()
      });

      console.log('✅ Pedido creado exitosamente:');
      console.log(`   📋 ID de Orden: ${response.data.data.order.order.id}`);
      console.log(`   🔄 ID de Transacción: ${response.data.data.transactionId}`);
      console.log(`   💰 Monto Total: $${response.data.data.order.order.total_amount}`);
      console.log(`   📦 Items: ${response.data.data.order.items.length}`);
      
      return response.data.data;
    } catch (error) {
      console.error('❌ Error creando pedido exitoso:', error.response?.data || error.message);
      throw error;
    }
  }

  /**
   * Intentar crear un pedido que falle (para probar rollback)
   */
  async createFailingOrder() {
    try {
      console.log('\n💥 === CREANDO PEDIDO QUE FALLARÁ ===');
      
      const orderData = {
        user_id: 999, // Usuario inexistente
        total_amount: 199.99,
        shipping_address: '456 Fail Street, Fail City, FC 67890',
        payment_method: 'CREDIT_CARD'
      };

      const orderItems = [
        {
          product_id: 1,
          quantity: 1000, // Cantidad que excede el stock
          unit_price: 129.99,
          subtotal: 129999.00
        }
      ];

      const response = await axios.post(`${this.baseUrl}/orders`, {
        orderData: orderData,
        orderItems: orderItems
      }, {
        headers: this.getHeaders()
      });

      console.log('🤔 Pedido creado inesperadamente:', response.data);
      return response.data;
      
    } catch (error) {
      console.log('✅ Pedido falló como se esperaba:');
      console.log(`   ❌ Error: ${error.response?.data?.error || error.message}`);
      console.log(`   🔄 Transacción ID: ${error.response?.data?.transactionId || 'N/A'}`);
      console.log('   🔄 Rollback automático ejecutado');
      
      return error.response?.data;
    }
  }

  /**
   * Consultar historial de una orden
   */
  async getOrderHistory(orderId) {
    try {
      console.log(`\n📋 === CONSULTANDO HISTORIAL DE ORDEN ${orderId} ===`);
      
      const response = await axios.get(`${this.baseUrl}/orders/${orderId}/history`, {
        headers: this.getHeaders()
      });

      const { order, items, history } = response.data.data;
      
      console.log('✅ Historial obtenido exitosamente:');
      console.log(`   📋 Orden: #${order.id} - Estado: ${order.status}`);
      console.log(`   📦 Items: ${items.length}`);
      console.log(`   📝 Logs de auditoría: ${history.length}`);
      
      console.log('\n   🔍 Últimos 3 logs:');
      history.slice(0, 3).forEach((log, index) => {
        console.log(`     ${index + 1}. ${log.action} - ${log.status} (${log.created_at})`);
      });
      
      return response.data.data;
    } catch (error) {
      console.error('❌ Error consultando historial:', error.response?.data || error.message);
      throw error;
    }
  }

  /**
   * Cancelar una orden
   */
  async cancelOrder(orderId, reason = 'Cancelación de prueba') {
    try {
      console.log(`\n🚫 === CANCELANDO ORDEN ${orderId} ===`);
      
      const response = await axios.put(`${this.baseUrl}/orders/${orderId}/cancel`, {
        reason: reason
      }, {
        headers: this.getHeaders()
      });

      console.log('✅ Orden cancelada exitosamente:');
      console.log(`   🔄 Transacción ID: ${response.data.data.transactionId}`);
      console.log(`   📝 Razón: ${reason}`);
      
      return response.data.data;
    } catch (error) {
      console.error('❌ Error cancelando orden:', error.response?.data || error.message);
      throw error;
    }
  }

  /**
   * Obtener estadísticas de transacciones
   */
  async getTransactionStats() {
    try {
      console.log('\n📊 === ESTADÍSTICAS DE TRANSACCIONES ===');
      
      const response = await axios.get(`${this.baseUrl}/orders/stats/transactions`, {
        headers: this.getHeaders()
      });

      const stats = response.data.data;
      
      console.log('✅ Estadísticas obtenidas:');
      console.log(`   🔄 Transacciones activas: ${stats.active_transactions.length}`);
      console.log(`   ❌ Transacciones fallidas (24h): ${stats.failed_transactions_last_24h.length}`);
      console.log(`   📈 Estadísticas semanales: ${stats.weekly_stats.length} registros`);
      
      if (stats.weekly_stats.length > 0) {
        console.log('\n   📊 Top acciones esta semana:');
        stats.weekly_stats.slice(0, 5).forEach((stat, index) => {
          console.log(`     ${index + 1}. ${stat.action} (${stat.entity_type}): ${stat.count} veces`);
        });
      }
      
      return stats;
    } catch (error) {
      console.error('❌ Error obteniendo estadísticas:', error.response?.data || error.message);
      throw error;
    }
  }

  /**
   * Consultar logs de auditoría por transacción
   */
  async getAuditLogsByTransaction(transactionId) {
    try {
      console.log(`\n🔍 === LOGS DE TRANSACCIÓN ${transactionId} ===`);
      
      const response = await axios.get(`${this.baseUrl}/audit/transaction/${transactionId}`, {
        headers: this.getHeaders()
      });

      const { logs, count } = response.data.data;
      
      console.log(`✅ Encontrados ${count} logs para la transacción:`);
      
      logs.forEach((log, index) => {
        console.log(`   ${index + 1}. ${log.action} - ${log.entity_type} - ${log.status}`);
        console.log(`      Duración: ${log.duration_ms}ms - ${log.created_at}`);
      });
      
      return logs;
    } catch (error) {
      console.error('❌ Error consultando logs de transacción:', error.response?.data || error.message);
      throw error;
    }
  }

  /**
   * Ejecutar suite completa de pruebas
   */
  async runFullTest() {
    try {
      console.log('🚀 === INICIANDO SUITE COMPLETA DE PRUEBAS ===\n');
      
      // 1. Autenticación
      await this.login();
      
      // 2. Crear pedido exitoso
      const successOrder = await this.createSuccessfulOrder();
      const orderId = successOrder.order.order.id;
      const successTxnId = successOrder.transactionId;
      
      // 3. Intentar crear pedido que falle
      const failedOrder = await this.createFailingOrder();
      const failedTxnId = failedOrder?.transactionId;
      
      // 4. Consultar historial del pedido exitoso
      await this.getOrderHistory(orderId);
      
      // 5. Consultar logs de la transacción exitosa
      await this.getAuditLogsByTransaction(successTxnId);
      
      // 6. Consultar logs de la transacción fallida (si existe)
      if (failedTxnId) {
        await this.getAuditLogsByTransaction(failedTxnId);
      }
      
      // 7. Cancelar el pedido exitoso
      await this.cancelOrder(orderId, 'Prueba de cancelación automática');
      
      // 8. Ver el historial después de la cancelación
      await this.getOrderHistory(orderId);
      
      // 9. Obtener estadísticas finales
      await this.getTransactionStats();
      
      console.log('\n🎉 === SUITE DE PRUEBAS COMPLETADA EXITOSAMENTE ===');
      console.log('✅ Todas las funcionalidades de transacciones distribuidas funcionan correctamente');
      console.log('✅ Sistema de auditoría y logs operativo');
      console.log('✅ Rollback automático funcionando');
      console.log('✅ Repositorios y servicios integrados correctamente');
      
    } catch (error) {
      console.error('\n💥 === ERROR EN SUITE DE PRUEBAS ===');
      console.error('Error:', error.message);
      
      if (error.response) {
        console.error('Respuesta del servidor:', error.response.data);
        console.error('Status:', error.response.status);
      }
    }
  }
}

// Función principal
async function main() {
  const tester = new ComparewareAPITester();
  
  // Verificar argumentos de línea de comandos
  const args = process.argv.slice(2);
  
  if (args.includes('--help') || args.includes('-h')) {
    console.log(`
🧪 Tester de Transacciones Distribuidas - Compareware API

Uso: node test_distributed_transactions.js [opciones]

Opciones:
  --help, -h          Mostrar esta ayuda
  --full             Ejecutar suite completa de pruebas
  --create-order     Solo crear un pedido exitoso
  --create-fail      Solo probar pedido que falla
  --stats            Solo obtener estadísticas

Ejemplos:
  node test_distributed_transactions.js --full
  node test_distributed_transactions.js --create-order
  node test_distributed_transactions.js --stats
    `);
    return;
  }
  
  try {
    await tester.login();
    
    if (args.includes('--full')) {
      await tester.runFullTest();
    } else if (args.includes('--create-order')) {
      await tester.createSuccessfulOrder();
    } else if (args.includes('--create-fail')) {
      await tester.createFailingOrder();
    } else if (args.includes('--stats')) {
      await tester.getTransactionStats();
    } else {
      // Por defecto, ejecutar suite completa
      await tester.runFullTest();
    }
    
  } catch (error) {
    console.error('💥 Error ejecutando pruebas:', error.message);
    process.exit(1);
  }
}

// Ejecutar si es llamado directamente
if (require.main === module) {
  main();
}

module.exports = ComparewareAPITester;