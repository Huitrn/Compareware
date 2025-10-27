# 🧪 **Manual de Pruebas de Inyección SQL**
## Guía Completa para Validar la Protección

---

## **📋 Índice de Pruebas**

### **1. 🚨 Inyecciones SQL Básicas**
### **2. 🔥 Inyecciones SQL Avanzadas**
### **3. 🛡️ Validación de Protección**
### **4. 📊 Casos de Prueba en Postman**

---

## **1. 🚨 INYECCIONES SQL BÁSICAS**

### **A) Inyección en Login - Bypass de Autenticación**

```json
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@test.com' OR '1'='1' --",
  "password": "cualquier_cosa"
}
```

**¿Qué hace?**
- Intenta hacer bypass del WHERE email = 'admin@test.com' OR '1'='1'
- El `--` comenta el resto de la query (password check)
- `'1'='1'` siempre es verdadero

### **B) Inyección UNION - Extracción de Datos**

```json
GET /api/orders/1' UNION SELECT id,email,password,null,null FROM users --
```

**¿Qué hace?**
- Intenta unir resultados de la tabla orders con datos de users
- Busca extraer emails y passwords de otros usuarios

### **C) Inyección con Comentarios**

```json
POST /api/auth/register
Content-Type: application/json

{
  "name": "Hacker'; DROP TABLE users; --",
  "email": "test@evil.com",
  "password": "123456"
}
```

**¿Qué hace?**
- Intenta ejecutar DROP TABLE para eliminar la tabla users
- Usa `';` para terminar la query original y ejecutar nueva query

---

## **2. 🔥 INYECCIONES SQL AVANZADAS**

### **A) Time-Based Blind SQL Injection**

```json
POST /api/auth/login
Content-Type: application/json

{
  "email": "test@test.com'; IF (1=1) WAITFOR DELAY '00:00:05' --",
  "password": "test"
}
```

**¿Qué hace?**
- Si la condición es verdadera, la base de datos espera 5 segundos
- Permite determinar información sin ver respuestas directas

### **B) Boolean-Based Blind Injection**

```json
GET /api/orders?search=1' AND (SELECT COUNT(*) FROM users WHERE email LIKE 'admin%') > 0 --
```

**¿Qué hace?**
- Determina si existe un usuario admin por el comportamiento de la respuesta
- No necesita ver los datos, solo el comportamiento (true/false)

### **C) Stored Procedure Injection**

```json
POST /api/orders
Content-Type: application/json

{
  "orderData": {
    "user_id": "1; EXEC xp_cmdshell('dir'); --",
    "total_amount": 100,
    "shipping_address": "Test Address",
    "payment_method": "credit_card"
  }
}
```

### **D) Second-Order SQL Injection**

```json
// Paso 1: Registrar usuario con payload
POST /api/auth/register
{
  "name": "admin'+(SELECT password FROM users WHERE id=1)+'",
  "email": "test2@test.com",
  "password": "123456"
}

// Paso 2: El payload se ejecuta cuando se usa el nombre en otra query
GET /api/users/profile
```

---

## **3. 🛡️ VALIDACIÓN DE PROTECCIÓN**

### **Respuestas Esperadas (Sistema Protegido):**

```json
// ✅ Respuesta cuando la protección funciona
{
  "success": false,
  "message": "Request contiene datos no válidos",
  "error": "INVALID_INPUT_DATA",
  "details": [
    "email: Contiene caracteres SQL peligrosos: OR, --",
    "name: Contiene palabras clave SQL no permitidas: DROP, TABLE"
  ]
}
```

### **Logs de Seguridad Generados:**

```json
{
  "timestamp": "2024-01-15T10:30:45.123Z",
  "level": "SECURITY_ALERT",
  "event": "SQL_INJECTION_BLOCKED",
  "data": {
    "ip": "192.168.1.100",
    "userAgent": "PostmanRuntime/7.32.0",
    "route": "/api/auth/login",
    "method": "POST",
    "detectedPatterns": ["' OR ", "1'='1", "--"],
    "riskLevel": "HIGH",
    "input": "admin@test.com' OR '1'='1' --"
  }
}
```

---

## **4. 📊 CASOS DE PRUEBA EN POSTMAN**

### **Colección: SQL Injection Tests**

