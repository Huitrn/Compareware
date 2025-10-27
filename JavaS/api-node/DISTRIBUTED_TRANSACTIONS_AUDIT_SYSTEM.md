# Sistema de Transacciones Distribuidas y Auditoría - Compareware API

## 📋 Resumen

Este documento describe la implementación de un sistema avanzado de transacciones distribuidas, capa de repositorios y auditoría completa para la API de Compareware. El sistema garantiza la integridad de los datos y proporciona trazabilidad completa de todas las operaciones.

## 🏗️ Arquitectura Implementada

### Componentes Principales

1. **Capa de Repositorios** - Abstracción del acceso a datos
2. **Transaction Manager** - Gestor de transacciones distribuidas
3. **Sistema de Auditoría** - Logging completo de operaciones
4. **Servicios de Negocio** - Lógica de aplicación
5. **Controladores** - Endpoints de API

### Patrón Arquitectónico

```
Controllers → Services → Repositories → Database
                ↓
         Transaction Manager
                ↓
          Audit System
```

## 🔄 Transacciones Distribuidas

### Características

- ✅ **Atomicidad**: Todas las operaciones se ejecutan o ninguna
- ✅ **Consistencia**: Los datos mantienen su integridad
- ✅ **Aislamiento**: Las transacciones no interfieren entre sí
- ✅ **Durabilidad**: Los cambios confirmados persisten
- ✅ **Compensación Automática**: Rollback automático en caso de error

### Flujo de Transacción Distribuida

```javascript
1. Generar ID único de transacción
2. Iniciar transacción en BD
3. Ejecutar operaciones secuencialmente:
   - Validar usuario
   - Validar stock
   - Reservar productos
   - Crear orden
   - Procesar pago
4. Si todas exitosas → COMMIT
5. Si alguna falla → ROLLBACK automático
6. Registrar resultado en auditoría
```

### Ejemplo de Uso

```javascript
const orderService = new OrderService();

const result = await orderService.createOrderWithTransaction(
  userId, 
  orderData, 
  orderItems, 
  requestInfo
);

if (result.success) {
  console.log('Pedido creado:', result.order);
} else {
  console.log('Error revertido:', result.error);
}
```

## 📊 Sistema de Repositorios

### BaseRepository

Clase base que proporciona operaciones CRUD estándar con logging automático:

```javascript
class BaseRepository {
  async create(data, client = null) // Crear registro
  async findById(id, client = null) // Buscar por ID
  async update(id, data, client = null) // Actualizar
  async delete(id, client = null) // Eliminar
  async findAll(page, limit, client = null) // Listar con paginación
  async executeQuery(query, params, client = null) // Query personalizada
}
```

### Repositorios Específicos

#### UserRepository
- Gestión de usuarios con hash de contraseñas
- Autenticación y autorización
- Soft delete y restauración

#### OrderRepository
- Creación de órdenes con items
- Gestión de estados
- Consultas avanzadas con joins

#### AuditLogRepository
- Registro completo de auditoría
- Consultas por transacción, usuario, entidad
- Limpieza automática de logs antiguos

## 📝 Sistema de Auditoría y Logs

### Tipos de Logs

1. **Logs de Operación**: Cada operación CRUD
2. **Logs de Transacción**: Inicio, commit, rollback
3. **Logs de Seguridad**: Autenticación, autorización
4. **Logs de Sistema**: Eventos del sistema

### Estructura de Log de Auditoría

```javascript
{
  transaction_id: "txn_1234567890_abc123",
  user_id: 1,
  action: "ORDER_CREATED",
  entity_type: "ORDER",
  entity_id: 123,
  old_values: { status: "PENDING" },
  new_values: { status: "PROCESSING" },
  ip_address: "192.168.1.100",
  user_agent: "Mozilla/5.0...",
  operations_count: 5,
  start_time: "2025-10-20T10:30:00Z",
  end_time: "2025-10-20T10:30:02Z",
  duration_ms: 2000,
  status: "SUCCESS",
  created_at: "2025-10-20T10:30:02Z"
}
```

## 🚀 Endpoints de API

### Órdenes/Pedidos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/orders` | Crear pedido con transacción distribuida |
| GET | `/api/orders/:id/history` | Obtener pedido con historial completo |
| PUT | `/api/orders/:id/cancel` | Cancelar pedido con rollback |
| GET | `/api/orders/user/:userId` | Órdenes de usuario específico |
| GET | `/api/orders/status/:status` | Órdenes por estado |
| GET | `/api/orders/stats/transactions` | Estadísticas de transacciones |

### Auditoría y Logs

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/audit/transaction/:id` | Logs por transacción |
| GET | `/api/audit/user/:userId` | Logs por usuario |
| GET | `/api/audit/entity/:type/:id` | Logs por entidad |
| GET | `/api/audit/action/:action` | Logs por acción |
| GET | `/api/audit/stats` | Estadísticas de auditoría |
| DELETE | `/api/audit/cleanup` | Limpiar logs antiguos |

## 💾 Estructura de Base de Datos

### Tablas Principales

```sql
-- Productos con control de stock
products (id, name, price, stock_quantity, reserved_quantity, ...)

-- Órdenes principales
orders (id, user_id, total_amount, status, payment_method, ...)

-- Items de órdenes
order_items (id, order_id, product_id, quantity, unit_price, ...)

