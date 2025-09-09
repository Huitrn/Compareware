# Compareware.sql

Este proyecto contiene el script de la base de datos **Compareware**, diseñada para gestionar información relevante para aplicaciones de comparación de perifericos.

## Descripción General
La base de datos **Compareware** está pensada para almacenar y organizar datos relacionados con perifericoss, categorías, usuarios y comparaciones. Permite realizar consultas eficientes para comparar características y precios entre diferentes opciones.

## Estructura Principal
- **Productos**: Tabla principal para almacenar información de productos.
- **Categorías**: Organización de perifericos por tipo .
- **Usuarios**: Registro de usuarios que pueden realizar comparaciones o guardar favoritos.
- **Comparaciones**: Historial y detalles de comparaciones realizadas.

> Consulta el archivo `Compareware.sql` para ver la definición completa de tablas, relaciones y restricciones.

## Requisitos
- Motor de base de datos compatible con SQL (por ejemplo, MySQL, PostgreSQL, SQL Server).
- Herramienta para ejecutar scripts SQL (DBeaver, SQL Server Management Studio, phpMyAdmin, etc.).

## Instalación y Uso
1. Abre tu gestor de base de datos preferido.
2. Crea una nueva base de datos (por ejemplo, `compareware`).
3. Ejecuta el script `Compareware.sql` para crear las tablas y relaciones.

```sql
-- Ejemplo de creación de base de datos en MySQL
CREATE DATABASE compareware;
USE compareware;
SOURCE Compareware.sql;
```

## Ejemplo de Consultas
```sql
-- Obtener todos los productos de una categoría
SELECT * FROM productos WHERE categoria_id = 1;

-- Comparar dos productos por precio
SELECT nombre, precio FROM productos WHERE producto_id IN (101, 102);
```

## Autor
- Nombre: [Huitron Viveros Kevin E.]
- Contacto: [khuitron777@gmail.com]

## Licencia
Este proyecto se distribuye bajo la licencia MIT. Consulta el archivo LICENSE para más detalles.

---

> Si tienes dudas o sugerencias, puedes abrir un issue o contactar al autor.
