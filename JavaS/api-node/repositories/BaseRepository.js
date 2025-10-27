const pool = require('../config/db');
const { logSecurityEvent } = require('../middlewares/logging');
const { sqlValidator } = require('../security/SQLSecurityValidator');

class BaseRepository {
  constructor(tableName) {
    this.tableName = tableName;
    this.pool = pool;
  }

  /**
   * Ejecutar query con transacción opcional y validación de seguridad
   */
  async executeQuery(query, params = [], client = null, options = {}) {
    const dbClient = client || this.pool;
    
    try {
      // 🛡️ Validación adicional de seguridad en capa de repositorio
      if (options.validateSecurity !== false) {
        this.validateQuerySecurity(query, params);
      }
      
      const result = await dbClient.query(query, params);
      
      // Log de la operación
      this.logOperation('QUERY_EXECUTED', {
        table: this.tableName,
        query: query.substring(0, 100) + '...',
        paramsCount: params.length,
        rowCount: result.rowCount,
        securityValidated: options.validateSecurity !== false
      });
      
      return result;
    } catch (error) {
      this.logOperation('QUERY_ERROR', {
        table: this.tableName,
        query: query.substring(0, 100) + '...',
        error: error.message,
        params: params.map(p => typeof p === 'string' ? p.substring(0, 50) + '...' : p)
      });
      throw error;
    }
  }

  /**
   * Validar seguridad de query y parámetros
   */
  validateQuerySecurity(query, params) {
    // Validar que la query no contenga patrones peligrosos
    const queryValidation = sqlValidator.validateQuery(query, params);
    
    if (!queryValidation.isValid) {
      logSecurityEvent('REPOSITORY_SECURITY_VIOLATION', {
        table: this.tableName,
        query: query.substring(0, 200),
        violations: queryValidation.errors,
        timestamp: new Date().toISOString()
      });
      
      throw new Error(`Query de seguridad violada: ${queryValidation.errors.join(', ')}`);
    }

    // Validar parámetros individualmente si hay dudas
    params.forEach((param, index) => {
      if (typeof param === 'string') {
        const paramValidation = sqlValidator.validateAndSanitize(param, 'default');
        
        if (!paramValidation.isValid) {
          logSecurityEvent('PARAMETER_SECURITY_VIOLATION', {
            table: this.tableName,
            paramIndex: index,
            paramValue: param.substring(0, 100),
            violations: paramValidation.errors
          });
          
          // En lugar de fallar, sanitizar el parámetro
          params[index] = paramValidation.sanitized;
        }
      }
    });
  }

  /**
   * Iniciar transacción
   */
  async beginTransaction() {
    const client = await this.pool.connect();
    await client.query('BEGIN');
    
    this.logOperation('TRANSACTION_STARTED', {
      table: this.tableName,
      timestamp: new Date().toISOString()
    });
    
    return client;
  }

  /**
   * Confirmar transacción
   */
  async commitTransaction(client) {
    try {
      await client.query('COMMIT');
      this.logOperation('TRANSACTION_COMMITTED', {
        table: this.tableName,
        timestamp: new Date().toISOString()
      });
    } finally {
      client.release();
    }
  }

  /**
   * Revertir transacción
   */
  async rollbackTransaction(client) {
    try {
      await client.query('ROLLBACK');
      this.logOperation('TRANSACTION_ROLLED_BACK', {
        table: this.tableName,
        timestamp: new Date().toISOString()
      });
    } finally {
      client.release();
    }
  }

  /**
   * Crear registro con log
   */
  async create(data, client = null) {
    const columns = Object.keys(data);
    const values = Object.values(data);
    const placeholders = values.map((_, index) => `$${index + 1}`);

    const query = `
      INSERT INTO ${this.tableName} (${columns.join(', ')})
      VALUES (${placeholders.join(', ')})
      RETURNING *
    `;

    const result = await this.executeQuery(query, values, client);
    
    this.logOperation('RECORD_CREATED', {
      table: this.tableName,
      recordId: result.rows[0]?.id,
      data: data
    });

    return result.rows[0];
  }

  /**
   * Buscar por ID con log
   */
  async findById(id, client = null) {
    const query = `SELECT * FROM ${this.tableName} WHERE id = $1`;
    const result = await this.executeQuery(query, [id], client);
    
    this.logOperation('RECORD_FETCHED', {
      table: this.tableName,
      recordId: id,
      found: result.rows.length > 0
    });

    return result.rows[0] || null;
  }

  /**
   * Actualizar registro con log
   */
  async update(id, data, client = null) {
    const columns = Object.keys(data);
    const values = Object.values(data);
    const setClause = columns.map((col, index) => `${col} = $${index + 2}`);

    const query = `
      UPDATE ${this.tableName}
      SET ${setClause.join(', ')}, updated_at = NOW()
      WHERE id = $1
      RETURNING *
    `;

    const result = await this.executeQuery(query, [id, ...values], client);
    
    this.logOperation('RECORD_UPDATED', {
      table: this.tableName,
      recordId: id,
      data: data
    });

    return result.rows[0] || null;
  }

  /**
   * Eliminar registro con log
   */
  async delete(id, client = null) {
    const query = `DELETE FROM ${this.tableName} WHERE id = $1 RETURNING *`;
    const result = await this.executeQuery(query, [id], client);
    
    this.logOperation('RECORD_DELETED', {
      table: this.tableName,
      recordId: id,
      deleted: result.rows.length > 0
    });

    return result.rows[0] || null;
  }

  /**
   * Buscar todos con paginación
   */
  async findAll(page = 1, limit = 10, client = null) {
    const offset = (page - 1) * limit;
    const query = `
      SELECT * FROM ${this.tableName}
      ORDER BY created_at DESC
      LIMIT $1 OFFSET $2
    `;
    
    const result = await this.executeQuery(query, [limit, offset], client);
    
    this.logOperation('RECORDS_LISTED', {
      table: this.tableName,
      page: page,
      limit: limit,
      count: result.rows.length
    });

    return result.rows;
  }

  /**
   * Contar registros
   */
  async count(client = null) {
    const query = `SELECT COUNT(*) as total FROM ${this.tableName}`;
    const result = await this.executeQuery(query, [], client);
    return parseInt(result.rows[0].total);
  }

  /**
   * Log de operaciones
   */
  logOperation(action, data = {}) {
    logSecurityEvent(action, {
      ip: 'REPOSITORY',
      route: this.tableName,
      message: `${action} en ${this.tableName}`,
      data: {
        repository: this.constructor.name,
        ...data,
        timestamp: new Date().toISOString()
      }
    });
  }
}

module.exports = BaseRepository;