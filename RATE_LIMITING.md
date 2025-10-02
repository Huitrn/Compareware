# Práctica 4: Configurar limitación de peticiones (Rate Limiting)

## 📋 Descripción
Sistema de **Rate Limiting** implementado para proteger la API contra abuso de peticiones y ataques de fuerza bruta.

## 🎯 Objetivos Completados

### ✅ Paso 40: Agregar un contador de accesos a la ruta
- **Implementación**: Sistema de conteo por IP + ruta usando `Map` en memoria
- **Funcionalidad**: Cada petición se registra con timestamp para control temporal
- **Clave única**: `${clientIP}:${routeIdentifier}` para diferenciar por ruta

### ✅ Paso 41: Definir un límite (ej. 5 peticiones por minuto)
- **Configuración**: 5 peticiones máximo por ventana de 1 minuto (60,000 ms)
- **Algoritmo**: Ventana deslizante que filtra peticiones por timestamp
- **Limpieza automática**: Eliminación periódica de contadores expirados

### ✅ Paso 42: Bloquear si se excede el límite  
- **Respuesta**: HTTP 429 "Too Many Requests"
- **Headers informativos**: 
  - `X-RateLimit-Limit`: Límite máximo
  - `X-RateLimit-Remaining`: Peticiones restantes
  - `X-RateLimit-Reset`: Tiempo de reset
  - `Retry-After`: Segundos para reintentar

## 🛠️ Implementación Técnica

### Configuración del Rate Limiting
```javascript
const RATE_LIMIT_CONFIG = {
  windowMs: 60 * 1000,    // 1 minuto
  maxRequests: 5,         // 5 peticiones máximo
  message: 'Demasiadas peticiones desde esta IP, intenta nuevamente en 1 minuto'
};
```

### Middleware Principal
```javascript
const rateLimiter = (routeIdentifier = 'default') => {
  return (req, res, next) => {
    // Lógica de rate limiting
  };
};
```

### Rutas Protegidas con Rate Limiting

#### 1. Dashboard de Administrador
- **Ruta**: `GET /api/admin/dashboard`
- **Protección**: Basic Auth + Rate Limiting
- **Identificador**: `admin-dashboard`

#### 2. Perfil JWT
- **Ruta**: `GET /api/jwt/profile`  
- **Protección**: JWT Token + Rate Limiting
- **Identificador**: `jwt-profile`

#### 3. Ruta Protegida JWT
- **Ruta**: `GET /api/jwt/protected`
- **Protección**: JWT Token + Rate Limiting  
- **Identificador**: `jwt-protected`

#### 4. Generación de Tokens
- **Ruta**: `POST /api/generate-token`
- **Protección**: Rate Limiting (crítico para prevenir ataques de fuerza bruta)
- **Identificador**: `generate-token`

## 📊 Endpoint de Monitoreo

### Estado del Rate Limiting
```http
GET /api/rate-limit/status
```

**Respuesta de ejemplo**:
```json
{
  "ip": "::1",
  "config": {
    "maxRequests": 5,
    "windowMs": 60000,
    "windowDescription": "1 minuto"
  },
  "routes": [
    {
      "route": "admin-dashboard",
      "currentCount": 2,
      "limit": 5,
      "remaining": 3,
      "resetTime": "2024-01-15T10:31:00.000Z"
    }
  ]
}
```

## 🧪 Pruebas en Postman

### Escenario 1: Peticiones Normales (≤5 por minuto)
1. Realizar 3-4 peticiones a `/api/admin/dashboard`
2. **Resultado esperado**: Respuestas exitosas (200)
3. **Headers**: `X-RateLimit-Remaining` muestra peticiones restantes

### Escenario 2: Exceder el Límite (>5 por minuto)
1. Realizar 6+ peticiones rápidamente a la misma ruta
2. **Resultado esperado**: Primeras 5 exitosas, sexta retorna 429
3. **Respuesta 429**:
```json
{
  "error": "Rate limit exceeded",
  "message": "Demasiadas peticiones desde esta IP, intenta nuevamente en 1 minuto",
  "details": {
    "limit": 5,
    "windowMs": 60000,
    "retryAfter": "45 segundos",
    "resetTime": "2024-01-15T10:32:00.000Z"
  }
}
```

### Escenario 3: Diferentes Rutas
1. Realizar 5 peticiones a `/api/admin/dashboard`
2. Realizar 5 peticiones a `/api/jwt/protected`
3. **Resultado esperado**: Ambas rutas tienen contadores independientes

## 🔒 Características de Seguridad

### Algoritmo de Ventana Deslizante
- **Ventaja**: Distribución uniforme de peticiones
- **Funcionamiento**: Elimina peticiones antiguas antes de contar
- **Precisión**: Control exacto por minuto

### Protección por IP + Ruta
- **Granularidad**: Cada ruta tiene su propio límite
- **Aislamiento**: Una ruta bloqueada no afecta otras
- **Flexibilidad**: Diferentes límites por tipo de endpoint

### Limpieza de Memoria
- **Optimización**: Eliminación automática de contadores expirados
- **Probabilidad**: 10% de probabilidad por petición
- **Eficiencia**: Previene acumulación de memoria

## 🚀 Servidor en Funcionamiento

**Estado**: ✅ Servidor corriendo en `http://localhost:4000`

**Configuración cargada**:
- ✅ Variables de entorno (.env)
- ✅ Conexión a PostgreSQL
- ✅ Rate limiting activo en todas las rutas protegidas

## 📝 Próximas Pruebas Sugeridas

1. **Prueba de Carga**: Simular múltiples IPs
2. **Prueba de Persistencia**: Verificar reset después de 1 minuto
3. **Prueba de Rutas**: Confirmar límites independientes
4. **Monitoreo**: Usar `/api/rate-limit/status` para seguimiento

---

**✅ Práctica 4 Completada** - Rate Limiting implementado correctamente con todas las especificaciones requeridas.