# Sistema-Biblioteca
Proyecto Final web, Biblioteca Virtual realizado por: Luis Calderón, Luis Ortega, Octavio Frauca y Arelys Carrión.

# 📚 Sistema de Biblioteca Virtual - UTP - Proyecto Semestral Ingeniería Web

## 🎯 Resumen del Proyecto
Sistema web completo para la gestión de una biblioteca universitaria que permite:
- Gestión de usuarios con diferentes roles y permisos
- CRUD de estudiantes, libros y categorías
- Reservas de libros con control de inventario
- Sistema de solicitudes para libros no disponibles
- Reportes estadísticos y exportación a Excel
- Interfaz pública para estudiantes

## ✅ Requisitos Cumplidos

### 🔐 **Login y Autenticación**
- ✅ Sistema de login seguro para administradores
- ✅ Página pública de login para estudiantes
- ✅ Control de sesiones y timeout automático

### 👥 **CRUD de Usuarios y Roles**
- ✅ Módulo completo de usuarios (Admin, Bibliotecario y Estudiante.)
- ✅ Sistema de roles con permisos específicos
- ✅ Control total o parcial según rol
- ✅ Validación de datos y seguridad

### 🎓 **CRUD de Estudiantes**
- ✅ Registro completo con: CIP, nombres, apellidos, fecha nacimiento, carrera
- ✅ Validación de CIP único (no duplicados)
- ✅ Asociación estudiante ↔ usuario
- ✅ Búsqueda y filtros avanzados

### 📂 **CRUD de Categorías**
- ✅ Categorías de libros (Química, Sistemas, Matemática, etc.)
- ✅ Conteo automático de libros por categoría
- ✅ Validación de nombres únicos

### 📖 **CRUD de Libros**
- ✅ Altas, bajas y consultas completas
- ✅ Campos: ISBN, título, autor, descripción, costo, existencias
- ✅ Asignación de categorías
- ✅ Búsqueda avanzada por múltiples criterios

### 🖼️ **Gestión de Imágenes**
- ✅ Subida de imágenes de portada de libros
- ✅ Creación automática de thumbnails
- ✅ Almacenamiento en servidor y rutas en BD
- ✅ Validación de tipos y tamaños

### 📊 **Reportes y Estadísticas**
- ✅ Reporte de libros por categoría (disponibles/no disponibles)
- ✅ Reporte de reservas por fecha con filtros
- ✅ Exportación a Excel de consultas
- ✅ Estadísticas de libros más usados por período

### 🔄 **Reservas y Préstamos**
- ✅ Página pública para reservas de estudiantes
- ✅ Disminución automática de inventario al reservar
- ✅ Aumento automático al devolver
- ✅ Control de días de reserva

### 📝 **Solicitudes de Libros**
- ✅ Módulo para libros no disponibles
- ✅ Estudiantes pueden solicitar libros faltantes
- ✅ Especificación de materia y justificación
- ✅ Seguimiento de estado (pendiente/aprobado/rechazado)

### 🛡️ **Seguridad y Validación**
- ✅ Clase dedicada de conexión a base de datos
- ✅ Clase completa de sanitización y validación
- ✅ Protección contra inyección SQL
- ✅ Validación de formularios en frontend y backend

## 🛠️ Tecnologías Utilizadas
- **Backend:** PHP 7.4+, MySQL
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Librerías:** PHP GD (para imágenes), PHPSpreadsheet (para Excel)
- **Patrones:** MVC, POO, PDO

## 🗄️ Base de Datos
Adjuntamos fuera de la carpeta con todo el proyeto el archivo llamado 'sistema_biblioteca que contiene:
- Esquema completo de la base de datos
- Datos de prueba para todas las tablas
- Índices y relaciones optimizadas
- Usuarios por defecto para pruebas

## 🚀 Instalación

### Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Wamp64)
- Extensiones PHP: PDO, GD, zip (para Excel)

