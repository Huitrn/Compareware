const BaseRepository = require('./BaseRepository');

class AuditLogRepository extends BaseRepository {
  constructor() {
    super('audit_logs');
  }

  /**
   * Crear log de auditoría completo
   */
  async createAuditLog(logData, client = null) {
    try {
      const auditLogToCreate = {
        transaction_id: logData.transaction_id,
        user_id: logData.user_id || null,
        action: logData.action,
        entity_type: logData.entity_type,
        entity_id: logData.entity_id || null,
        old_values: logData.old_values ? JSON.stringify(logData.old_values) : null,
        new_values: logData.new_values ? JSON.stringify(logData.new_values) : null,
        ip_address: logData.ip_address,
        user_agent: logData.user_agent || null,
        operations_count: logData.operations_count || 1,
        start_time: logData.start_time || new Date(),
        end_time: logData.end_time || new Date(),
        duration_ms: logData.duration_ms || 0,
        operations_detail: logData.operations_detail ? JSON.stringify(logData.operations_detail) : null,
        status: logData.status || 'SUCCESS',
        error_message: logData.error_message || null,
        created_at: new Date()
      };

      const auditLog = await this.create(auditLogToCreate, client);
      
      this.logOperation('AUDIT_LOG_CREATED', {
        auditLogId: auditLog.id,
        transactionId: auditLog.transaction_id,
        action: auditLog.action,
        entityType: auditLog.entity_type
      });

      return auditLog;
    } catch (error) {
      this.logOperation('AUDIT_LOG_CREATION_ERROR', {
        transactionId: logData.transaction_id,
        action: logData.action,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Buscar logs por transacción
   */
  async findByTransactionId(transactionId, client = null) {
    try {
      const query = `
        SELECT al.*, u.name as user_name, u.email as user_email
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.id
        WHERE al.transaction_id = $1
        ORDER BY al.created_at DESC
      `;
      
      const result = await this.executeQuery(query, [transactionId], client);

      this.logOperation('AUDIT_LOGS_BY_TRANSACTION_FETCHED', {
        transactionId: transactionId,
        logsCount: result.rows.length
      });

      return result.rows;
    } catch (error) {
      this.logOperation('AUDIT_LOGS_BY_TRANSACTION_FETCH_ERROR', {
        transactionId: transactionId,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Buscar logs por usuario
   */
  async findByUserId(userId, page = 1, limit = 20, client = null) {
    try {
      const offset = (page - 1) * limit;
      
      const query = `
        SELECT *
        FROM audit_logs
        WHERE user_id = $1
        ORDER BY created_at DESC
        LIMIT $2 OFFSET $3
      `;
      
      const result = await this.executeQuery(query, [userId, limit, offset], client);

      this.logOperation('AUDIT_LOGS_BY_USER_FETCHED', {
        userId: userId,
        page: page,
        logsCount: result.rows.length
      });

      return result.rows;
    } catch (error) {
      this.logOperation('AUDIT_LOGS_BY_USER_FETCH_ERROR', {
        userId: userId,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Buscar logs por entidad
   */
  async findByEntity(entityType, entityId, client = null) {
    try {
      const query = `
        SELECT al.*, u.name as user_name, u.email as user_email
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.id
        WHERE al.entity_type = $1 AND al.entity_id = $2
        ORDER BY al.created_at DESC
      `;
      
      const result = await this.executeQuery(query, [entityType, entityId], client);

      this.logOperation('AUDIT_LOGS_BY_ENTITY_FETCHED', {
        entityType: entityType,
        entityId: entityId,
        logsCount: result.rows.length
      });

      return result.rows;
    } catch (error) {
      this.logOperation('AUDIT_LOGS_BY_ENTITY_FETCH_ERROR', {
        entityType: entityType,
        entityId: entityId,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Buscar logs por acción
   */
  async findByAction(action, dateFrom = null, dateTo = null, page = 1, limit = 50, client = null) {
    try {
      const offset = (page - 1) * limit;
      let query = `
        SELECT al.*, u.name as user_name, u.email as user_email
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.id
        WHERE al.action = $1
      `;
      
      const params = [action];
      
      if (dateFrom) {
        query += ` AND al.created_at >= $${params.length + 1}`;
        params.push(dateFrom);
      }
      
      if (dateTo) {
        query += ` AND al.created_at <= $${params.length + 1}`;
        params.push(dateTo);
      }
      
      query += ` ORDER BY al.created_at DESC LIMIT $${params.length + 1} OFFSET $${params.length + 2}`;
      params.push(limit, offset);
      
      const result = await this.executeQuery(query, params, client);

      this.logOperation('AUDIT_LOGS_BY_ACTION_FETCHED', {
        action: action,
        dateFrom: dateFrom,
        dateTo: dateTo,
        page: page,
        logsCount: result.rows.length
      });

      return result.rows;
    } catch (error) {
      this.logOperation('AUDIT_LOGS_BY_ACTION_FETCH_ERROR', {
        action: action,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Obtener estadísticas de auditoría
   */
  async getAuditStats(days = 7, client = null) {
    try {
      const query = `
        SELECT 
          action,
          entity_type,
          status,
          COUNT(*) as count,
          AVG(duration_ms) as avg_duration,
          MAX(duration_ms) as max_duration,
          MIN(duration_ms) as min_duration
        FROM audit_logs
        WHERE created_at >= CURRENT_DATE - INTERVAL '${days} days'
        GROUP BY action, entity_type, status
        ORDER BY count DESC
      `;
      
      const result = await this.executeQuery(query, [], client);

      this.logOperation('AUDIT_STATS_FETCHED', {
        days: days,
        statsCount: result.rows.length
      });

      return result.rows;
    } catch (error) {
      this.logOperation('AUDIT_STATS_FETCH_ERROR', {
        days: days,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Buscar transacciones fallidas
   */
  async findFailedTransactions(hours = 24, client = null) {
    try {
      const query = `
        SELECT *
        FROM audit_logs
        WHERE status = 'ERROR' 
          AND created_at >= NOW() - INTERVAL '${hours} hours'
        ORDER BY created_at DESC
      `;
      
      const result = await this.executeQuery(query, [], client);

      this.logOperation('FAILED_TRANSACTIONS_FETCHED', {
        hours: hours,
        failedCount: result.rows.length
      });

      return result.rows;
    } catch (error) {
      this.logOperation('FAILED_TRANSACTIONS_FETCH_ERROR', {
        hours: hours,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Limpiar logs antiguos
   */
  async cleanOldLogs(daysToKeep = 90, client = null) {
    try {
      const query = `
        DELETE FROM audit_logs
        WHERE created_at < NOW() - INTERVAL '${daysToKeep} days'
      `;
      
      const result = await this.executeQuery(query, [], client);

      this.logOperation('OLD_LOGS_CLEANED', {
        daysToKeep: daysToKeep,
        deletedCount: result.rowCount
      });

      return {
        deleted: result.rowCount,
        daysToKeep: daysToKeep
      };
    } catch (error) {
      this.logOperation('OLD_LOGS_CLEANUP_ERROR', {
        daysToKeep: daysToKeep,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Crear log de sistema
   */
  async createSystemLog(action, details, status = 'SUCCESS', client = null) {
    try {
      const systemLogData = {
        transaction_id: `system_${Date.now()}`,
        action: action,
        entity_type: 'SYSTEM',
        operations_detail: details,
        status: status,
        ip_address: 'SYSTEM',
        start_time: new Date(),
        end_time: new Date(),
        duration_ms: 0
      };

      return await this.createAuditLog(systemLogData, client);
    } catch (error) {
      console.error('Error creando log de sistema:', error.message);
      throw error;
    }
  }
}

module.exports = AuditLogRepository;