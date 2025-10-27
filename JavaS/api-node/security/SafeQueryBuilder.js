/**
 * Constructor de Consultas Seguras
 * Proyecto: Compareware - Desarrollo Backend
 * Propósito: Crear consultas SQL seguras con prepared statements y validación automática
 */

const { sqlValidator } = require('./SQLSecurityValidator');

class SafeQueryBuilder {
  constructor(dbConnection) {
    this.db = dbConnection;
    this.query = '';
    this.params = [];
    this.paramIndex = 1;
    this.queryType = null;
    this.tableName = null;
    this.validationRules = {};
  }

  /**
   * Iniciar una consulta SELECT
   */
  select(columns = '*') {
    this.queryType = 'SELECT';
    
    // Validar y sanitizar nombres de columnas
    if (Array.isArray(columns)) {
      const sanitizedColumns = columns.map(col => this.sanitizeIdentifier(col));
      this.query = `SELECT ${sanitizedColumns.join(', ')}`;
    } else if (columns === '*') {
      this.query = 'SELECT *';
    } else {
      const sanitizedColumn = this.sanitizeIdentifier(columns);
      this.query = `SELECT ${sanitizedColumn}`;
    }
    
    return this;
  }

  /**
   * Especificar tabla FROM
   */
  from(tableName) {
    const sanitizedTable = this.sanitizeIdentifier(tableName);
    this.tableName = sanitizedTable;
    this.query += ` FROM ${sanitizedTable}`;
    return this;
  }

  /**
   * Agregar condiciones WHERE seguras
   */
  where(column, operator = '=', value = null) {
    const sanitizedColumn = this.sanitizeIdentifier(column);
    
    // Validar operador
    const allowedOperators = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'IN', 'NOT IN'];
    if (!allowedOperators.includes(operator.toUpperCase())) {
      throw new Error(`Operador no permitido: ${operator}`);
    }

    // Validar valor
    const validation = sqlValidator.validateAdvanced(value, {
      fieldType: 'default',
      expectedContext: 'WHERE_CLAUSE'
    });

    if (!validation.isValid) {
      throw new Error(`Valor no válido en WHERE: ${validation.errors.join(', ')}`);
    }

    // Agregar condición
    const paramPlaceholder = `$${this.paramIndex++}`;
    
    if (this.query.includes('WHERE')) {
      this.query += ` AND ${sanitizedColumn} ${operator} ${paramPlaceholder}`;
    } else {
      this.query += ` WHERE ${sanitizedColumn} ${operator} ${paramPlaceholder}`;
    }
    
