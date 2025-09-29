<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Compareware

Compareware es una aplicación web moderna desarrollada con una **arquitectura híbrida** que combina lo mejor de varios frameworks para ofrecer una experiencia completa y escalable. El proyecto utiliza **Laravel** (PHP) para el frontend y vistas Blade, **Node.js + Express** para la API REST backend, y **PostgreSQL** como sistema de gestión de base de datos.

## ¿Qué es Compareware?

Compareware es una plataforma que permite a los usuarios comparar periféricos de computadora (mouse, teclados, monitores, etc.) de manera eficiente. Los usuarios pueden:

- Registrarse y autenticarse de forma segura
- Explorar catálogos de periféricos organizados por categorías
- Realizar comparaciones detalladas entre productos
- Dejar comentarios y reseñas
- Gestionar sus comparaciones favoritas

## Arquitectura del Proyecto

### **Frontend: Laravel + Blade**
- Vistas elegantes y responsivas usando el sistema de plantillas Blade
- Gestión de rutas web y controladores PHP
- Interfaz de usuario intuitiva y moderna

### **Backend: Node.js + Express**
- API REST robusta para todas las operaciones CRUD
- Autenticación JWT segura
- Conexión directa con PostgreSQL
- Escalabilidad y alto rendimiento

### **Base de Datos: PostgreSQL**
- Sistema relacional robusto y confiable
- Estructura optimizada para consultas complejas
- Integridad referencial y transacciones ACID

## Tecnologías Utilizadas

- **Laravel 10.x** - Framework PHP para frontend
- **Node.js 18.x + Express** - Runtime y framework para API backend
- **PostgreSQL** - Sistema de gestión de base de datos
- **JWT** - Autenticación segura sin estado
- **bcryptjs** - Encriptación de contraseñas
- **AJAX/Fetch** - Comunicación frontend-backend

## Inicio Rápido

### Requisitos Previos
- PHP 8.0+ y Composer
- Node.js 18.0+ y npm
- PostgreSQL 12+
- Git

### Instalación Básica
```bash
# Clonar el repositorio
git clone https://github.com/tu-usuario/compareware.git
cd compareware

# Instalar dependencias de Laravel
composer install

# Instalar dependencias de Node.js
cd api-node
npm install

# Configurar variables de entorno
cp .env.example .env
# Editar .env con tus configuraciones

# Iniciar servidores
php artisan serve          # Laravel en http://localhost:8000
node app.js               # API en http://localhost:4000
```

## Documentación Detallada

Para una instalación completa, configuración avanzada y uso del proyecto, consulta nuestros manuales especializados:

###  [Manual de Usuario - Frameworks](storage/framework/Manual_usuario_frameworks.md)
Este manual contiene:
- **Instalación paso a paso** de Composer, Laravel y Node.js con capturas de pantalla
- **Configuración detallada** de cada framework
- **Estructura de carpetas** y organización del proyecto  
- **Integración entre frameworks** y comunicación frontend-backend
- **Mejores prácticas** y recomendaciones de desarrollo
- **Ejemplos de código** y configuraciones

###  [Manual de Usuario - Base de Datos](database/manual-de-usuario-para-base-de-datos.md)
Este manual incluye:
- **Estructura completa** de todas las tablas del proyecto
- **Consultas SQL básicas y avanzadas** para cada tabla
- **Operaciones CRUD** con ejemplos prácticos
- **Configuración de conexiones** desde Laravel y Node.js
- **Backup y restore** de la base de datos
- **Optimización** con índices y mejores prácticas
- **Seguridad** y administración de usuarios

## Pruebas y Desarrollo

### Pruebas con Postman
El proyecto incluye endpoints completamente funcionales para:
- `POST /api/register` - Registro de usuarios
- `POST /api/login` - Autenticación
- `GET /POST /PUT /DELETE /api/perifericos` - CRUD completo

### Desarrollo Local
```bash
# Laravel (Frontend)
php artisan serve

# Node.js (API Backend)  
cd api-node
node app.js

# Base de datos
psql -U postgres -d Compareware
```

## Características Principales

**Autenticación segura** con JWT y bcrypt  
**API REST completa** con operaciones CRUD  
**Frontend moderno** con Laravel Blade  
**Base de datos robusta** con PostgreSQL  
**Documentación completa** con manuales detallados  
**Arquitectura escalable** y mantenible  
**Pruebas con Postman** incluidas  

## Estructura del Proyecto

```
compareware/
├── app/Http/Controllers/    # Controladores Laravel
├── resources/views/         # Vistas Blade
├── routes/web.php          # Rutas web Laravel  
├── api-node/               # API Node.js
│   ├── app.js             # Servidor Express
│   └── .env               # Variables de entorno API
├── database/              # Migraciones y manual DB
├── storage/framework/     # Manual de frameworks
└── public/               # Recursos públicos
```

## Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

Revisa los manuales de frameworks y base de datos para entender la arquitectura antes de contribuir.

## Soporte y Documentación

- **Instalación y configuración**: Ver [Manual de Frameworks](storage/framework/Manual_usuario_frameworks.md)
- **Base de datos y consultas**: Ver [Manual de Base de Datos](database/manual-de-usuario-para-base-de-datos.md)
- **Problemas conocidos**: Revisar la sección de troubleshooting en los manuales
- **API Reference**: Documentación de endpoints en el manual de frameworks

## Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo `LICENSE` para más detalles.

---

**¿Primera vez usando Compareware?**  
Te recomendamos empezar leyendo el [Manual de Frameworks](storage/framework/Manual_usuario_frameworks.md) para la instalación y luego el [Manual de Base de Datos](database/manual-de-usuario-para-base-de-datos.md) para entender la estructura de datos.