#### **Test Case 1: Login Bypass**
```javascript
// Pre-request Script
pm.globals.set("malicious_email", "admin@test.com' OR '1'='1' --");

// Test Script
pm.test("Should block SQL injection in login", function () {
    pm.expect(pm.response.code).to.equal(400);
    pm.expect(pm.response.json().error).to.equal("INVALID_INPUT_DATA");
});
```

#### **Test Case 2: Order Creation with SQL**
```javascript
// Request Body
{
  "orderData": {
    "user_id": "{{user_id}}'; DROP TABLE orders; --",
    "total_amount": 100.00,
    "shipping_address": "123 Test St",
    "payment_method": "credit_card"
  }
}

// Test Script
pm.test("Should sanitize malicious user_id", function () {
    const response = pm.response.json();
    // Si se permite, user_id debe estar sanitizado
    if (response.success) {
        pm.expect(response.data.sanitizedInput).to.not.include("DROP");
        pm.expect(response.data.sanitizedInput).to.not.include("--");
    } else {
        // O debe ser bloqueado completamente
        pm.expect(pm.response.code).to.equal(400);
    }
});
```

#### **Test Case 3: Search Parameter Injection**
```javascript
GET /api/orders?search={{malicious_search}}

// Pre-request
pm.globals.set("malicious_search", "'; SELECT * FROM users WHERE '1'='1");

// Test
pm.test("Search parameter should be protected", function () {
    pm.expect(pm.response.code).to.be.oneOf([200, 400]);
    if (pm.response.code === 200) {
        // Verificar que no se ejecutó la inyección
        const response = pm.response.json();
        pm.expect(response.data).to.not.have.property("password");
        pm.expect(response.data).to.not.have.property("email");
    }
});
```

---

## **5. 🔍 TÉCNICAS DE TESTING MANUAL**

### **A) Caracteres de Prueba Básicos**
```
'
"
;
--
/*
*/
xp_
sp_
UNION
SELECT
DROP
INSERT
UPDATE
DELETE
OR 1=1
AND 1=1
```

### **B) Payloads de Prueba por Campo**

#### **Email Field:**
```
test@test.com' OR '1'='1
test@test.com'; DROP TABLE users; --
test@test.com' UNION SELECT password FROM users--
test@test.com'+(SELECT TOP 1 password FROM users WHERE id=1)+'@test.com
```

#### **Numeric Fields (user_id, order_id):**
```
1 OR 1=1
1; DROP TABLE orders; --
1 UNION SELECT password FROM users
1' AND (SELECT COUNT(*) FROM users) > 0 --
```

#### **Text Fields (name, address):**
```
John'; DROP TABLE users; --
John' OR '1'='1' --
John'+(SELECT password FROM users WHERE id=1)+'
<script>alert('XSS')</script>'; DROP TABLE users; --
```

---

## **6. 🎯 VALIDACIÓN COMPLETA**

### **Checklist de Protección:**

- [ ] **Inyección básica bloqueada** (`' OR '1'='1'`)
- [ ] **Comentarios SQL bloqueados** (`--`, `/*`)
- [ ] **Comandos peligrosos detectados** (`DROP`, `DELETE`, `UNION`)
- [ ] **Caracteres especiales sanitizados** (`'`, `"`, `;`)
- [ ] **Logs de seguridad generados**
- [ ] **Respuestas consistentes** (no revelan estructura de DB)
- [ ] **Time-based attacks mitigados**
- [ ] **Boolean-based attacks detectados**

### **Comando para Testing Automatizado:**
```bash
# Newman (Postman CLI) para testing automatizado
newman run SQL_Injection_Tests.postman_collection.json \
  -e production.postman_environment.json \
  --reporters cli,junit \
  --reporter-junit-export junit-report.xml
```

---

## **7. 🚨 SEÑALES DE COMPROMISO**

### **Si estos tests PASAN (malo):**
- Respuesta 200 con datos de otros usuarios
- Delays inusuales en respuestas (5+ segundos)
- Errores de base de datos expuestos
- Datos extraños en respuestas JSON

### **Si estos tests FALLAN (bueno):**
- Respuesta 400 con mensaje genérico
- Logs de seguridad generados
- Datos sanitizados en respuesta
- Tiempo de respuesta consistente

---

## **📞 Soporte**
Si encuentras vulnerabilidades, documenta:
1. Payload utilizado
2. Respuesta del servidor
3. Logs generados
4. Impacto potencial

**¡Recuerda: Estas pruebas son para VALIDAR la protección, no para atacar sistemas reales!**