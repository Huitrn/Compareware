const validator = require('validator');

/**
 * Sistema de Validación y Sanitización contra Inyecciones SQL
 * Proyecto: Compareware - Desarrollo Backend
 * Propósito: Proteger contra ataques de inyección SQL y validar inputs
 */

class SQLSecurityValidator {
  constructor() {
    // Patrones avanzados de SQL Injection (incluyendo técnicas modernas de bypass)
    this.sqlInjectionPatterns = [
      // Comandos SQL básicos
      /(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b|\bCREATE\b|\bALTER\b)/gi,
      
      // Comentarios y terminadores
      /(--|\/\*|\*\/|;|\||&|#)/g,
      
      // Inyecciones booleanas clásicas
      /(\bOR\b|\bAND\b)\s+(\d+\s*=\s*\d+|\'\w*\'\s*=\s*\'\w*\')/gi,
      
      // Procedimientos almacenados peligrosos
      /(\bEXEC\b|\bEXECUTE\b|\bSP_\w+\b|\bXP_\w+\b)/gi,
      
      // XSS y scripts
      /(script|javascript|vbscript|onload|onerror|onclick)/gi,
      
      // Caracteres de encoding
      /(\<|\>|&lt|&gt|&amp|&#)/g,
      
      // Bases de datos del sistema
      /(information_schema|sys\.|master\.|pg_|mysql\.|performance_schema)/gi,
      
      // Funciones de conversión
      /(char|nchar|varchar|nvarchar)\s*\(\s*\d+\s*\)/gi,
      
      // NUEVAS PROTECCIONES AVANZADAS:
      
      // Time-based blind SQL injection
      /(sleep\s*\(|waitfor\s+delay|benchmark\s*\(|pg_sleep\s*\()/gi,
      
      // Inyecciones con UNION bypass
      /(\bunion\b.*\bselect\b|\bselect\b.*\bunion\b)/gi,
      
      // Error-based injection
      /(extractvalue\s*\(|updatexml\s*\(|exp\s*\(|floor\s*\(.*rand\s*\()/gi,
      
      // Bypass con comentarios inline
      /\/\*.*\*\/|\/\*\!\d+/g,
      
      // Inyecciones con concatenación
      /(concat\s*\(|group_concat\s*\(|string_agg\s*\()/gi,
      
      // Bypass con espacios alternativos
      /[\t\n\r\f\v]+/g,
      
      // Inyecciones con hexadecimal/unicode
      /(0x[0-9a-f]+|\\\u[0-9a-f]{4}|\\\x[0-9a-f]{2})/gi,
      
      // Inyecciones con funciones de sistema
      /(load_file\s*\(|into\s+outfile|dumpfile|system\s*\()/gi,
      
      // Bypass con case manipulation
      /([a-z])(\1+)/gi, // Detectar repetición de caracteres (posible bypass)
      
      // Inyecciones con operadores matemáticos como bypass
      /(\+|\-|\*|\/|\%)\s*\d+\s*(\+|\-|\*|\/|\%)/g,
      
      // Detección de encoding bypass
      /%[0-9a-f]{2}/gi,
      
      // Stacked queries
      /;\s*(select|insert|update|delete|drop|create|alter)/gi
    ];

    // Caracteres peligrosos específicos (ampliados)
    this.dangerousChars = [
      "'", '"', ';', '(', ')', '{', '}', '[', ']',
      '--', '/*', '*/', '||', '&&', '|', '&',
      '<', '>', '=', '!=', '<>', '<=', '>=',
      '\\', '\n', '\r', '\t', '\0', '%', '_',
      '`', '~', '^', '#', '@', '$'
    ];

    // Palabras clave SQL peligrosas (blacklist expandida)
    this.sqlKeywords = [
      'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER',
      'UNION', 'JOIN', 'WHERE', 'FROM', 'INTO', 'VALUES', 'SET',
      'EXEC', 'EXECUTE', 'DECLARE', 'CAST', 'CONVERT', 'SUBSTRING',
      'ADMIN', 'BACKUP', 'RESTORE', 'SHUTDOWN', 'LOAD', 'DUMP',
      'OUTFILE', 'DUMPFILE', 'LOAD_FILE', 'INFORMATION_SCHEMA',
      'PERFORMANCE_SCHEMA', 'MYSQL', 'SYS', 'MASTER', 'MSDB', 'TEMPDB'
    ];

    // Funciones peligrosas específicas por DBMS
    this.dangerousFunctions = {
      mysql: ['load_file', 'into outfile', 'sleep', 'benchmark'],
      postgresql: ['pg_sleep', 'pg_read_file', 'copy'],
      mssql: ['waitfor delay', 'sp_', 'xp_', 'openrowset'],
      oracle: ['dbms_', 'utl_file', 'sys.'],
      sqlite: ['load_extension', 'attach']
    };

    // Límites de longitud por tipo de campo
    this.fieldLimits = {
      id: 10,           // IDs numéricos
      name: 100,        // Nombres
      email: 254,       // Emails (RFC estándar)
      password: 128,    // Contraseñas hasheadas
      address: 500,     // Direcciones
      description: 2000, // Descripciones
      notes: 1000,      // Notas
      phone: 20,        // Teléfonos
      sku: 50,          // Códigos de producto
      default: 255      // Por defecto
    };
  }

  /**
   * Validar y sanitizar entrada contra SQL Injection
   */
  validateAndSanitize(input, fieldType = 'default', options = {}) {
    const result = {
      isValid: true,
      sanitized: input,
      errors: [],
      warnings: []
    };

    // 1. Verificar que no sea null/undefined
    if (input === null || input === undefined) {
      if (options.required) {
        result.isValid = false;
        result.errors.push(`Campo requerido no puede ser nulo`);
      }
      return result;
    }

    // 2. Convertir a string para validación
    const inputStr = String(input).trim();

    // 3. Verificar longitud
    const maxLength = this.fieldLimits[fieldType] || this.fieldLimits.default;
    if (inputStr.length > maxLength) {
      result.isValid = false;
      result.errors.push(`Longitud máxima excedida: ${inputStr.length}/${maxLength}`);
      return result;
    }

    // 4. Detectar patrones de SQL Injection
    for (const pattern of this.sqlInjectionPatterns) {
      if (pattern.test(inputStr)) {
        result.isValid = false;
        result.errors.push(`Patrón SQL peligroso detectado: ${pattern.source}`);
      }
    }

    // 5. Verificar caracteres peligrosos
    const foundDangerousChars = this.dangerousChars.filter(char => 
      inputStr.includes(char)
    );
    
    if (foundDangerousChars.length > 0 && !options.allowSpecialChars) {
      result.warnings.push(`Caracteres peligrosos encontrados: ${foundDangerousChars.join(', ')}`);
      
      // En modo estricto, rechazar
      if (options.strictMode) {
        result.isValid = false;
        result.errors.push(`Caracteres no permitidos en modo estricto: ${foundDangerousChars.join(', ')}`);
      }
    }

    // 6. Sanitización específica por tipo
    result.sanitized = this.sanitizeByType(inputStr, fieldType);

    return result;
  }

  /**
   * Sanitización específica por tipo de campo
   */
  sanitizeByType(input, fieldType) {
    switch (fieldType) {
      case 'email':
        return validator.normalizeEmail(input, {
          all_lowercase: true,
          gmail_remove_dots: false
        });

      case 'id':
        // Solo números enteros
        const numberId = parseInt(input, 10);
        return isNaN(numberId) ? null : numberId;

      case 'name':
        // Solo letras, números, espacios y algunos caracteres especiales
        return input.replace(/[^a-zA-ZÀ-ÿ0-9\s\-\.]/g, '').trim();

      case 'phone':
        // Solo números, +, -, espacios y paréntesis
        return input.replace(/[^\d\+\-\s\(\)]/g, '');

      case 'address':
        // Letras, números, espacios y caracteres comunes de direcciones
        return input.replace(/[^a-zA-ZÀ-ÿ0-9\s\-\.\,\#]/g, '').trim();

      case 'sku':
        // Códigos de producto: letras, números, guiones
        return input.replace(/[^a-zA-Z0-9\-]/g, '').toUpperCase();

      case 'description':
      case 'notes':
        // Texto libre pero sin caracteres SQL peligrosos
        return input
          .replace(/['"`;(){}]/g, '') // Quitar caracteres SQL peligrosos
          .replace(/--/g, '')        // Quitar comentarios SQL
          .replace(/\/\*|\*\//g, '') // Quitar comentarios de bloque
          .trim();

      default:
        // Sanitización básica
        return input
          .replace(/['"`;]/g, '')    // Caracteres básicos peligrosos
          .trim();
    }
  }

  /**
   * Validar objeto completo con múltiples campos
   */
  validateObject(data, schema = {}) {
    const results = {
      isValid: true,
      sanitized: {},
      errors: [],
      warnings: []
    };

    for (const [field, value] of Object.entries(data)) {
      const fieldSchema = schema[field] || { type: 'default' };
      
      const validation = this.validateAndSanitize(
        value, 
        fieldSchema.type, 
        fieldSchema.options || {}
      );

      results.sanitized[field] = validation.sanitized;

      if (!validation.isValid) {
        results.isValid = false;
        results.errors.push(`${field}: ${validation.errors.join(', ')}`);
      }

      if (validation.warnings.length > 0) {
        results.warnings.push(`${field}: ${validation.warnings.join(', ')}`);
      }
    }

    return results;
  }

  /**
   * Validar query SQL construida para detectar inyecciones
   */
  validateQuery(query, params = []) {
    const result = {
      isValid: true,
      errors: [],
      warnings: []
    };

    // 1. Verificar que use parámetros preparados
    const paramCount = (query.match(/\$\d+/g) || []).length;
    if (paramCount !== params.length) {
      result.isValid = false;
      result.errors.push(`Mismatch de parámetros: esperados ${paramCount}, recibidos ${params.length}`);
    }

    // 2. Verificar que no haya concatenación directa sospechosa
    if (query.includes("' +") || query.includes('" +') || query.includes("+ '") || query.includes('+ "')) {
      result.warnings.push('Posible concatenación de strings en query');
    }

    // 3. Verificar palabras clave peligrosas en contexto incorrecto
    const dangerousInQuery = [
      'DROP TABLE', 'DROP DATABASE', 'TRUNCATE', 'ALTER TABLE',
      'GRANT', 'REVOKE', 'CREATE USER', 'DROP USER',
      'LOAD_FILE', 'INTO OUTFILE', 'DUMPFILE'
    ];

    for (const dangerous of dangerousInQuery) {
      if (query.toUpperCase().includes(dangerous)) {
        result.warnings.push(`Comando potencialmente peligroso: ${dangerous}`);
      }
    }

    return result;
  }

  /**
   * Crear log de intento de inyección SQL
   */
  logSQLInjectionAttempt(input, source, userInfo = {}) {
    const logEntry = {
      timestamp: new Date().toISOString(),
      type: 'SQL_INJECTION_ATTEMPT',
      input: input,
      source: source,
      userInfo: userInfo,
      severity: 'HIGH',
      blocked: true
    };

    // Log en consola (en producción, enviar a sistema de logs)
    console.error('🚨 INTENTO DE INYECCIÓN SQL DETECTADO:', JSON.stringify(logEntry, null, 2));

    // También loguear en el sistema de auditoría existente
    const { logSecurityEvent } = require('../middlewares/logging');
    logSecurityEvent('SQL_INJECTION_ATTEMPT', {
      ip: userInfo.ip || 'UNKNOWN',
      route: source,
      message: 'Intento de inyección SQL bloqueado',
      data: logEntry
    });

    return logEntry;
  }

  /**
   * Detección avanzada de bypass techniques
   */
  detectBypassTechniques(input) {
    const bypasses = {
      detected: [],
      severity: 'LOW'
    };

    const inputStr = String(input).toLowerCase();

    // 1. Bypass con encoding
    if (/%[0-9a-f]{2}/i.test(input)) {
      bypasses.detected.push({
        type: 'URL_ENCODING_BYPASS',
        pattern: 'URL encoding detected',
        severity: 'MEDIUM'
      });
    }

    // 2. Bypass con Unicode
    if (/\\u[0-9a-f]{4}/i.test(input)) {
      bypasses.detected.push({
        type: 'UNICODE_BYPASS',
        pattern: 'Unicode encoding detected',
        severity: 'HIGH'
      });
    }

    // 3. Bypass con comentarios
    if (/\/\*.*\*\/|--|\#/.test(input)) {
      bypasses.detected.push({
        type: 'COMMENT_BYPASS',
        pattern: 'SQL comments detected',
        severity: 'HIGH'
      });
    }

    // 4. Bypass con case variations
    if (/[a-z][A-Z]|[A-Z][a-z]/.test(input) && this.sqlKeywords.some(kw => 
      inputStr.includes(kw.toLowerCase()))) {
      bypasses.detected.push({
        type: 'CASE_VARIATION_BYPASS',
        pattern: 'Mixed case SQL keywords',
        severity: 'MEDIUM'
      });
    }

    // 5. Bypass con espacios alternativos
    if (/[\t\n\r\f\v]/.test(input)) {
      bypasses.detected.push({
        type: 'WHITESPACE_BYPASS',
        pattern: 'Alternative whitespace characters',
        severity: 'MEDIUM'
      });
    }

    // 6. Time-based injection indicators
    if (/(sleep|waitfor|benchmark|pg_sleep)\s*\(/i.test(input)) {
      bypasses.detected.push({
        type: 'TIME_BASED_INJECTION',
        pattern: 'Time delay functions detected',
        severity: 'HIGH'
      });
    }

    // 7. Concatenation bypass
    if (/\+|concat\s*\(/i.test(input)) {
      bypasses.detected.push({
        type: 'CONCATENATION_BYPASS',
        pattern: 'String concatenation detected',
        severity: 'MEDIUM'
      });
    }

    // Determinar severidad máxima
    if (bypasses.detected.some(b => b.severity === 'HIGH')) {
      bypasses.severity = 'HIGH';
    } else if (bypasses.detected.some(b => b.severity === 'MEDIUM')) {
      bypasses.severity = 'MEDIUM';
    }

    return bypasses;
  }

  /**
   * Análisis contextual de la query
   */
  analyzeQueryContext(input, expectedContext = 'UNKNOWN') {
    const analysis = {
      isValid: true,
      context: expectedContext,
      anomalies: [],
      riskScore: 0
    };

    const inputStr = String(input).toLowerCase().trim();

    // 1. Verificar si contiene múltiples statements
    const statements = inputStr.split(';').filter(s => s.trim().length > 0);
    if (statements.length > 1) {
      analysis.anomalies.push({
        type: 'MULTIPLE_STATEMENTS',
        message: 'Multiple SQL statements detected',
        riskScore: 8
      });
    }

    // 2. Verificar UNION injection
    if (inputStr.includes('union') && inputStr.includes('select')) {
      analysis.anomalies.push({
        type: 'UNION_INJECTION',
        message: 'UNION-based injection pattern',
        riskScore: 9
      });
    }

    // 3. Verificar error-based injection
    if (/(extractvalue|updatexml|exp|floor.*rand)/i.test(input)) {
      analysis.anomalies.push({
        type: 'ERROR_BASED_INJECTION',
        message: 'Error-based injection functions',
        riskScore: 8
      });
    }

    // 4. Verificar boolean-based injection
    if (/(or|and)\s+\d+\s*=\s*\d+/i.test(input)) {
      analysis.anomalies.push({
        type: 'BOOLEAN_INJECTION',
        message: 'Boolean-based injection pattern',
        riskScore: 7
      });
    }

    // 5. Verificar acceso a tablas del sistema
    if (/(information_schema|sys\.|mysql\.|pg_catalog)/i.test(input)) {
      analysis.anomalies.push({
        type: 'SYSTEM_TABLE_ACCESS',
        message: 'System table access attempt',
        riskScore: 9
      });
    }

    // Calcular riesgo total
    analysis.riskScore = analysis.anomalies.reduce((sum, anomaly) => 
      sum + anomaly.riskScore, 0);
    
    // Determinar si es válido
    analysis.isValid = analysis.riskScore < 5;

    return analysis;
  }

  /**
   * Validación mejorada con análisis contextual
   */
  validateAdvanced(input, context = {}) {
    const result = {
      isValid: true,
      sanitized: input,
      errors: [],
      warnings: [],
      riskScore: 0,
      bypasses: [],
      analysis: null
    };

    // Validación básica
    const basicValidation = this.validateAndSanitize(
      input, 
      context.fieldType || 'default', 
      context.options || {}
    );

    Object.assign(result, basicValidation);

    // Análisis de bypasses
    const bypassAnalysis = this.detectBypassTechniques(input);
    result.bypasses = bypassAnalysis.detected;
    
    if (bypassAnalysis.severity === 'HIGH') {
      result.isValid = false;
      result.errors.push('Técnica de bypass de alto riesgo detectada');
      result.riskScore += 10;
    } else if (bypassAnalysis.severity === 'MEDIUM') {
      result.warnings.push('Técnica de bypass potencial detectada');
      result.riskScore += 5;
    }

    // Análisis contextual
    result.analysis = this.analyzeQueryContext(input, context.expectedContext);
    if (!result.analysis.isValid) {
      result.isValid = false;
      result.errors.push('Análisis contextual falló');
      result.riskScore += result.analysis.riskScore;
    }

    return result;
  }

  /**
   * Generar reporte de seguridad mejorado
   */
  generateSecurityReport(validations = []) {
    const report = {
      timestamp: new Date().toISOString(),
      totalValidations: validations.length,
      passed: validations.filter(v => v.isValid).length,
      failed: validations.filter(v => !v.isValid).length,
      warnings: validations.reduce((acc, v) => acc + v.warnings.length, 0),
      totalRiskScore: validations.reduce((acc, v) => acc + (v.riskScore || 0), 0),
      avgRiskScore: 0,
      riskLevel: 'LOW',
      bypassAttempts: validations.reduce((acc, v) => acc + (v.bypasses?.length || 0), 0),
      details: validations
    };

    // Calcular promedio de riesgo
    report.avgRiskScore = report.totalValidations > 0 ? 
      report.totalRiskScore / report.totalValidations : 0;

    // Calcular nivel de riesgo mejorado
    const failureRate = report.failed / report.totalValidations;
    if (failureRate > 0.1 || report.avgRiskScore > 7) {
      report.riskLevel = 'CRITICAL';
    } else if (failureRate > 0.05 || report.avgRiskScore > 4) {
      report.riskLevel = 'HIGH';
    } else if (report.avgRiskScore > 2) {
      report.riskLevel = 'MEDIUM';
    }

    return report;
  }
}

// Instancia singleton para usar en toda la aplicación
const sqlValidator = new SQLSecurityValidator();

module.exports = {
  SQLSecurityValidator,
  sqlValidator
};