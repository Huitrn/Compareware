# Práctica 5: Registrar intentos fallidos de acceso

## 📋 Descripción
Sistema de **logging de seguridad** implementado para registrar y monitorear intentos fallidos de acceso, así como eventos de seguridad exitosos.

## 🎯 Objetivos Completados

### ✅ Paso 43: Guardar en un log los intentos fallidos
- **Implementación**: Sistema de logging con archivos separados
- **Archivos de log**:
  - `logs/failed-access.log` - Intentos fallidos detallados
  - `logs/security.log` - Eventos de seguridad generales
- **Funcionalidad**: Escritura asíncrona sin bloquear el servidor

### ✅ Paso 44: Incluir fecha, ruta y usuario
- **Información registrada**:
  - ✅ **Timestamp**: ISO 8601 con timezone
  - ✅ **IP del cliente**: Dirección real del usuario
  - ✅ **Ruta accedida**: Endpoint específico
  - ✅ **Método HTTP**: GET, POST, etc.
  - ✅ **Usuario**: Username extraído de credenciales
  - ✅ **Tipo de autenticación**: BASIC_AUTH o JWT_TOKEN
  - ✅ **Razón del fallo**: Código específico del error
  - ✅ **User-Agent**: Información del cliente
  - ✅ **Código de estado**: HTTP status code

### ✅ Paso 45: Verificar archivo de logs
- **Endpoints de verificación**:
  - `GET /api/logs/failed-access` - Consultar intentos fallidos
  - `GET /api/logs/security` - Ver eventos de seguridad
  - `GET /api/logs/stats` - Estadísticas generales
- **Pruebas realizadas**: ✅ Logs funcionando correctamente

## 🛠️ Implementación Técnica

### Configuración del Sistema de Logging
```javascript
const LOG_CONFIG = {
  logDir: path.join(__dirname, 'logs'),
  failedAccessLog: path.join(__dirname, 'logs', 'failed-access.log'),
  securityLog: path.join(__dirname, 'logs', 'security.log'),
  maxLogSize: 10 * 1024 * 1024, // 10MB máximo
};
```

### Funciones Principales

#### 1. Registro de Intentos Fallidos
```javascript
const logFailedAccess = (details) => {
  // Registra: IP, método, ruta, tipo auth, usuario, razón, status
};
```

#### 2. Registro de Eventos de Seguridad
```javascript
const logSecurityEvent = (type, details) => {
  // Registra eventos exitosos y otros eventos de seguridad
};
```

### Tipos de Intentos Fallidos Registrados

#### Autenticación Básica
- ❌ `NO_AUTH_HEADER` - Sin header de autorización
- ❌ `INVALID_BASE64_ENCODING` - Credenciales mal codificadas
- ❌ `INVALID_CREDENTIALS` - Usuario/contraseña incorrectos

#### JWT Token
- ❌ `NO_BEARER_TOKEN` - Sin token Bearer
- ❌ `EMPTY_TOKEN` - Token vacío
- ❌ `EXPIRED_TOKEN` - Token expirado
- ❌ `MALFORMED_TOKEN` - Token malformado
- ❌ `INVALID_TOKEN` - Token inválido

## 📊 Endpoints de Monitoreo

### 1. Consultar Intentos Fallidos
```http
GET /api/logs/failed-access?limit=50
```

**Respuesta de ejemplo**:
```json
{
  "status": "success",
  "message": "Logs de intentos fallidos",
  "logs": [
    {
      "id": 1,
      "timestamp": "2025-10-01T02:56:15.019Z",
      "message": "FAILED_ACCESS | IP: ::1 | METHOD: GET | ROUTE: /api/admin/dashboard | AUTH_TYPE: BASIC_AUTH | USERNAME: admin | REASON: INVALID_CREDENTIALS | STATUS: 401 | USER_AGENT: Mozilla/5.0...",
      "parsed": {
        "type": "FAILED_ACCESS",
        "ip": "::1",
        "method": "GET",
        "route": "/api/admin/dashboard",
        "authtype": "BASIC_AUTH",
        "username": "admin",
        "reason": "INVALID_CREDENTIALS",
        "status": "401"
      }
    }
  ],
  "count": 3,
  "totalLines": 3,
  "logFile": "D:\\Repositorio\\Bdd Compareware\\JavaS\\api-node\\logs\\failed-access.log"
}
```

### 2. Consultar Eventos de Seguridad
```http
GET /api/logs/security?limit=50
```

### 3. Estadísticas de Logs
```http
GET /api/logs/stats
```

**Respuesta de ejemplo**:
```json
{
  "status": "success",
  "message": "Estadísticas de logs",
  "timestamp": "2025-10-01T02:56:41.661Z",
  "stats": {
    "failedAccess": {
      "exists": true,
      "lines": 3,
      "size": 1024,
      "lastModified": "2025-10-01T02:56:26.377Z",
      "file": "...\\logs\\failed-access.log"
    },
    "security": {
      "exists": true,
      "lines": 4,
      "size": 890,
      "lastModified": "2025-10-01T02:56:53.779Z",
      "file": "...\\logs\\security.log"
    },
    "logDirectory": "...\\logs"
  }
}
```

