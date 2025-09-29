# Manual de Usuario - Base de Datos Compareware

---

## Introducción

Este manual documenta la estructura y uso de la base de datos **PostgreSQL** utilizada en el proyecto Compareware. La base de datos almacena información sobre usuarios, periféricos, categorías, comentarios y comparaciones.

---

## Estructura de la Base de Datos

### 1. **Tabla: users**
Almacena la información de los usuarios registrados en el sistema.

```sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Campos:**
- `id`: Identificador único del usuario (auto-incremental)
- `name`: Nombre del usuario
- `email`: Correo electrónico (único)
- `password`: Contraseña encriptada
- `role`: Rol del usuario (user, admin)
- `created_at`: Fecha de creación
- `updated_at`: Fecha de última actualización

---

### 2. **Tabla: categorias**
Define las categorías de periféricos disponibles.

```sql
CREATE TABLE categorias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Campos:**
- `id`: Identificador único de la categoría
- `nombre`: Nombre de la categoría (ej: Mouse, Teclado, Monitor)
- `descripcion`: Descripción detallada de la categoría
- `created_at`: Fecha de creación

---

### 3. **Tabla: perifericos**
Almacena la información de los periféricos disponibles para comparar.

```sql
CREATE TABLE perifericos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100),
    categoria_id INTEGER REFERENCES categorias(id),
    precio NUMERIC(10,2),
    descripcion TEXT,
    especificaciones TEXT,
    imagen_url VARCHAR(255),
    disponible BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Campos:**
- `id`: Identificador único del periférico
- `nombre`: Nombre comercial del periférico
- `marca`: Marca del fabricante
- `modelo`: Modelo específico
- `categoria_id`: Referencia a la categoría (FK)
- `precio`: Precio del periférico
- `descripcion`: Descripción general
- `especificaciones`: Especificaciones técnicas detalladas
- `imagen_url`: URL de la imagen del producto
- `disponible`: Estado de disponibilidad
- `created_at`: Fecha de creación
- `updated_at`: Fecha de última actualización

---

### 4. **Tabla: comentarios**
Almacena comentarios y reseñas de usuarios sobre periféricos.

```sql
CREATE TABLE comentarios (
    id SERIAL PRIMARY KEY,
    periferico_id INTEGER REFERENCES perifericos(id) ON DELETE CASCADE,
    usuario_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    usuario VARCHAR(100), -- Para comentarios anónimos
    texto TEXT NOT NULL,
    calificacion INTEGER CHECK (calificacion >= 1 AND calificacion <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Campos:**
- `id`: Identificador único del comentario
- `periferico_id`: Referencia al periférico comentado (FK)
- `usuario_id`: Referencia al usuario (FK, opcional)
- `usuario`: Nombre del usuario (para comentarios anónimos)
- `texto`: Contenido del comentario
- `calificacion`: Puntuación de 1 a 5 estrellas
- `created_at`: Fecha del comentario

---

### 5. **Tabla: comparaciones**
Registra las comparaciones realizadas entre periféricos.

```sql
CREATE TABLE comparaciones (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    periferico1_id INTEGER REFERENCES perifericos(id) ON DELETE CASCADE,
    periferico2_id INTEGER REFERENCES perifericos(id) ON DELETE CASCADE,
    resultado TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Campos:**
- `id`: Identificador único de la comparación
- `usuario_id`: Usuario que realizó la comparación (FK)
- `periferico1_id`: Primer periférico comparado (FK)
- `periferico2_id`: Segundo periférico comparado (FK)
- `resultado`: Resultado o notas de la comparación
- `created_at`: Fecha de la comparación

---

## Operaciones Básicas

### **Inserción de datos**

#### Agregar una categoría:
```sql
INSERT INTO categorias (nombre, descripcion) 
VALUES ('Mouse Gaming', 'Ratones diseñados para videojuegos con alta precisión');
```

#### Agregar un periférico:
```sql
INSERT INTO perifericos (nombre, marca, modelo, categoria_id, precio, descripcion) 
VALUES ('DeathAdder V3', 'Razer', 'DA-V3-001', 1, 1299.99, 'Mouse gaming ergonómico con sensor de alta precisión');
```

#### Agregar un comentario:
```sql
INSERT INTO comentarios (periferico_id, usuario, texto, calificacion) 
VALUES (1, 'Juan Pérez', 'Excelente mouse, muy cómodo para largas sesiones', 5);
```

---

### **Consultas frecuentes**

#### Listar todos los periféricos con su categoría:
```sql
SELECT p.id, p.nombre, p.marca, c.nombre as categoria, p.precio 
FROM perifericos p 
JOIN categorias c ON p.categoria_id = c.id 
WHERE p.disponible = true;
```

#### Obtener periféricos por categoría:
```sql
SELECT * FROM perifericos 
WHERE categoria_id = 1 AND disponible = true 
ORDER BY precio ASC;
```

#### Ver comentarios de un periférico específico:
```sql
SELECT c.usuario, c.texto, c.calificacion, c.created_at 
FROM comentarios c 
WHERE c.periferico_id = 1 
ORDER BY c.created_at DESC;
```

#### Obtener estadísticas de calificaciones:
```sql
SELECT p.nombre, 
       AVG(c.calificacion) as promedio_calificacion,
       COUNT(c.id) as total_comentarios
FROM perifericos p 
LEFT JOIN comentarios c ON p.id = c.periferico_id 
GROUP BY p.id, p.nombre;
```

---

### **Actualizaciones comunes**

#### Actualizar precio de un periférico:
```sql
UPDATE perifericos 
SET precio = 1199.99, updated_at = CURRENT_TIMESTAMP 
WHERE id = 1;
```

#### Marcar periférico como no disponible:
```sql
UPDATE perifericos 
SET disponible = false 
WHERE id = 1;
```

---

### **Eliminaciones**

#### Eliminar un comentario:
```sql
DELETE FROM comentarios WHERE id = 1;
```

#### Eliminar un periférico (eliminará comentarios asociados automáticamente):
```sql
DELETE FROM perifericos WHERE id = 1;
```

---

## Índices recomendados

Para mejorar el rendimiento de las consultas:

```sql
-- Índices para búsquedas frecuentes
CREATE INDEX idx_perifericos_categoria ON perifericos(categoria_id);
CREATE INDEX idx_perifericos_marca ON perifericos(marca);
CREATE INDEX idx_perifericos_precio ON perifericos(precio);
CREATE INDEX idx_comentarios_periferico ON comentarios(periferico_id);
```

---

## Backup y Restore

### **Crear backup:**
```bash
pg_dump -U postgres -h localhost Compareware > backup_compareware.sql
```

### **Restaurar backup:**
```bash
psql -U postgres -h localhost Compareware < backup_compareware.sql
```

---

## Conexión desde las aplicaciones

### **Laravel (.env):**
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=Compareware
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

### **Node.js (.env):**
```
PGUSER=postgres
PGPASSWORD=tu_contraseña
PGHOST=localhost
PGPORT=5432
PGDATABASE=Compareware
```

---

## Mantenimiento

### **Verificar integridad:**
```sql
-- Verificar registros huérfanos
SELECT * FROM perifericos WHERE categoria_id NOT IN (SELECT id FROM categorias);
```

### **Limpiar datos antiguos:**
```sql
-- Eliminar comentarios antiguos (más de 2 años)
DELETE FROM comentarios WHERE created_at < NOW() - INTERVAL '2 years';
```

---

## Seguridad

- Usa roles específicos para cada aplicación
- No uses el usuario `postgres` en producción
- Realiza backups regulares
- Implementa políticas de contraseñas fuertes
- Restringe conexiones por IP cuando sea posible

---

**Este manual cubre la estructura y operaciones principales de la base de datos Compareware. Para operaciones avanzadas o modificaciones de esquema, consulta la documentación oficial de PostgreSQL.**