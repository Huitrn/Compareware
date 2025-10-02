#  Autenticación Básica - Compareware API

##  Descripción de la Práctica

Esta implementación cumple con los requisitos de la **Práctica 2: Implementar autenticación básica con usuario y contraseña**.

### Pasos Implementados:

34. **Solicitar credenciales en la ruta privada** ✓
35. **Comparar con credenciales fijas** ✓  
36. **Responder con acceso concedido o denegado** ✓

## 🔧 Configuración

### Credenciales Fijas (archivo .env):
```env
BASIC_AUTH_USER=admin
BASIC_AUTH_PASSWORD=123456
```

##  Middleware de Autenticación

El middleware `basicAuth` implementa:
- Validación del header `Authorization`
- Decodificación de credenciales Base64
- Comparación con credenciales fijas
- Respuestas apropiadas (401 Unauthorized)

##  Rutas Protegidas

### 1. **Dashboard de Administración**
- **URL**: `GET http://localhost:4000/api/admin/dashboard`
- **Protección**: Requiere autenticación básica
- **Funcionalidad**: Muestra estadísticas del sistema

### 2. **Ruta de Prueba Simple**  
- **URL**: `GET http://localhost:4000/api/private/test`
- **Protección**: Requiere autenticación básica
- **Funcionalidad**: Respuesta simple de confirmación

##  Pruebas en Postman

### **Caso 1: Sin Credenciales (Acceso Denegado)**

**Request:**
```
GET http://localhost:4000/api/admin/dashboard
Headers: (sin Authorization)
```

**Response Esperada:**
```json
Status: 401 Unauthorized
{
  "error": "Acceso denegado",
  "message": "Se requieren credenciales de autenticación básica"
}
```

### **Caso 2: Credenciales Incorrectas (Acceso Denegado)**

**Request:**
```
GET http://localhost:4000/api/admin/dashboard
Authorization: Basic (usuario: wrong, password: wrong)
```

**Response Esperada:**
```json
Status: 401 Unauthorized
{
  "error": "Acceso denegado", 
  "message": "Credenciales incorrectas"
}
```

### **Caso 3: Credenciales Correctas (Acceso Concedido)**

**Request:**
```
GET http://localhost:4000/api/admin/dashboard
Authorization: Basic (usuario: admin, password: 123456)
```

**Response Esperada:**
```json
Status: 200 OK
{
  "message": "Acceso concedido al panel de administración",
  "user": "admin",
  "timestamp": "2024-XX-XXTXX:XX:XX.XXXZ",
  "statistics": [
    {"tabla": "usuarios", "total": "X"},
    {"tabla": "marcas", "total": "X"}, 
    {"tabla": "perifericos", "total": "X"}
  ],
  "status": "authenticated"
}
```

##  Instrucciones de Postman

### Configurar Autenticación Básica:

1. **Abrir Postman**
2. **Crear nueva request**: `GET http://localhost:4000/api/admin/dashboard`
3. **Ir a la pestaña "Authorization"**
4. **Seleccionar Type**: "Basic Auth"
5. **Llenar campos**:
   - Username: `admin`
   - Password: `123456`
6. **Enviar request**

### Probar Sin Autenticación:

1. **Misma URL**: `GET http://localhost:4000/api/admin/dashboard`
2. **Authorization Type**: "No Auth"  
3. **Enviar request** → Debería devolver 401

##  Funcionamiento Técnico

1. **Cliente envía request** a ruta protegida
2. **Middleware basicAuth** intercepta la request
3. **Verifica header Authorization** (Basic xxxxx)
4. **Decodifica credenciales** Base64 → usuario:contraseña
5. **Compara con credenciales fijas** del .env
6. **Si coinciden**: `next()` → continúa a la ruta
7. **Si no coinciden**: Respuesta 401 + WWW-Authenticate header

##  Comandos para Probar

```bash
# Iniciar el servidor
cd "d:\Repositorio\Bdd Compareware\JavaS\api-node"
node app.js

# Probar con curl (credenciales correctas)
curl -u admin:123456 http://localhost:4000/api/private/test

# Probar con curl (sin credenciales)
curl http://localhost:4000/api/private/test
```

## Resultados de la Práctica

✅ **Paso 34**: Las rutas privadas solicitan credenciales mediante HTTP Basic Auth
✅ **Paso 35**: Las credenciales se comparan con valores fijos (admin/123456)
✅ **Paso 36**: El sistema responde apropiadamente:
   - **Acceso concedido**: Status 200 + datos solicitados
   - **Acceso denegado**: Status 401 + mensaje de error

---

**Autenticación Básica implementada exitosamente en Compareware API**