-- Logs de auditoría completos
audit_logs (id, transaction_id, action, entity_type, old_values, new_values, ...)

-- Transacciones activas (monitoreo)
active_transactions (id, transaction_id, status, operations_count, ...)
```

### Índices Optimizados

- Índices por transacción, usuario, entidad
- Índices compuestos para consultas frecuentes
- Índices parciales para datos activos

## 🧪 Testing y Validación

### Script de Pruebas

```bash
# Ejecutar suite completa de pruebas
node test_distributed_transactions.js --full

# Probar solo creación de pedidos
node test_distributed_transactions.js --create-order

# Probar rollback con pedido que falla
node test_distributed_transactions.js --create-fail

# Ver estadísticas
node test_distributed_transactions.js --stats
```

### Casos de Prueba

1. ✅ **Pedido Exitoso**: Todas las operaciones se completan
2. ✅ **Usuario Inexistente**: Rollback en validación de usuario
3. ✅ **Stock Insuficiente**: Rollback en validación de stock
4. ✅ **Pago Fallido**: Rollback en procesamiento de pago
5. ✅ **Cancelación**: Liberación de stock reservado

## 📈 Monitoreo y Estadísticas

### Métricas Disponibles

- Transacciones por día/hora
- Tiempo promedio de transacción
- Tasa de éxito/fallo
- Operaciones más frecuentes
- Transacciones activas en tiempo real

### Alertas y Notificaciones

- Transacciones que exceden tiempo límite
- Tasas de error elevadas
- Problemas de stock
- Errores de pago frecuentes

## 🛡️ Seguridad y Control de Acceso

### Autenticación Requerida

Todos los endpoints requieren token JWT válido:

```javascript
headers: {
  'Authorization': 'Bearer <jwt_token>',
  'Content-Type': 'application/json'
}
```

### Rate Limiting

- Creación de pedidos: 5 requests/minuto
- Cancelación de pedidos: 3 requests/minuto
- Consultas generales: Límites estándar

### Validaciones

- Validación de esquemas de entrada
- Sanitización de datos
- Prevención de inyección SQL
- Logging de intentos sospechosos

## 🚀 Instalación y Configuración

### 1. Ejecutar Migración de Base de Datos

```sql
-- Ejecutar el archivo de migración
\i database/migrations/001_distributed_transactions_audit_system.sql
```

### 2. Configurar Variables de Entorno

```env
# Base de datos
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=compareware
DB_USERNAME=postgres
DB_PASSWORD=password

# JWT
JWT_SECRET=your_jwt_secret_key

# API
PORT=3000
NODE_ENV=development
```

### 3. Instalar Dependencias

```bash
cd JavaS/api-node
npm install axios  # Para el script de pruebas
```

### 4. Iniciar el Servidor

```bash
node app.js
```

## 📚 Ejemplos de Uso

### Crear Pedido con Transacción Distribuida

```javascript
const orderData = {
  user_id: 1,
  total_amount: 299.97,
  shipping_address: "123 Test Street, Test City",
  payment_method: "CREDIT_CARD"
};

const orderItems = [
  {
    product_id: 1,
    quantity: 2,
    unit_price: 129.99,
    subtotal: 259.98
  }
];

// POST /api/orders
const response = await fetch('/api/orders', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ orderData, orderItems })
});
```

### Consultar Historial de Pedido

```javascript
// GET /api/orders/123/history
const history = await fetch('/api/orders/123/history', {
  headers: { 'Authorization': `Bearer ${token}` }
});

const { order, items, history: auditLogs } = await history.json();
```

### Obtener Estadísticas

```javascript
// GET /api/orders/stats/transactions
const stats = await fetch('/api/orders/stats/transactions', {
  headers: { 'Authorization': `Bearer ${token}` }
});

const { weekly_stats, failed_transactions_last_24h } = await stats.json();
```

## 🔧 Mantenimiento

### Limpieza Automática de Logs

```sql
-- Limpiar logs más antiguos de 90 días
SELECT clean_old_audit_logs(90);
```

### Monitoreo de Performance

```sql
-- Ver transacciones más lentas
SELECT transaction_id, action, duration_ms 
FROM audit_logs 
WHERE duration_ms > 5000 
ORDER BY duration_ms DESC;
```

### Backup de Auditoría

```bash
# Exportar logs de auditoría
pg_dump -t audit_logs compareware > audit_backup.sql
```

## 🎯 Beneficios Implementados

1. ✅ **Integridad de Datos**: Transacciones ACID completas
2. ✅ **Trazabilidad**: Auditoría completa de todas las operaciones
3. ✅ **Escalabilidad**: Patrón de repositorios modular
4. ✅ **Mantenibilidad**: Código organizado y documentado
5. ✅ **Monitoreo**: Estadísticas y métricas en tiempo real
6. ✅ **Seguridad**: Logging de seguridad y control de acceso
7. ✅ **Recuperación**: Rollback automático en caso de errores
8. ✅ **Performance**: Índices optimizados y consultas eficientes

## 📞 Soporte y Contacto

Para dudas o problemas con el sistema:

1. Revisar los logs de auditoría para diagnóstico
2. Ejecutar el script de pruebas para validar funcionalidad
3. Consultar las estadísticas de transacciones para identificar patrones
4. Revisar la documentación de endpoints específicos

---

**¡Sistema de transacciones distribuidas y auditoría implementado exitosamente! 🚀**