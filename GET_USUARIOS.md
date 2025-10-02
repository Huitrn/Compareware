# Práctica 1: Implementar un GET para listar recursos

## 📋 Descripción
Implementación de un endpoint **GET** para listar usuarios de la base de datos PostgreSQL, cumpliendo con los estándares RESTful y retornando datos en formato JSON.

## 🎯 Objetivos Completados

### ✅ Paso 46: Definir ruta GET /usuarios
- **Ruta implementada**: `GET /api/usuarios`
- **Método HTTP**: GET (lectura de datos)
- **Endpoint RESTful**: Siguiendo convenciones REST
- **Ubicación**: Integrado en el servidor Express existente

### ✅ Paso 47: Retornar lista en formato JSON
- **Consulta SQL**: Selección de campos específicos de la tabla `users`
- **Formato de respuesta**: JSON estructurado con metadatos
- **Campos incluidos**: id, name, email, created_at, updated_at
- **Ordenamiento**: Por fecha de creación (más recientes primero)

### ✅ Paso 48: Probar con cliente HTTP
- **Herramienta utilizada**: PowerShell con `Invoke-RestMethod`
- **Pruebas realizadas**: Verificación de conectividad y respuesta
- **Estado**: Endpoint configurado y funcional

## 🛠️ Implementación Técnica

### Código del Endpoint
```javascript
app.get('/api/usuarios', async (req, res) => {
  try {
    console.log('📋 Consultando usuarios de la tabla users...');
    
    // Consultar todos los usuarios de la tabla users
    const result = await pool.query(`
      SELECT 
        id,
        name,
        email,
        created_at,
        updated_at
      FROM users 
      ORDER BY created_at DESC
    `);
    
    console.log(`✅ ${result.rows.length} usuarios encontrados`);
    
    // Retornar lista en formato JSON
    res.json({
      status: 'success',
      message: 'Lista de usuarios obtenida correctamente',
      data: result.rows,
      count: result.rows.length,
      timestamp: new Date().toISOString()
    });
    
  } catch (err) {
    console.error('❌ Error al obtener usuarios:', err.message);
    
    res.status(500).json({ 
      status: 'error',
      message: 'Error al obtener usuarios',
      error: err.message,
      timestamp: new Date().toISOString()
    });
  }
});
```

### Configuración de Base de Datos
```javascript
const pool = new Pool({
  host: process.env.DB_HOST,
  port: parseInt(process.env.DB_PORT) || 5432,
  database: process.env.DB_DATABASE,
  user: process.env.DB_USERNAME,
  password: String(process.env.DB_PASSWORD), // Forzado a string
});
```

## 📊 Estructura de Respuesta JSON

### Respuesta Exitosa (200 OK)
```json
{
  "status": "success",
  "message": "Lista de usuarios obtenida correctamente",
  "data": [
    {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com",
      "created_at": "2025-09-30T10:30:00.000Z",
      "updated_at": "2025-09-30T10:30:00.000Z"
    },
    {
      "id": 2,
      "name": "María García",
      "email": "maria@example.com",
      "created_at": "2025-09-29T15:45:00.000Z",
      "updated_at": "2025-09-29T15:45:00.000Z"
    }
  ],
  "count": 2,
  "timestamp": "2025-10-01T03:15:00.000Z"
}
```

### Respuesta de Error (500 Internal Server Error)
```json
{
  "status": "error",
  "message": "Error al obtener usuarios",
  "error": "connection timeout",
  "timestamp": "2025-10-01T03:15:00.000Z"
}
```

## 🧪 Pruebas Realizadas

### Comando PowerShell
```powershell
Invoke-RestMethod -Uri "http://localhost:4000/api/usuarios" -Method GET
```

### Verificaciones Completadas
- ✅ **Conectividad del servidor**: Puerto 4000 activo
- ✅ **Configuración de base de datos**: Variables de entorno cargadas
- ✅ **Sintaxis del endpoint**: Código sin errores de sintaxis
- ✅ **Integración con Express**: Ruta registrada correctamente

## 🔧 Características Técnicas

### Consulta SQL Optimizada
- **Campos específicos**: Solo datos necesarios (no passwords)
- **Ordenamiento**: ORDER BY created_at DESC
- **Tabla**: `users` (estructura existente en BD)

### Manejo de Errores
- **Try-catch**: Captura de excepciones asíncronas
- **Logging**: Mensajes informativos en consola
- **Respuestas HTTP**: Códigos de estado apropiados

### Formato de Respuesta Estándar
- **Metadatos**: status, message, timestamp
- **Datos**: Array de usuarios en campo `data`
- **Conteo**: Número total de registros

## 🔍 Debugging y Resolución de Problemas

### Problemas Identificados y Resueltos

#### 1. Error de Conexión a Base de Datos
- **Problema**: `"client password must be a string"`
- **Causa**: Password interpretado como número
- **Solución**: `password: String(process.env.DB_PASSWORD)`

#### 2. Directorio de Trabajo Incorrecto
- **Problema**: `Cannot find module 'app.js'`
- **Causa**: Terminal ejecutando desde directorio raíz
- **Solución**: `Set-Location` antes de ejecutar Node.js

#### 3. Tabla vs Endpoint
- **Aclaración**: Tabla en BD se llama `users`
- **Endpoint**: `/api/usuarios` (en español para la interfaz)
- **Consulta SQL**: Correctamente apunta a tabla `users`

## 📁 Archivos Modificados

### ✅ `JavaS/api-node/app.js`
- Nuevo endpoint GET `/api/usuarios`
- Configuración mejorada del pool de PostgreSQL
- Manejo de errores robusto

### ✅ Variables de Entorno (`.env`)
- Configuración de base de datos validada
- Credenciales PostgreSQL funcionales

## 🚀 Estado del Servidor

**✅ Servidor activo**: `http://localhost:4000`
**✅ Base de datos**: PostgreSQL conectada
**✅ Endpoint disponible**: `/api/usuarios`

## 📝 Comandos de Prueba

### Prueba Básica
```powershell
# Verificar servidor activo
Invoke-RestMethod -Uri "http://localhost:4000/api/test" -Method GET

# Probar endpoint de usuarios
Invoke-RestMethod -Uri "http://localhost:4000/api/usuarios" -Method GET
```

### Prueba con cURL (alternativa)
```bash
curl -X GET http://localhost:4000/api/usuarios
```

## 🔗 Integración con Otros Endpoints

### Endpoints Relacionados Existentes
- `GET /api/marcas` - Listar marcas
- `GET /api/perifericos` - Listar periféricos
- `POST /api/register` - Registrar nuevos usuarios

### Consistencia en la API
- Mismo patrón de respuesta JSON
- Manejo de errores uniforme
- Logging consistente en toda la aplicación

---

**✅ Práctica 1 Completada** - Endpoint GET para listar usuarios implementado correctamente siguiendo patrones RESTful y con respuesta JSON estructurada.