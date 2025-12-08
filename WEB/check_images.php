<?php
// check_images.php - Script de diagnóstico
echo "<h2>Diagnóstico de Imágenes</h2>";

// Verificar estructura de carpetas
$carpetas = [
    'assets/uploads/images' => '../assets/uploads/images',
    'assets/uploads/thumbnails' => '../assets/uploads/thumbnails',
    'uploads/images' => '../uploads/images', 
    'uploads/thumbnails' => '../uploads/thumbnails'
];

foreach ($carpetas as $nombre => $ruta) {
    echo "<h3>Carpeta: $nombre</h3>";
    if (is_dir($ruta)) {
        echo "Existe<br>";
        echo "Permisos: " . substr(sprintf('%o', fileperms($ruta)), -4) . "<br>";
        
        // Listar archivos
        $archivos = scandir($ruta);
        echo "Archivos: " . (count($archivos) - 2) . "<br>";
        if (count($archivos) > 2) {
            echo "<ul>";
            foreach ($archivos as $archivo) {
                if ($archivo != '.' && $archivo != '..') {
                    echo "<li>$archivo</li>";
                }
            }
            echo "</ul>";
        }
    } else {
        echo "No existe<br>";
    }
    echo "<hr>";
}

// Conectar a la base de datos y verificar imágenes
require_once 'includes/config.php';
require_once 'includes/database.php';

try {
    $db = new Database();
    $stmt = $db->conn->query("SELECT id, titulo, imagen_thumbnail FROM libros WHERE imagen_thumbnail IS NOT NULL");
    $libros_con_imagen = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Libros con imágenes en la base de datos:</h3>";
    if (empty($libros_con_imagen)) {
        echo "No hay libros con imágenes en la base de datos.<br>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Título</th><th>Imagen</th><th>¿Existe?</th></tr>";
        foreach ($libros_con_imagen as $libro) {
            $existe = false;
            $rutas = [
                "../assets/uploads/thumbnails/{$libro['imagen_thumbnail']}",
                "assets/uploads/thumbnails/{$libro['imagen_thumbnail']}",
                "../uploads/thumbnails/{$libro['imagen_thumbnail']}"
            ];
            
            foreach ($rutas as $ruta) {
                if (file_exists($ruta)) {
                    $existe = true;
                    break;
                }
            }
            
            echo "<tr>";
            echo "<td>{$libro['id']}</td>";
            echo "<td>{$libro['titulo']}</td>";
            echo "<td>{$libro['imagen_thumbnail']}</td>";
            echo "<td>" . ($existe ? "✅" : "❌") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error al conectar con la base de datos: " . $e->getMessage();
}
?>