    this.params.push(validation.sanitized);
    return this;
  }

  /**
   * Agregar condiciones OR
   */
  orWhere(column, operator = '=', value = null) {
    const sanitizedColumn = this.sanitizeIdentifier(column);
    
    const validation = sqlValidator.validateAdvanced(value, {
      fieldType: 'default',
      expectedContext: 'WHERE_CLAUSE'
    });

    if (!validation.isValid) {
      throw new Error(`Valor no válido en OR WHERE: ${validation.errors.join(', ')}`);
    }

    const paramPlaceholder = `$${this.paramIndex++}`;
    this.query += ` OR ${sanitizedColumn} ${operator} ${paramPlaceholder}`;
    this.params.push(validation.sanitized);
    return this;
  }

  /**
   * Iniciar consulta INSERT
   */
  insertInto(tableName) {
    this.queryType = 'INSERT';
    this.tableName = this.sanitizeIdentifier(tableName);
    this.query = `INSERT INTO ${this.tableName}`;
    return this;
  }

  /**
   * Especificar valores para INSERT
   */
  values(data) {
    if (this.queryType !== 'INSERT') {
      throw new Error('VALUES solo puede usarse con INSERT');
    }

    const columns = Object.keys(data);
    const sanitizedColumns = columns.map(col => this.sanitizeIdentifier(col));
    
    // Validar todos los valores
    const validatedValues = [];
    for (const [column, value] of Object.entries(data)) {
      const validation = sqlValidator.validateAdvanced(value, {
        fieldType: this.getFieldType(column),
        expectedContext: 'INSERT_VALUE'
      });

      if (!validation.isValid) {
        throw new Error(`Valor no válido para ${column}: ${validation.errors.join(', ')}`);
      }

      validatedValues.push(validation.sanitized);
    }

    // Construir query
    const placeholders = validatedValues.map(() => `$${this.paramIndex++}`);
    this.query += ` (${sanitizedColumns.join(', ')}) VALUES (${placeholders.join(', ')})`;
    this.params.push(...validatedValues);
    
    return this;
  }

  /**
   * Iniciar consulta UPDATE
   */
  update(tableName) {
    this.queryType = 'UPDATE';
    this.tableName = this.sanitizeIdentifier(tableName);
    this.query = `UPDATE ${this.tableName} SET`;
    return this;
  }

  /**
   * Especificar valores para UPDATE
   */
  set(data) {
    if (this.queryType !== 'UPDATE') {
      throw new Error('SET solo puede usarse con UPDATE');
    }

    const setPairs = [];
    for (const [column, value] of Object.entries(data)) {
      const sanitizedColumn = this.sanitizeIdentifier(column);
      
      const validation = sqlValidator.validateAdvanced(value, {
        fieldType: this.getFieldType(column),
        expectedContext: 'UPDATE_VALUE'
      });

      if (!validation.isValid) {
        throw new Error(`Valor no válido para ${column}: ${validation.errors.join(', ')}`);
      }

      const placeholder = `$${this.paramIndex++}`;
      setPairs.push(`${sanitizedColumn} = ${placeholder}`);
      this.params.push(validation.sanitized);
    }

    this.query += ` ${setPairs.join(', ')}`;
    return this;
  }

  /**
   * Iniciar consulta DELETE
   */
  deleteFrom(tableName) {
    this.queryType = 'DELETE';
    this.tableName = this.sanitizeIdentifier(tableName);
    this.query = `DELETE FROM ${this.tableName}`;
    return this;
  }

  /**
   * Agregar LIMIT seguro
   */
  limit(count) {
    const limitValue = parseInt(count, 10);
    if (isNaN(limitValue) || limitValue < 0) {
      throw new Error('LIMIT debe ser un número entero positivo');
    }
    
    // Prevenir LIMIT excesivo (DoS protection)
    if (limitValue > 10000) {
      throw new Error('LIMIT no puede exceder 10000 registros');
    }

    this.query += ` LIMIT ${limitValue}`;
    return this;
  }

  /**
   * Agregar ORDER BY seguro
   */
  orderBy(column, direction = 'ASC') {
    const sanitizedColumn = this.sanitizeIdentifier(column);
    const sanitizedDirection = direction.toUpperCase();
    
    if (!['ASC', 'DESC'].includes(sanitizedDirection)) {
      throw new Error('Dirección de ORDER BY debe ser ASC o DESC');
    }

    this.query += ` ORDER BY ${sanitizedColumn} ${sanitizedDirection}`;
    return this;
  }

  /**
   * Sanitizar identificadores (nombres de tabla, columna, etc.)
   */
  sanitizeIdentifier(identifier) {
    // Solo permitir letras, números y guiones bajos
    const sanitized = String(identifier).replace(/[^a-zA-Z0-9_]/g, '');
    
    if (sanitized !== identifier) {
      throw new Error(`Identificador contiene caracteres no válidos: ${identifier}`);
    }

    if (sanitized.length === 0) {
      throw new Error('Identificador no puede estar vacío');
    }

    // Verificar que no sea una palabra reservada
    const sqlKeywords = [
      'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'FROM', 'WHERE', 'JOIN',
      'UNION', 'DROP', 'CREATE', 'ALTER', 'INDEX', 'TABLE', 'DATABASE'
    ];
    
    if (sqlKeywords.includes(sanitized.toUpperCase())) {
      throw new Error(`No se puede usar palabra reservada como identificador: ${identifier}`);
    }

    return sanitized;
  }

  /**
   * Determinar tipo de campo (para validación específica)
   */
  getFieldType(columnName) {
    const fieldTypeMap = {
      'id': 'id',
      'user_id': 'id',
      'categoria_id': 'id',
      'periferico_id': 'id',
      'email': 'email',
      'correo': 'email',
      'nombre': 'name',
      'name': 'name',
      'titulo': 'name',
      'telefono': 'phone',
      'phone': 'phone',
      'descripcion': 'description',
      'description': 'description',
      'comentario': 'description',
      'direccion': 'address',
      'address': 'address',
      'sku': 'sku',
      'codigo': 'sku'
    };

    return fieldTypeMap[columnName.toLowerCase()] || 'default';
  }

  /**
   * Ejecutar la consulta construida
   */
  async execute() {
    // Validación final de la consulta
    const queryValidation = sqlValidator.validateQuery(this.query, this.params);
    
    if (!queryValidation.isValid) {
      throw new Error(`Consulta no válida: ${queryValidation.errors.join(', ')}`);
    }

    try {
      console.log('🔍 Ejecutando consulta segura:', {
        query: this.query,
        params: this.params,
        type: this.queryType
      });

      // Ejecutar con el cliente de base de datos
      const result = await this.db.query(this.query, this.params);
      
      // Log de éxito
      console.log('✅ Consulta ejecutada exitosamente:', {
        rowCount: result.rowCount || result.length,
        type: this.queryType
      });

      return result;
    } catch (error) {
      console.error('❌ Error ejecutando consulta:', {
        query: this.query,
        params: this.params,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Obtener la consulta SQL generada (para debugging)
   */
  toSQL() {
    return {
      query: this.query,
      params: this.params,
      type: this.queryType,
      table: this.tableName
    };
  }

  /**
   * Validar la consulta sin ejecutarla
   */
  validate() {
    const validation = sqlValidator.validateQuery(this.query, this.params);
    
    return {
      isValid: validation.isValid,
      errors: validation.errors,
      warnings: validation.warnings,
      query: this.query,
      params: this.params
    };
  }
}

/**
 * Factory function para crear un nuevo query builder
 */
function createSafeQuery(dbConnection) {
  return new SafeQueryBuilder(dbConnection);
}

/**
 * Utilidades de consulta rápida
 */
class QuickQueries {
  constructor(dbConnection) {
    this.db = dbConnection;
  }

  /**
   * Buscar usuario por ID de forma segura
   */
  async findUserById(id) {
    return createSafeQuery(this.db)
      .select(['id', 'nombre', 'email', 'created_at'])
      .from('usuarios')
      .where('id', '=', id)
      .limit(1)
      .execute();
  }

  /**
   * Buscar usuarios por email
   */
  async findUserByEmail(email) {
    return createSafeQuery(this.db)
      .select(['id', 'nombre', 'email'])
      .from('usuarios')
      .where('email', '=', email)
      .limit(1)
      .execute();
  }

  /**
   * Crear nuevo usuario
   */
  async createUser(userData) {
    return createSafeQuery(this.db)
      .insertInto('usuarios')
      .values(userData)
      .execute();
  }

  /**
   * Actualizar usuario
   */
  async updateUser(id, userData) {
    return createSafeQuery(this.db)
      .update('usuarios')
      .set(userData)
      .where('id', '=', id)
      .execute();
  }
}

module.exports = {
  SafeQueryBuilder,
  createSafeQuery,
  QuickQueries
};