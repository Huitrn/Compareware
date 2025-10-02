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

## Prácticas de Desarrollo Implementadas

Durante el desarrollo del proyecto se han implementado cinco prácticas importantes que mejoran significativamente la robustez, seguridad y mantenibilidad del sistema:

### **Práctica 1: Operaciones CRUD Completas**
El sistema cuenta con operaciones CRUD completamente funcionales con validación de datos, manejo de conflictos y respuestas estructuradas. Se implementaron rutas GET, POST, PUT y DELETE para la gestión de usuarios, con logging detallado de todas las operaciones.

### **Práctica 2: Cabeceras Personalizadas**
Se agregó un sistema de cabeceras HTTP personalizadas que incluye información de la aplicación, identificadores únicos de petición, timestamps de respuesta y cabeceras de seguridad estándar. Esto facilita el debugging y proporciona información útil para el monitoreo del sistema.

### **Práctica 3: Sistema de Logging Avanzado**
Implementación de un sistema de logging estructurado que registra eventos de seguridad, intentos de acceso fallidos, autenticaciones y errores del sistema. Los logs se organizan en archivos separados con rotación automática por tamaño.

### **Práctica 4: Validaciones y Códigos HTTP Específicos**
Se desarrollaron funciones especializadas para devolver códigos HTTP precisos (400, 401, 403) según el tipo de error, con middleware de validación de campos requeridos, formatos y respuestas estructuradas con información detallada del error.

### **Práctica 5: Log de Respuestas HTTP**
Sistema completo de logging HTTP que registra automáticamente todas las peticiones con método, ruta, código de estado, tiempo de respuesta e información del cliente. Incluye endpoints para consultar estadísticas y análisis de uso.

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

### Documentación de Prácticas Implementadas

- **[CABECERAS_PERSONALIZADAS.md](JavaS/api-node/CABECERAS_PERSONALIZADAS.md)** - Detalles completos de implementación de cabeceras HTTP personalizadas, incluyendo ejemplos de uso y verificación con Postman
- **[VALIDACIONES_CODIGOS_HTTP.md](JavaS/api-node/VALIDACIONES_CODIGOS_HTTP.md)** - Guía completa del sistema de validaciones y códigos de error HTTP específicos, con casos de uso y ejemplos de respuesta

## Sistema de Seguridad y Monitoreo

### **Autenticación y Autorización**
- Autenticación básica (Basic Auth) para rutas administrativas
- Sistema JWT para autenticación de usuario
- Rate limiting configurable por IP
- Middleware de validación de roles y permisos

### **Sistema de Logging**
El proyecto incluye un sistema completo de logging con archivos especializados:

```
logs/
├── failed-access.log         # Intentos de acceso fallidos
├── security.log             # Eventos de seguridad
├── http-requests.log        # Peticiones HTTP entrantes
├── http-responses.log       # Respuestas HTTP con códigos
├── access.log              # Log estilo Apache Combined
└── http-errors.log         # Errores HTTP (4xx, 5xx)
```

### **Endpoints de Monitoreo**
- `GET /api/logs/failed-access` - Consultar accesos fallidos
- `GET /api/logs/security` - Eventos de seguridad
- `GET /api/logs/http-requests` - Peticiones HTTP registradas
- `GET /api/logs/http-responses` - Respuestas con códigos de estado
- `GET /api/logs/http-stats` - Estadísticas de uso y rendimiento

## Pruebas y Desarrollo

### Pruebas con Postman
El proyecto incluye endpoints completamente funcionales para:
- `POST /api/register` - Registro de usuarios
- `POST /api/login` - Autenticación
- `GET /POST /PUT /DELETE /api/perifericos` - CRUD completo
- Endpoints de demostración para códigos HTTP específicos
- Rutas de consulta de logs y estadísticas

###  Cómo Iniciar los Servidores

**IMPORTANTE**: Necesitas ejecutar ambos servidores simultáneamente para que el proyecto funcione correctamente.

