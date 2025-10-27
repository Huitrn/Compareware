# 🛡️ Guía Completa de SQL Injection - Proyecto Compareware

## ⚠️ ADVERTENCIA IMPORTANTE
Esta guía es con **fines educativos únicamente** para aprender sobre seguridad en desarrollo backend. **NUNCA** uses estas técnicas en sistemas que no te pertenezcan. El uso malicioso de estos conocimientos es **ILEGAL** y éticamente incorrecto.

## 📚 ¿Qué es SQL Injection?

SQL Injection es una vulnerabilidad de seguridad que permite a un atacante interferir con las consultas que una aplicación hace a su base de datos. Generalmente permite ver datos que normalmente no podría recuperar, como datos de otros usuarios o cualquier otro dato al que la aplicación pueda acceder.

## 🎯 Tipos de SQL Injection

### 1. In-band SQL Injection (Clásico)

#### Union-based SQL Injection
El atacante usa la declaración UNION para combinar resultados de dos o más consultas SELECT.

**Ejemplo vulnerable:**
```sql
SELECT * FROM users WHERE id = '$user_input'
```

**Payload de ataque:**
```
1' UNION SELECT username, password FROM admin_users--
```

**Query resultante:**
```sql
SELECT * FROM users WHERE id = '1' UNION SELECT username, password FROM admin_users--'
```

#### Error-based SQL Injection
El atacante aprovecha los mensajes de error de la base de datos para obtener información.

**Payload de ejemplo:**
```
1' AND EXTRACTVALUE(1, CONCAT(0x7e, (SELECT database()), 0x7e))--
```

### 2. Inferential SQL Injection (Blind)

#### Boolean-based Blind SQL Injection
El atacante envía consultas que fuerzan a la aplicación a devolver diferentes resultados según si la consulta devuelve TRUE o FALSE.

**Payload de ejemplo:**
```
1' AND LENGTH(database()) > 5--
```

#### Time-based Blind SQL Injection
El atacante usa funciones de tiempo para determinar si una consulta es verdadera o falsa basándose en el tiempo de respuesta.

**Payload de ejemplo:**
```
1' AND IF(1=1, SLEEP(5), 0)--
```

### 3. Out-of-band SQL Injection
Ocurre cuando el atacante no puede usar el mismo canal para lanzar el ataque y recopilar resultados.

## 🔧 Técnicas de Bypass Comunes

### 1. Bypass de Filtros Básicos

**Espacios alternativos:**
```sql
SELECT/**/username/**/FROM/**/users
SELECT+username+FROM+users
SELECT%20username%20FROM%20users
```

**Comentarios inline:**
```sql
SEL/*comment*/ECT username FROM users
```

**Case variation:**
```sql
SeLeCt username FrOm users
```

### 2. Encoding Bypass

**URL Encoding:**
```
%27 = '
%20 = space
%2D%2D = --
```

**Unicode Encoding:**
```
\u0027 = '
\u0020 = space
```

**Hex Encoding:**
```sql
SELECT 0x61646D696E -- 'admin' en hexadecimal
```

### 3. Function-based Bypass

**Usando CHAR():**
```sql
SELECT CHAR(97,100,109,105,110) -- 'admin'
```

**Usando CONCAT():**
```sql
SELECT CONCAT('ad','min')
```

## 🛡️ Medidas de Protección Implementadas

### 1. Prepared Statements (Parámetros Preparados)

**❌ Código vulnerable:**
```javascript
const query = `SELECT * FROM users WHERE id = '${userId}'`;
db.query(query);
```

**✅ Código seguro:**
```javascript
const query = 'SELECT * FROM users WHERE id = $1';
db.query(query, [userId]);
```

### 2. Validación y Sanitización de Entrada

```javascript
// Nuestro sistema de validación
const validation = sqlValidator.validateAdvanced(userInput, {
  fieldType: 'id',
  expectedContext: 'WHERE_CLAUSE'
});

if (!validation.isValid) {
  throw new Error('Input no válido');
}
```

### 3. Whitelist de Caracteres Permitidos

```javascript
// Solo permitir caracteres alfanuméricos para IDs
const sanitizedId = input.replace(/[^a-zA-Z0-9]/g, '');
```

### 4. Principio de Menor Privilegio

- Usuario de BD con permisos mínimos necesarios
- No usar cuenta 'root' o 'sa' para la aplicación
- Revocar permisos innecesarios (DROP, CREATE, ALTER)

### 5. Web Application Firewall (WAF)

Nuestro WAF detecta y bloquea:
- Patrones SQL maliciosos
- Técnicas de bypass
- Ataques de fuerza bruta
- User-Agents sospechosos

## 🧪 Cómo Probar Tu Sistema

### 1. Testing Automatizado

```bash
# Ejecutar suite completa de tests
node security/security_tests.js full

# Test específico
node security/security_tests.js specific
```

### 2. Testing Manual

**Payloads básicos para probar:**

```sql
-- Authentication bypass
admin'--
admin'/*
' OR '1'='1'--
' OR 1=1--

-- Union-based
' UNION SELECT 1,2,3--
' UNION SELECT null,null,null--

-- Boolean blind
' AND 1=1--
' AND 1=2--

-- Time-based blind
' AND SLEEP(5)--
'; WAITFOR DELAY '00:00:05'--

-- Error-based
' AND EXTRACTVALUE(1, CONCAT(0x7e, database(), 0x7e))--
```

