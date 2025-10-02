#  Validación de Token JWT - Compareware API

##  Descripción de la Práctica

Esta implementación cumple con los requisitos de la **Práctica 3: Agregar validación de token**.

### Pasos Implementados:

37. **Configurar middleware que revise un token en headers** ✓
38. **Si el token es válido, permitir acceso** ✓  
39. **Si no, responder con 401** ✓

## 🔧 Configuración JWT

### Variables de Entorno (archivo .env):
```env
JWT_SECRET=13246587cba           # Clave secreta para firmar/verificar tokens
JWT_REFRESH_SECRET=13246587acb   # Clave para refresh tokens
```

## 🛡️ Middleware de Validación JWT

El middleware `verifyToken` implementa:
- Validación del header `Authorization: Bearer <token>`
- Verificación del token usando `JWT_SECRET`
- Decodificación de información del usuario
- Manejo de tokens expirados/malformados
- Respuestas 401 apropiadas

## Rutas Protegidas con JWT

### 1. **Perfil de Usuario**
- **URL**: `GET http://localhost:4000/api/jwt/profile`
- **Protección**: Requiere token JWT válido
- **Funcionalidad**: Obtiene información del usuario autenticado

### 2. **Ruta Protegida Simple**  
- **URL**: `GET http://localhost:4000/api/jwt/protected`
- **Protección**: Requiere token JWT válido
- **Funcionalidad**: Respuesta simple de confirmación

### 3. **Generar Token de Prueba**
- **URL**: `POST http://localhost:4000/api/generate-token`
- **Protección**: Ninguna (para obtener tokens)
- **Funcionalidad**: Genera un token JWT para testing

##  Pruebas en Postman

### **PASO 1: Generar un Token de Prueba**

**Request:**
```
POST http://localhost:4000/api/generate-token
Content-Type: application/json

Body (raw JSON):
{
  "email": "test@ejemplo.com",
  "password": "123456"
}
```

**Response Esperada:**
```json
Status: 200 OK
{
  "message": "Token generado exitosamente",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "name": "Usuario Test",
    "email": "test@ejemplo.com"
  },
  "expiresIn": "1 hora"
}
```

### **CASO 1: Sin Token (Acceso Denegado)**

**Request:**
```
GET http://localhost:4000/api/jwt/protected
Headers: (sin Authorization)
```

**Response Esperada:**
```json
Status: 401 Unauthorized
{
  "error": "Token requerido",
  "message": "Se requiere un token de autorización en el header (Bearer token)"
}
```

### **CASO 2: Token Inválido (Acceso Denegado)**

**Request:**
```
GET http://localhost:4000/api/jwt/protected
Authorization: Bearer token_falso_invalido
```

**Response Esperada:**
```json
Status: 401 Unauthorized
{
  "error": "Acceso denegado",
  "message": "Token malformado",
  "details": "jwt malformed"
}
```

### **CASO 3: Token Válido (Acceso Concedido)**

**Request:**
```
GET http://localhost:4000/api/jwt/protected
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

**Response Esperada:**
```json
Status: 200 OK
{
  "message": "Acceso concedido - Token JWT válido",
  "user": {
    "id": 1,
    "email": "test@ejemplo.com",
    "name": "Usuario Test",
    "iat": 1696123456,
    "exp": 1696127056
  },
  "timestamp": "2024-XX-XXTXX:XX:XX.XXXZ",
  "status": "success"
}
```

## Instrucciones Detalladas de Postman

### **Configurar Token JWT:**

1. **Generar Token**:
   - POST `http://localhost:4000/api/generate-token`
   - Body → raw → JSON: `{"email": "test@ejemplo.com", "password": "123456"}`
   - Copiar el `token` de la respuesta

2. **Usar Token en Rutas Protegidas**:
   - GET `http://localhost:4000/api/jwt/protected`
   - **Authorization** → Type: **Bearer Token**
   - **Token**: Pegar el token copiado
   - Send

### **Probar Sin Token:**

1. **Misma URL**: `GET http://localhost:4000/api/jwt/protected`
2. **Authorization Type**: "No Auth"  
3. **Send** → Debería devolver 401

### **Probar Token Inválido:**

1. **Misma URL**: `GET http://localhost:4000/api/jwt/protected`
2. **Authorization Type**: "Bearer Token"
3. **Token**: `token_falso_123`
4. **Send** → Debería devolver 401

## Funcionamiento Técnico

1. **Cliente envía request** con header `Authorization: Bearer <token>`
2. **Middleware verifyToken** intercepta la request
3. **Extrae el token** del header Authorization
4. **Verifica el token** usando `jwt.verify()` y `JWT_SECRET`
5. **Si es válido**: Decodifica info del usuario → `req.user` → `next()`
6. **Si es inválido**: Respuesta 401 con detalles del error

##  Flujo de Validación

```
Request → verifyToken Middleware → Verificar Header → Extraer Token → 
jwt.verify(token, JWT_SECRET) → ¿Válido? → 
├─ SÍ: req.user = decoded → next() → Ruta protegida
└─ NO: res.status(401) → Error específico
```

## Comandos para Probar

```bash
# Iniciar el servidor (si no está corriendo)
cd "d:\Repositorio\Bdd Compareware\JavaS\api-node"
node app.js

# Probar con curl (obtener token)
curl -X POST http://localhost:4000/api/generate-token \
  -H "Content-Type: application/json" \
  -d '{"email":"test@ejemplo.com","password":"123456"}'

# Probar con curl (usar token)
curl -H "Authorization: Bearer <TOKEN_AQUI>" \
  http://localhost:4000/api/jwt/protected
```

## 🎯 Resultados de la Práctica

**Paso 37**: Middleware configurado para revisar token en headers `Authorization: Bearer`
**Paso 38**: Tokens válidos permiten acceso y decodifican información del usuario
**Paso 39**: Tokens inválidos/faltantes responden con 401 y mensaje específico

## Comparación: Autenticación Básica vs JWT

| Aspecto | Basic Auth | JWT Token |
|---------|------------|-----------|
| **Header** | `Authorization: Basic <base64>` | `Authorization: Bearer <token>` |
| **Validación** | Usuario:contraseña fijos | Token firmado + secreto |
| **Información** | Solo credenciales | Datos del usuario + expiración |
| **Seguridad** | Básica | Avanzada con firma criptográfica |
| **Uso** | APIs simples | Aplicaciones modernas |

---

**Validación de Token JWT implementada exitosamente en Compareware API**