<?php
// repair_thumbnails.php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!Auth::isLoggedIn() || !Auth::hasPermission('libros')) {
    header('Location: ../login.php');
    exit;
}

require_once '../classes/Book.php';
require_once '../classes/ImageHandler.php';

$bookModel = new Book();
$imageHandler = new ImageHandler();

// Obtener libros sin thumbnails
$booksWithoutThumbs = $bookModel->getBooksWithoutThumbnails();

echo "<h2>Reparando Thumbnails</h2>";
echo "<p>Libros que necesitan thumbnails: " . count($booksWithoutThumbs) . "</p>";

if (!empty($booksWithoutThumbs)) {
    $repaired = $imageHandler->repairThumbnails($booksWithoutThumbs);
    echo "<p>Thumbnails reparados: " . $repaired . "</p>";
} else {
    echo "<p>No hay libros que necesiten reparación.</p>";
}

echo "<a href='books.php'>Volver a Gestión de Libros</a>";
?>