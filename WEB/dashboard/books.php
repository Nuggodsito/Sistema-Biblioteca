<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!Auth::isLoggedIn() || !Auth::hasPermission('libros')) {
    header('Location: ../login.php');
    exit;
}

require_once '../classes/Book.php';
require_once '../classes/Category.php';
require_once '../classes/ImageHandler.php';

$bookModel = new Book();
$categoryModel = new Category();
$imageHandler = new ImageHandler();

$message = '';
$messageType = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $data = [
                    'isbn' => Validator::sanitize($_POST['isbn']),
                    'titulo' => Validator::sanitize($_POST['titulo']),
                    'autor' => Validator::sanitize($_POST['autor']),
                    'descripcion' => Validator::sanitize($_POST['descripcion']),
                    'costo' => $_POST['costo'],
                    'existencias' => $_POST['existencias'],
                    'categoria_id' => $_POST['categoria_id'],
                    'imagen_portada' => '',
                    'imagen_thumbnail' => ''
                ];
                
                // Validar ISBN único
                if (!empty($data['isbn']) && $bookModel->isbnExists($data['isbn'])) {
                    throw new Exception('El ISBN ya existe');
                }
                
                // Procesar imagen si se subió
                if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
                    $imageNames = $imageHandler->uploadBookImage($_FILES['imagen_portada'], 0);
                    $data['imagen_portada'] = $imageNames['image'];
                    $data['imagen_thumbnail'] = $imageNames['thumbnail'];
                }
                
                if ($bookModel->create($data)) {
                    $message = 'Libro creado exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $data = [
                    'isbn' => Validator::sanitize($_POST['isbn']),
                    'titulo' => Validator::sanitize($_POST['titulo']),
                    'autor' => Validator::sanitize($_POST['autor']),
                    'descripcion' => Validator::sanitize($_POST['descripcion']),
                    'costo' => $_POST['costo'],
                    'existencias' => $_POST['existencias'],
                    'categoria_id' => $_POST['categoria_id']
                ];
                
                // Validar ISBN único
                if (!empty($data['isbn']) && $bookModel->isbnExists($data['isbn'], $id)) {
                    throw new Exception('El ISBN ya existe');
                }
                
                // Procesar nueva imagen si se subió
                if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
                    $currentBook = $bookModel->getById($id);
                    if ($currentBook) {
                        // Eliminar imágenes antiguas
                        $imageHandler->deleteBookImages($currentBook['imagen_portada'], $currentBook['imagen_thumbnail']);
                    }
                    
                    $imageNames = $imageHandler->uploadBookImage($_FILES['imagen_portada'], $id);
                    $data['imagen_portada'] = $imageNames['image'];
                    $data['imagen_thumbnail'] = $imageNames['thumbnail'];
                }
                
                if ($bookModel->update($id, $data)) {
                    $message = 'Libro actualizado exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $book = $bookModel->getById($id);
                
                if ($book) {
                    // Eliminar imágenes
                    $imageHandler->deleteBookImages($book['imagen_portada'], $book['imagen_thumbnail']);
                    
                    if ($bookModel->delete($id)) {
                        $message = 'Libro eliminado exitosamente';
                        $messageType = 'success';
                    }
                }
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Obtener datos
$books = $bookModel->getAllWithCategory();
$categories = $categoryModel->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Libros - Sistema de Biblioteca</title>
    <link href="<?php echo BOOTSTRAP_CSS; ?>" rel="stylesheet">
    <link href="<?php echo FONTAWESOME; ?>" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Libros</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookModal">
                        <i class="fas fa-plus"></i> Nuevo Libro
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Portada</th>
                                        <th>Título</th>
                                        <th>Autor</th>
                                        <th>ISBN</th>
                                        <th>Categoría</th>
                                        <th>Costo</th>
                                        <th>Existencias</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td>
                                            <?php if ($book['imagen_thumbnail']): ?>
                                                <img src="<?php echo ImageHandler::getImageUrl($book['imagen_thumbnail'], 'thumbnail'); ?>" 
                                                     alt="Portada" class="book-thumbnail">
                                            <?php else: ?>
                                                <div class="book-thumbnail bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-book text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($book['titulo']); ?></strong>
                                            <?php if ($book['descripcion']): ?>
                                                <br><small class="text-muted"><?php echo substr($book['descripcion'], 0, 50); ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($book['autor']); ?></td>
                                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $book['categoria_nombre']; ?></span>
                                        </td>
                                        <td>$<?php echo number_format($book['costo'], 2); ?></td>
                                        <td>
                                            <span class="badge <?php echo $book['existencias'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $book['existencias']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($book['existencias'] > 0): ?>
                                                <span class="status-available">Disponible</span>
                                            <?php else: ?>
                                                <span class="status-unavailable">Agotado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-book" 
                                                    data-book='<?php echo json_encode($book); ?>'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este libro?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para libros -->
    <div class="modal fade" id="bookModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="bookForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nuevo Libro</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="bookId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="isbn" class="form-label">ISBN</label>
                                <input type="text" class="form-control" id="isbn" name="isbn">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="categoria_id" class="form-label">Categoría *</label>
                                <select class="form-select" id="categoria_id" name="categoria_id" required>
                                    <option value="">Seleccionar Categoría</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['nombre']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="autor" class="form-label">Autor *</label>
                            <input type="text" class="form-control" id="autor" name="autor" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="costo" class="form-label">Costo *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="costo" name="costo" 
                                           step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="existencias" class="form-label">Existencias *</label>
                                <input type="number" class="form-control" id="existencias" name="existencias" 
                                       min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="imagen_portada" class="form-label">Imagen de Portada</label>
                            <input type="file" class="form-control" id="imagen_portada" name="imagen_portada"
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="form-text">Formatos: JPG, PNG, GIF, WebP. Máximo 2MB.</div>
                        </div>
                        
                        <div id="currentImage" class="mb-3" style="display: none;">
                            <label class="form-label">Imagen Actual</label>
                            <div>
                                <img id="currentImagePreview" src="" alt="Imagen actual" class="book-thumbnail">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const bookModal = new bootstrap.Modal(document.getElementById('bookModal'));
        const form = document.getElementById('bookForm');
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const bookId = document.getElementById('bookId');
        const currentImage = document.getElementById('currentImage');
        const currentImagePreview = document.getElementById('currentImagePreview');
        
        // Botones de editar
        document.querySelectorAll('.edit-book').forEach(button => {
            button.addEventListener('click', function() {
                const book = JSON.parse(this.dataset.book);
                
                modalTitle.textContent = 'Editar Libro';
                formAction.value = 'update';
                bookId.value = book.id;
                
                document.getElementById('isbn').value = book.isbn || '';
                document.getElementById('titulo').value = book.titulo;
                document.getElementById('autor').value = book.autor;
                document.getElementById('descripcion').value = book.descripcion || '';
                document.getElementById('costo').value = book.costo;
                document.getElementById('existencias').value = book.existencias;
                document.getElementById('categoria_id').value = book.categoria_id;
                
                // Mostrar imagen actual si existe
                if (book.imagen_thumbnail) {
                    currentImagePreview.src = '../assets/uploads/thumbnails/' + book.imagen_thumbnail;
                    currentImage.style.display = 'block';
                } else {
                    currentImage.style.display = 'none';
                }
                
                bookModal.show();
            });
        });
        
        // Botón nuevo libro
        document.querySelector('[data-bs-target="#bookModal"]').addEventListener('click', function() {
            modalTitle.textContent = 'Nuevo Libro';
            formAction.value = 'create';
            form.reset();
            bookId.value = '';
            currentImage.style.display = 'none';
        });
        
        // Vista previa de imagen seleccionada
        document.getElementById('imagen_portada').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    currentImagePreview.src = e.target.result;
                    currentImage.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    });
    </script>
</body>
</html>