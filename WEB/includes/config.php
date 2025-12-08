<?php
// Configuración de la aplicación
define('APP_NAME', 'Sistema de Biblioteca');
define('APP_VERSION', '1.0');
define('BASE_URL', 'http://localhost/biblioteca');

// Configuración de uploads
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/biblioteca/assets/uploads/');
define('UPLOAD_URL', BASE_URL . '/assets/uploads/');

// Configuración de imágenes
define('MAX_IMAGE_SIZE', 2097152); // 2MB
define('THUMBNAIL_WIDTH', 200);
define('THUMBNAIL_HEIGHT', 300);

// Iniciar sesión
session_start();

// Incluir clases necesarias
require_once 'database.php';
require_once 'validator.php';
// CDN para Bootstrap y FontAwesome
define('BOOTSTRAP_CSS', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
define('BOOTSTRAP_JS', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js');
define('FONTAWESOME', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
?>