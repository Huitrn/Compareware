/**
 * Sistema Avanzado de Detección de Inyecciones SQL
 * Proyecto: Compareware - Desarrollo Backend
 * Propósito: Detectar y analizar patrones avanzados de ataques SQL injection
 */

const { sqlValidator } = require('./SQLSecurityValidator');

class AdvancedSQLDetector {
  constructor() {
    // Patrones de ataque específicos por técnica
    this.attackPatterns = {
      // 1. Union-based SQL Injection
      unionBased: [
        /union\s+(all\s+)?select/gi,
        /\d+\s+union\s+select/gi,
        /null.*union.*select/gi,
        /union\s+select\s+null/gi,
        /'.*union.*select.*'/gi
      ],

      // 2. Boolean-based Blind SQL Injection
      booleanBlind: [
        /(and|or)\s+\d+\s*=\s*\d+/gi,
        /(and|or)\s+'\w*'\s*=\s*'\w*'/gi,
        /(and|or)\s+\d+\s*<>\s*\d+/gi,
        /(and|or)\s+length\s*\(/gi,
        /(and|or)\s+substring\s*\(/gi,
        /(and|or)\s+ascii\s*\(/gi
      ],

      // 3. Time-based Blind SQL Injection
      timeBlind: [
        /sleep\s*\(\s*\d+\s*\)/gi,
        /waitfor\s+delay\s+'/gi,
        /benchmark\s*\(/gi,
        /pg_sleep\s*\(/gi,
        /dbms_lock\.sleep\s*\(/gi
      ],

      // 4. Error-based SQL Injection
      errorBased: [
        /extractvalue\s*\(/gi,
        /updatexml\s*\(/gi,
        /exp\s*\(\s*~\s*\(/gi,
        /floor\s*\(.*rand\s*\(/gi,
        /geometrycollection\s*\(/gi,
        /multipoint\s*\(/gi,
        /polygon\s*\(/gi,
        /multipolygon\s*\(/gi,
        /linestring\s*\(/gi,
        /multilinestring\s*\(/gi
      ],

      // 5. Stacked Queries
      stackedQueries: [
        /;\s*(select|insert|update|delete|drop|create|alter)/gi,
        /;\s*exec\s*\(/gi,
        /;\s*declare\s+/gi,
        /;\s*set\s+/gi
      ],

      // 6. Information Schema Attacks
      infoSchema: [
        /information_schema\.(tables|columns|schemata)/gi,
        /sys\.(tables|objects|databases)/gi,
        /mysql\.(user|db)/gi,
        /pg_catalog\.(pg_tables|pg_class)/gi,
        /all_tables/gi,
        /user_tables/gi
      ],

      // 7. Function-based Attacks
      functionBased: [
        /load_file\s*\(/gi,
        /into\s+outfile/gi,
        /dumpfile/gi,
        /system\s*\(/gi,
        /@\@version/gi,
        /@\@datadir/gi,
        /user\s*\(\s*\)/gi,
        /database\s*\(\s*\)/gi,
        /version\s*\(\s*\)/gi
      ],

      // 8. Encoding/Bypass Techniques
      encodingBypass: [
        /%[0-9a-f]{2}/gi,
        /\\x[0-9a-f]{2}/gi,
        /\\u[0-9a-f]{4}/gi,
        /char\s*\(\s*\d+\s*\)/gi,
        /ascii\s*\(\s*\d+\s*\)/gi,
        /0x[0-9a-f]+/gi
      ],

      // 9. Comment-based Bypass
      commentBypass: [
        /\/\*.*\*\//g,
        /\/\*\!\d+.*\*\//g,
        /--.*$/gm,
        /#.*$/gm,
        /\/\*.*union.*\*\//gi,
        /\/\*.*select.*\*\//gi
      ],

      // 10. Second-order SQL Injection indicators
      secondOrder: [
        /insert.*select/gi,
        /update.*select/gi,
        /delete.*where.*select/gi
      ]
    };

    // Indicadores de severidad por técnica
    this.severityLevels = {
      unionBased: 9,
      booleanBlind: 7,
      timeBlind: 8,
      errorBased: 8,
      stackedQueries: 10,
      infoSchema: 9,
      functionBased: 10,
      encodingBypass: 6,
      commentBypass: 7,
      secondOrder: 8
    };

    // Contador de ataques por IP
    this.attackCounter = new Map();
    
    // Cache de patrones ya analizados
    this.analysisCache = new Map();
  }

  /**
   * Análisis completo de entrada para detectar inyecciones SQL
   */
  analyzeInput(input, context = {}) {
    const analysisId = this.generateAnalysisId(input, context);
    
    // Verificar cache
    if (this.analysisCache.has(analysisId)) {
      return this.analysisCache.get(analysisId);
    }

    const analysis = {
      input: input,
      context: context,
      timestamp: new Date().toISOString(),
      detectedAttacks: [],
      riskScore: 0,
      severity: 'LOW',
      isBlocked: false,
      recommendations: []
    };

    // Analizar cada tipo de ataque
    for (const [attackType, patterns] of Object.entries(this.attackPatterns)) {
      const detections = this.detectAttackType(input, attackType, patterns);
      
      if (detections.length > 0) {
        analysis.detectedAttacks.push({
          type: attackType,
          detections: detections,
          severity: this.severityLevels[attackType] || 5,
          description: this.getAttackDescription(attackType)
        });

        analysis.riskScore += this.severityLevels[attackType] || 5;
      }
    }

    // Análisis adicional de contexto
    this.analyzeContext(analysis, context);

    // Determinar severidad final
    this.calculateFinalSeverity(analysis);

    // Generar recomendaciones
    this.generateRecommendations(analysis);

    // Guardar en cache
    this.analysisCache.set(analysisId, analysis);

    return analysis;
  }

  /**
   * Detectar un tipo específico de ataque
   */
  detectAttackType(input, attackType, patterns) {
    const detections = [];
    const inputStr = String(input);

    for (let i = 0; i < patterns.length; i++) {
      const pattern = patterns[i];
      const matches = inputStr.match(pattern);
      
      if (matches) {
        detections.push({
          patternIndex: i,
          matches: matches,
          positions: this.getMatchPositions(inputStr, pattern),
          confidence: this.calculateConfidence(matches, pattern)
        });
      }
    }

    return detections;
  }

  /**
   * Obtener posiciones de las coincidencias
   */
  getMatchPositions(input, pattern) {
    const positions = [];
    let match;
    const regex = new RegExp(pattern.source, pattern.flags);
    
    while ((match = regex.exec(input)) !== null) {
      positions.push({
        start: match.index,
        end: match.index + match[0].length,
        match: match[0]
      });
      
      // Evitar bucles infinitos
      if (regex.lastIndex === match.index) {
        regex.lastIndex++;
      }
    }

    return positions;
  }

  /**
   * Calcular confianza de la detección
   */
  calculateConfidence(matches, pattern) {
    // Factores que aumentan la confianza:
    // - Múltiples coincidencias
    // - Longitud de la coincidencia
    // - Contexto de la coincidencia

    let confidence = 0.5; // Base

    // Más coincidencias = mayor confianza
    confidence += Math.min(matches.length * 0.1, 0.3);

    // Coincidencias más largas = mayor confianza
    const avgLength = matches.reduce((sum, m) => sum + m.length, 0) / matches.length;
    confidence += Math.min(avgLength / 20, 0.2);

    return Math.min(confidence, 1.0);
  }

  /**
   * Análisis contextual adicional
   */
  analyzeContext(analysis, context) {
    const input = analysis.input;
    
    // 1. Análisis de longitud sospechosa
    if (input.length > 1000) {
      analysis.detectedAttacks.push({
        type: 'SUSPICIOUS_LENGTH',
        severity: 4,
        description: 'Input excesivamente largo puede indicar ataque'
      });
      analysis.riskScore += 2;
    }

    // 2. Análisis de caracteres especiales
    const specialCharCount = (input.match(/['"`;(){}]/g) || []).length;
    if (specialCharCount > 5) {
      analysis.detectedAttacks.push({
        type: 'EXCESSIVE_SPECIAL_CHARS',
        severity: 3,
        description: 'Demasiados caracteres especiales SQL'
      });
      analysis.riskScore += 1;
    }

    // 3. Análisis de palabras clave SQL
    const sqlKeywords = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'UNION', 'DROP'];
    const keywordCount = sqlKeywords.reduce((count, keyword) => {
      return count + (input.toUpperCase().split(keyword).length - 1);
    }, 0);

    if (keywordCount > 3) {
      analysis.detectedAttacks.push({
        type: 'MULTIPLE_SQL_KEYWORDS',
        severity: 6,
        description: 'Múltiples palabras clave SQL detectadas'
      });
      analysis.riskScore += 3;
    }

    // 4. Análisis basado en el contexto esperado
    if (context.expectedType === 'numeric' && !/^\d+$/.test(input)) {
      analysis.riskScore += 2;
    }

    if (context.expectedType === 'email' && !input.includes('@')) {
      analysis.riskScore += 1;
    }
  }

  /**
   * Calcular severidad final
   */
  calculateFinalSeverity(analysis) {
    if (analysis.riskScore >= 15) {
      analysis.severity = 'CRITICAL';
      analysis.isBlocked = true;
    } else if (analysis.riskScore >= 10) {
      analysis.severity = 'HIGH';
      analysis.isBlocked = true;
    } else if (analysis.riskScore >= 5) {
      analysis.severity = 'MEDIUM';
    } else if (analysis.riskScore >= 2) {
      analysis.severity = 'LOW';
    }
  }

  /**
   * Generar recomendaciones de seguridad
   */
  generateRecommendations(analysis) {
    const recommendations = [];

    if (analysis.detectedAttacks.some(a => a.type === 'unionBased')) {
      recommendations.push('Implementar whitelist de columnas permitidas');
      recommendations.push('Usar prepared statements con validación estricta');
    }

    if (analysis.detectedAttacks.some(a => a.type === 'timeBlind')) {
      recommendations.push('Implementar timeout en consultas de base de datos');
      recommendations.push('Monitorear tiempos de respuesta anómalos');
    }

    if (analysis.detectedAttacks.some(a => a.type === 'infoSchema')) {
      recommendations.push('Restringir acceso a tablas del sistema');
      recommendations.push('Usar usuario de BD con privilegios mínimos');
    }

    if (analysis.detectedAttacks.some(a => a.type === 'encodingBypass')) {
      recommendations.push('Implementar decodificación y validación de entrada');
      recommendations.push('Normalizar encoding antes de validar');
    }

    analysis.recommendations = recommendations;
  }

  /**
   * Obtener descripción del tipo de ataque
   */
  getAttackDescription(attackType) {
    const descriptions = {
      unionBased: 'Inyección SQL basada en UNION para extraer datos',
      booleanBlind: 'Inyección SQL ciega basada en condiciones booleanas',
      timeBlind: 'Inyección SQL ciega basada en retardos de tiempo',
      errorBased: 'Inyección SQL que aprovecha mensajes de error',
      stackedQueries: 'Ejecución de múltiples consultas SQL',
      infoSchema: 'Acceso a información del esquema de base de datos',
      functionBased: 'Uso de funciones del sistema para obtener información',
      encodingBypass: 'Uso de encoding para evadir validaciones',
      commentBypass: 'Uso de comentarios SQL para evadir filtros',
      secondOrder: 'Inyección SQL de segundo orden'
    };

    return descriptions[attackType] || 'Técnica de inyección SQL no clasificada';
  }

  /**
   * Registrar intento de ataque por IP
   */
  registerAttackAttempt(ip, analysis) {
    if (!this.attackCounter.has(ip)) {
      this.attackCounter.set(ip, {
        attempts: 0,
        firstAttempt: new Date(),
        lastAttempt: new Date(),
        severestAttack: 'LOW',
        attackTypes: new Set()
      });
    }

    const record = this.attackCounter.get(ip);
    record.attempts++;
    record.lastAttempt = new Date();
    
    if (this.compareSeverity(analysis.severity, record.severestAttack) > 0) {
      record.severestAttack = analysis.severity;
    }

    analysis.detectedAttacks.forEach(attack => {
      record.attackTypes.add(attack.type);
    });

    return record;
  }

  /**
   * Comparar niveles de severidad
   */
  compareSeverity(sev1, sev2) {
    const levels = { 'LOW': 1, 'MEDIUM': 2, 'HIGH': 3, 'CRITICAL': 4 };
    return levels[sev1] - levels[sev2];
  }

  /**
   * Generar ID único para análisis
   */
  generateAnalysisId(input, context) {
    const crypto = require('crypto');
    const data = JSON.stringify({ input, context });
    return crypto.createHash('md5').update(data).digest('hex');
  }

  /**
   * Obtener estadísticas de ataques
   */
  getAttackStatistics() {
    const stats = {
      totalIPs: this.attackCounter.size,
      totalAttempts: 0,
      severityDistribution: { LOW: 0, MEDIUM: 0, HIGH: 0, CRITICAL: 0 },
      topAttackTypes: {},
      recentAttacks: []
    };

    for (const [ip, record] of this.attackCounter) {
      stats.totalAttempts += record.attempts;
      stats.severityDistribution[record.severestAttack]++;
      
      record.attackTypes.forEach(type => {
        stats.topAttackTypes[type] = (stats.topAttackTypes[type] || 0) + 1;
      });

      // Ataques recientes (última hora)
      const hourAgo = new Date(Date.now() - 3600000);
      if (record.lastAttempt > hourAgo) {
        stats.recentAttacks.push({
          ip: ip,
          attempts: record.attempts,
          lastAttempt: record.lastAttempt,
          severity: record.severestAttack
        });
      }
    }

    return stats;
  }

  /**
   * Limpiar cache y estadísticas antiguas
   */
  cleanup() {
    const hourAgo = new Date(Date.now() - 3600000);
    
    // Limpiar ataques antiguos
    for (const [ip, record] of this.attackCounter) {
      if (record.lastAttempt < hourAgo) {
        this.attackCounter.delete(ip);
      }
    }

    // Limpiar cache si es muy grande
    if (this.analysisCache.size > 1000) {
      this.analysisCache.clear();
    }
  }
}

// Instancia singleton
const advancedDetector = new AdvancedSQLDetector();

module.exports = {
  AdvancedSQLDetector,
  advancedDetector
};