### 3. Herramientas de Testing

**Herramientas profesionales:**
- SQLMap (automático)
- Burp Suite (manual/automático)
- OWASP ZAP (gratuito)
- Havij (Windows)

**Ejemplo con SQLMap:**
```bash
# SOLO en tu propio sistema de testing
sqlmap -u "http://localhost:3000/api/users?id=1" --batch --level 3
```

## 📊 Interpretando Resultados de Tests

### Estados de Respuesta

**✅ Blocked (403 Forbidden):**
- Tu WAF está funcionando
- Payload fue detectado y bloqueado
- **Esto es lo que quieres ver**

**⚠️ Different Response:**
- Posible vulnerabilidad
- La aplicación responde diferente a payloads maliciosos
- Requiere investigación

**🚨 Database Error:**
- **Vulnerabilidad confirmada**
- La aplicación expone errores de BD
- **Debe corregirse inmediatamente**

**⏱️ Timeout:**
- Posible time-based blind injection
- El payload causó demora
- **Vulnerabilidad probable**

### Scores de Seguridad

- **90-100:** 🟢 Excelente protección
- **70-89:** 🟡 Buena protección, mejoras menores
- **50-69:** 🟠 Protección moderada, necesita mejoras
- **0-49:** 🔴 Protección insuficiente, CRÍTICO

## 🎓 Ejercicios Prácticos

### Ejercicio 1: Identificar Vulnerabilidades

Revisa este código y identifica las vulnerabilidades:

```javascript
app.get('/users', (req, res) => {
  const { search } = req.query;
  const query = `SELECT * FROM users WHERE name LIKE '%${search}%'`;
  
  db.query(query, (err, results) => {
    if (err) {
      return res.status(500).json({ error: err.message });
    }
    res.json(results);
  });
});
```

**Vulnerabilidades:**
1. Concatenación directa de input del usuario
2. Exposición de errores de BD
3. No hay validación de entrada
4. No hay límite de resultados

### Ejercicio 2: Crear Código Seguro

Reescribe el código anterior de forma segura:

```javascript
app.get('/users', async (req, res) => {
  try {
    const { search } = req.query;
    
    // 1. Validar entrada
    const validation = sqlValidator.validateAdvanced(search, {
      fieldType: 'name',
      options: { strictMode: true }
    });
    
    if (!validation.isValid) {
      return res.status(400).json({
        error: 'Invalid search parameter'
      });
    }
    
    // 2. Usar prepared statement
    const query = 'SELECT id, name, email FROM users WHERE name ILIKE $1 LIMIT 50';
    const searchParam = `%${validation.sanitized}%`;
    
    const results = await db.query(query, [searchParam]);
    
    res.json({
      success: true,
      data: results.rows,
      count: results.rows.length
    });
    
  } catch (error) {
    // 3. No exponer errores internos
    console.error('Database error:', error);
    res.status(500).json({
      error: 'Internal server error',
      requestId: generateRequestId()
    });
  }
});
```

## 🔍 Monitoreo y Detección

### Logs a Monitorear

1. **Intentos de inyección bloqueados**
2. **Errores de base de datos frecuentes**
3. **Consultas con tiempo de ejecución anómalo**
4. **Patrones de acceso sospechosos**
5. **User-Agents de herramientas de hacking**

### Alertas Automáticas

```javascript
// Configurar alertas para ataques frecuentes
if (attacksPerHour > 10) {
  sendAlert('High frequency SQL injection attempts detected');
}

if (errorRate > 0.05) {
  sendAlert('High database error rate - possible attack');
}
```

## 📈 Mejores Prácticas

### Para Desarrolladores

1. **Siempre usar prepared statements**
2. **Validar TODA entrada de usuario**
3. **Principio de menor privilegio**
4. **No exponer errores de BD**
5. **Implementar rate limiting**
6. **Logging comprehensivo**
7. **Testing regular de seguridad**

### Para el Proyecto

1. **Code review obligatorio**
2. **Tests de seguridad automáticos**
3. **Monitoreo en producción**
4. **Respuesta a incidentes**
5. **Actualizaciones de seguridad**

## 🚨 Plan de Respuesta a Incidentes

Si detectas una vulnerabilidad:

1. **Bloquear inmediatamente** el vector de ataque
2. **Revisar logs** para detectar explotación
3. **Patchear la vulnerabilidad**
4. **Auditar código** similar
5. **Notificar** a stakeholders
6. **Documentar** lecciones aprendidas

## 📚 Recursos Adicionales

### Documentación
- [OWASP SQL Injection Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)
- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)

### Herramientas
- [SQLMap](http://sqlmap.org/) - Herramienta automática
- [Burp Suite](https://portswigger.net/burp) - Testing manual
- [OWASP ZAP](https://www.zaproxy.org/) - Gratuito

### Labs de Práctica
- [DVWA](http://www.dvwa.co.uk/) - Damn Vulnerable Web Application
- [bWAPP](http://www.itsecgames.com/) - Buggy Web Application
- [WebGoat](https://owasp.org/www-project-webgoat/) - OWASP WebGoat

---

## 💡 Recuerda

La seguridad es un proceso continuo, no un producto. Mantente actualizado sobre nuevas amenazas y técnicas de protección. El conocimiento de estas técnicas de ataque te convierte en un mejor desarrollador defensivo.

**¡Usa este conocimiento responsablemente y contribuye a hacer del internet un lugar más seguro!** 🛡️