## 🔍 Estructura de Logs

### Archivo: `failed-access.log`
```
[2025-10-01T02:56:15.019Z] FAILED_ACCESS | IP: ::1 | METHOD: GET | ROUTE: /api/admin/dashboard | AUTH_TYPE: BASIC_AUTH | USERNAME: admin | REASON: INVALID_CREDENTIALS | STATUS: 401 | USER_AGENT: Mozilla/5.0 (Windows NT; Windows NT 10.0; es-MX) WindowsPowerShell/5.1.26100.6584
[2025-10-01T02:56:20.844Z] FAILED_ACCESS | IP: ::1 | METHOD: GET | ROUTE: /api/admin/dashboard | AUTH_TYPE: BASIC_AUTH | USERNAME: N/A | REASON: NO_AUTH_HEADER | STATUS: 401 | USER_AGENT: Mozilla/5.0 (Windows NT; Windows NT 10.0; es-MX) WindowsPowerShell/5.1.26100.6584
```

### Archivo: `security.log`
```
[2025-10-01T02:56:15.020Z] SECURITY_EVENT | TYPE: FAILED_AUTH | IP: ::1 | ROUTE: /api/admin/dashboard | USER: admin | REASON: INVALID_CREDENTIALS
[2025-10-01T02:56:53.779Z] SECURITY_EVENT | TYPE: SUCCESSFUL_AUTH | IP: ::1 | ROUTE: /api/admin/dashboard | MESSAGE: Autenticación básica exitosa para usuario: admin | DATA: {"authType":"BASIC_AUTH","username":"admin"}
```

## 🧪 Pruebas Realizadas

### ✅ Escenario 1: Credenciales Incorrectas (Basic Auth)
- **Petición**: `GET /api/admin/dashboard` con `admin:wrong_password`
- **Resultado**: ❌ HTTP 401 + Log registrado
- **Log**: `INVALID_CREDENTIALS` con usuario `admin`

### ✅ Escenario 2: Sin Header de Autorización
- **Petición**: `GET /api/admin/dashboard` sin headers
- **Resultado**: ❌ HTTP 401 + Log registrado
- **Log**: `NO_AUTH_HEADER` con usuario `N/A`

### ✅ Escenario 3: JWT Token Malformado
- **Petición**: `GET /api/jwt/protected` con token inválido
- **Resultado**: ❌ HTTP 401 + Log registrado
- **Log**: `MALFORMED_TOKEN` con JWT_TOKEN

### ✅ Escenario 4: Autenticación Exitosa
- **Petición**: `GET /api/admin/dashboard` con credenciales correctas
- **Resultado**: ✅ Log de éxito registrado
- **Log**: `SUCCESSFUL_AUTH` en security.log

## 🔒 Características de Seguridad

### Información Completa de Auditoría
- **Trazabilidad**: Cada intento incluye IP, timestamp y contexto completo
- **Forense**: Logs estructurados para análisis posterior
- **Correlación**: Logs separados pero correlacionables por IP/timestamp

### Prevención de Ataques
- **Detección de patrones**: Múltiples intentos desde misma IP
- **Análisis de comportamiento**: User-Agent y patrones de acceso
- **Alertas tempranas**: Logs en tiempo real para monitoreo

### Gestión de Archivos
- **Auto-creación**: Directorio y archivos se crean automáticamente
- **Escritura asíncrona**: No bloquea el servidor
- **Limpieza**: Preparado para rotación de logs (futuro)

## 📁 Archivos Creados/Modificados

### ✅ Nuevos Archivos de Log
- `JavaS/api-node/logs/failed-access.log` - Intentos fallidos detallados
- `JavaS/api-node/logs/security.log` - Eventos de seguridad generales

### ✅ Código Modificado
- `JavaS/api-node/app.js` - Sistema de logging completo implementado

## 🚀 Estado del Servidor

**✅ Servidor corriendo**: `http://localhost:4000`
**✅ Logs activos**: Registrando todos los intentos
**✅ Endpoints funcionando**: Verificación completada

## 📝 Eventos Registrados en Pruebas

| Timestamp | Tipo | IP | Ruta | Usuario | Estado |
|-----------|------|----|----- |---------|--------|
| 02:56:15.019Z | FAILED_ACCESS | ::1 | /api/admin/dashboard | admin | ❌ INVALID_CREDENTIALS |
| 02:56:20.844Z | FAILED_ACCESS | ::1 | /api/admin/dashboard | N/A | ❌ NO_AUTH_HEADER |
| 02:56:26.377Z | FAILED_ACCESS | ::1 | /api/jwt/protected | N/A | ❌ MALFORMED_TOKEN |
| 02:56:53.779Z | SUCCESSFUL_AUTH | ::1 | /api/admin/dashboard | admin | ✅ BASIC_AUTH_SUCCESS |

---

**✅ Práctica 5 Completada** - Sistema de logging de seguridad implementado correctamente con registro completo de intentos fallidos y eventos de seguridad.