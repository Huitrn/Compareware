const pool = require('../config/db');
const { logSecurityEvent } = require('../middlewares/logging');

class TransactionManager {
  constructor() {
    this.pool = pool;
    this.activeTransactions = new Map();
  }

  /**
   * Generar ID único para transacción distribuida
   */
  generateTransactionId() {
    return `txn_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  /**
   * Iniciar transacción distribuida
   */
  async beginDistributedTransaction(transactionId = null) {
    const txnId = transactionId || this.generateTransactionId();
    
    try {
      const client = await this.pool.connect();
      await client.query('BEGIN');
      
      const transaction = {
        id: txnId,
        client: client,
        startTime: new Date(),
        operations: [],
        status: 'ACTIVE'
      };
      
      this.activeTransactions.set(txnId, transaction);
      
      // Log del inicio de transacción distribuida
      this.logTransaction('DISTRIBUTED_TRANSACTION_STARTED', {
        transactionId: txnId,
        startTime: transaction.startTime.toISOString()
      });
      
      return txnId;
    } catch (error) {
      this.logTransaction('DISTRIBUTED_TRANSACTION_START_ERROR', {
        transactionId: txnId,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Obtener cliente de transacción
   */
  getTransactionClient(transactionId) {
    const transaction = this.activeTransactions.get(transactionId);
    if (!transaction) {
      throw new Error(`Transacción ${transactionId} no encontrada`);
    }
    return transaction.client;
  }

  /**
   * Registrar operación en transacción
   */
  registerOperation(transactionId, operation) {
    const transaction = this.activeTransactions.get(transactionId);
    if (!transaction) {
      throw new Error(`Transacción ${transactionId} no encontrada`);
    }
    
    transaction.operations.push({
      ...operation,
      timestamp: new Date().toISOString()
    });

    this.logTransaction('OPERATION_REGISTERED', {
      transactionId: transactionId,
      operation: operation
    });
  }

  /**
   * Confirmar transacción distribuida
   */
  async commitDistributedTransaction(transactionId) {
    const transaction = this.activeTransactions.get(transactionId);
    
    if (!transaction) {
      throw new Error(`Transacción ${transactionId} no encontrada`);
    }

    try {
      // Confirmar en base de datos
      await transaction.client.query('COMMIT');
      
      // Actualizar estado
      transaction.status = 'COMMITTED';
      transaction.endTime = new Date();
      
      // Log de commit exitoso
      this.logTransaction('DISTRIBUTED_TRANSACTION_COMMITTED', {
        transactionId: transactionId,
        operationsCount: transaction.operations.length,
        duration: transaction.endTime - transaction.startTime,
        operations: transaction.operations
      });
      
      // Crear log de auditoría
      await this.createAuditLog(transactionId, 'COMMIT');
      
      return true;
    } catch (error) {
      // Si falla el commit, hacer rollback
      await this.rollbackDistributedTransaction(transactionId);
      throw error;
    } finally {
      // Liberar recursos
      transaction.client.release();
      this.activeTransactions.delete(transactionId);
    }
  }

  /**
   * Revertir transacción distribuida
   */
  async rollbackDistributedTransaction(transactionId) {
    const transaction = this.activeTransactions.get(transactionId);
    
    if (!transaction) {
      throw new Error(`Transacción ${transactionId} no encontrada`);
    }

    try {
      // Rollback en base de datos
      await transaction.client.query('ROLLBACK');
      
      // Actualizar estado
      transaction.status = 'ROLLED_BACK';
      transaction.endTime = new Date();
      
      // Log de rollback
      this.logTransaction('DISTRIBUTED_TRANSACTION_ROLLED_BACK', {
        transactionId: transactionId,
        operationsCount: transaction.operations.length,
        duration: transaction.endTime - transaction.startTime,
        operations: transaction.operations
      });
      
      // Crear log de auditoría
      await this.createAuditLog(transactionId, 'ROLLBACK');
      
      return true;
    } catch (error) {
      this.logTransaction('DISTRIBUTED_TRANSACTION_ROLLBACK_ERROR', {
        transactionId: transactionId,
        error: error.message
      });
      throw error;
    } finally {
      // Liberar recursos
      transaction.client.release();
      this.activeTransactions.delete(transactionId);
    }
  }

  /**
   * Ejecutar transacción distribuida con compensación automática
   */
  async executeDistributedTransaction(operations) {
    const transactionId = this.generateTransactionId();
    
    try {
      await this.beginDistributedTransaction(transactionId);
      const client = this.getTransactionClient(transactionId);
      
      const results = [];
      
      // Ejecutar todas las operaciones
      for (const operation of operations) {
        try {
          const result = await operation.execute(client, transactionId);
          
          this.registerOperation(transactionId, {
            name: operation.name,
            type: operation.type,
            result: 'SUCCESS',
            data: operation.data
          });
          
          results.push(result);
        } catch (error) {
          // Si una operación falla, registrar y lanzar error
          this.registerOperation(transactionId, {
            name: operation.name,
            type: operation.type,
            result: 'ERROR',
            error: error.message,
            data: operation.data
          });
          
          throw error;
        }
      }
      
      // Si todas las operaciones fueron exitosas, confirmar
      await this.commitDistributedTransaction(transactionId);
      
      return {
        transactionId: transactionId,
        success: true,
        results: results
      };
      
    } catch (error) {
      // Si algo falla, revertir toda la transacción
      await this.rollbackDistributedTransaction(transactionId);
      
      return {
        transactionId: transactionId,
        success: false,
        error: error.message
      };
    }
  }

  /**
   * Crear log de auditoría en base de datos
   */
  async createAuditLog(transactionId, action) {
    try {
      const transaction = this.activeTransactions.get(transactionId) || 
                         { operations: [], startTime: new Date(), endTime: new Date() };
      
      // Usar una nueva conexión para el log de auditoría
      const client = await this.pool.connect();
      
      try {
        await client.query(`
          INSERT INTO audit_logs (
            transaction_id, 
            action, 
            operations_count,
            start_time,
            end_time,
            duration_ms,
            operations_detail,
            created_at
          ) VALUES ($1, $2, $3, $4, $5, $6, $7, NOW())
        `, [
          transactionId,
          action,
          transaction.operations.length,
          transaction.startTime,
          transaction.endTime || new Date(),
          transaction.endTime ? (transaction.endTime - transaction.startTime) : 0,
          JSON.stringify(transaction.operations)
        ]);
      } finally {
        client.release();
      }
    } catch (error) {
      console.error('Error creando audit log:', error.message);
      // No lanzar error para no afectar la transacción principal
    }
  }

  /**
   * Obtener transacciones activas
   */
  getActiveTransactions() {
    const transactions = [];
    
    for (const [id, transaction] of this.activeTransactions.entries()) {
      transactions.push({
        id: id,
        status: transaction.status,
        startTime: transaction.startTime,
        operationsCount: transaction.operations.length
      });
    }
    
    return transactions;
  }

  /**
   * Log de transacciones
   */
  logTransaction(action, data = {}) {
    logSecurityEvent(action, {
      ip: 'TRANSACTION_MANAGER',
      route: 'DISTRIBUTED_TRANSACTION',
      message: `${action}`,
      data: {
        manager: 'TransactionManager',
        ...data,
        timestamp: new Date().toISOString()
      }
    });
  }
}

// Instancia singleton
const transactionManager = new TransactionManager();

module.exports = transactionManager;