#### **Servidor 1: Frontend Laravel** 
```bash
# Terminal/CMD #1 - Navegar a la carpeta principal del proyecto Laravel
cd "d:\Repositorio\Bdd Compareware\Compareware"

# Iniciar el servidor Laravel
php artisan serve

#  Laravel estará disponible en: http://localhost:8000
```

#### **Servidor 2: API Node.js**
```bash
# Terminal/CMD #2 - Navegar a la carpeta de la API
cd "d:\Repositorio\Bdd Compareware\JavaS\api-node"

# Iniciar el servidor de la API
node app.js

#  API REST estará disponible en: http://localhost:4000
```

#### **Base de Datos PostgreSQL**
```bash
# Terminal/CMD #3 - Conectar a la base de datos (opcional para desarrollo)
psql -U postgres -h 127.0.0.1 -p 5432 -d Compareware

#  PostgreSQL debe estar corriendo en puerto 5432
```

#### ** Verificación Rápida**
- **Frontend**: Visita `http://localhost:8000` para ver las vistas Blade
- **API**: Visita `http://localhost:4000/api/test` para verificar que la API responde
- **Base de Datos**: Ejecuta `SELECT * FROM users;` para verificar la conexión
- **Logs**: Visita `http://localhost:4000/api/logs/http-stats` para ver estadísticas

#### ** Consejos para Desarrollo**
- Mantén **3 terminales abiertas** (Laravel, Node.js, y PostgreSQL)
- Si cambias código de Laravel, no necesitas reiniciar el servidor
- Si cambias código de Node.js, reinicia con `Ctrl+C` y luego `node app.js`
- Usa `php artisan serve --port=8080` si el puerto 8000 está ocupado
- Los logs HTTP se generan automáticamente en la carpeta `/logs`

## Características Principales

**Autenticación segura** con JWT y bcrypt  
**API REST completa** con operaciones CRUD  
**Frontend moderno** con Laravel Blade  
**Base de datos robusta** con PostgreSQL  
**Sistema de logging avanzado** con múltiples archivos especializados  
**Validaciones HTTP específicas** con códigos de error precisos  
**Cabeceras personalizadas** para debugging y monitoreo  
**Rate limiting** y protección contra abuso  
**Documentación completa** con manuales detallados  
**Arquitectura escalable** y mantenible  
**Pruebas con Postman** incluidas  

## Estructura del Proyecto

```
compareware/
├── app/Http/Controllers/    # Controladores Laravel
├── resources/views/         # Vistas Blade
├── routes/web.php          # Rutas web Laravel  
├── JavaS/api-node/         # API Node.js
│   ├── app.js             # Servidor Express
│   ├── .env               # Variables de entorno API
│   ├── logs/              # Archivos de log del sistema
│   ├── CABECERAS_PERSONALIZADAS.md
│   └── VALIDACIONES_CODIGOS_HTTP.md
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

Revisa los manuales de frameworks y base de datos para entender la arquitectura antes de contribuir. También consulta la documentación de las prácticas implementadas para mantener la consistencia del código.

## Soporte y Documentación

- **Instalación y configuración**: Ver [Manual de Frameworks](storage/framework/Manual_usuario_frameworks.md)
- **Base de datos y consultas**: Ver [Manual de Base de Datos](database/manual-de-usuario-para-base-de-datos.md)
- **Cabeceras personalizadas**: Ver [Documentación de Cabeceras](JavaS/api-node/CABECERAS_PERSONALIZADAS.md)
- **Validaciones y códigos HTTP**: Ver [Documentación de Validaciones](JavaS/api-node/VALIDACIONES_CODIGOS_HTTP.md)
- **Problemas conocidos**: Revisar la sección de troubleshooting en los manuales
- **API Reference**: Documentación de endpoints en el manual de frameworks

## Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo `LICENSE` para más detalles.

---

**¿Primera vez usando Compareware?**  
Te recomendamos empezar leyendo el [Manual de Frameworks](storage/framework/Manual_usuario_frameworks.md) para la instalación y luego el [Manual de Base de Datos](database/manual-de-usuario-para-base-de-datos.md) para entender la estructura de datos. Para entender las funcionalidades avanzadas, consulta la documentación de las prácticas implementadas.