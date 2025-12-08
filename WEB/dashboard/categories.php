<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!Auth::isLoggedIn() || !Auth::hasPermission('libros')) {
    header('Location: ../login.php');
    exit;
}

require_once '../classes/Category.php';

$categoryModel = new Category();

$message = '';
$messageType = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $data = [
                    'nombre' => Validator::sanitize($_POST['nombre']),
                    'descripcion' => Validator::sanitize($_POST['descripcion'])
                ];
                
                if ($categoryModel->nameExists($data['nombre'])) {
                    throw new Exception('El nombre de categoría ya existe');
                }
                
                if ($categoryModel->create($data)) {
                    $message = 'Categoría creada exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $data = [
                    'nombre' => Validator::sanitize($_POST['nombre']),
                    'descripcion' => Validator::sanitize($_POST['descripcion'])
                ];
                
                if ($categoryModel->nameExists($data['nombre'], $id)) {
                    throw new Exception('El nombre de categoría ya existe');
                }
                
                if ($categoryModel->update($id, $data)) {
                    $message = 'Categoría actualizada exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                if ($categoryModel->delete($id)) {
                    $message = 'Categoría eliminada exitosamente';
                    $messageType = 'success';
                }
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Obtener datos
$categories = $categoryModel->getWithBookCount();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías - Sistema de Biblioteca</title>
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
                    <h1 class="h2">Gestión de Categorías</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <?php foreach ($categories as $category): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($category['nombre']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($category['descripcion']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary"><?php echo $category['total_libros']; ?> libros</span>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-category" 
                                                data-category='<?php echo json_encode($category); ?>'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta categoría?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para categorías -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="categoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nueva Categoría</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="categoryId">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de Categoría *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
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
        const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        const form = document.getElementById('categoryForm');
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const categoryId = document.getElementById('categoryId');
        
        // Botones de editar
        document.querySelectorAll('.edit-category').forEach(button => {
            button.addEventListener('click', function() {
                const category = JSON.parse(this.dataset.category);
                
                modalTitle.textContent = 'Editar Categoría';
                formAction.value = 'update';
                categoryId.value = category.id;
                
                document.getElementById('nombre').value = category.nombre;
                document.getElementById('descripcion').value = category.descripcion || '';
                
                categoryModal.show();
            });
        });
        
        // Botón nueva categoría
        document.querySelector('[data-bs-target="#categoryModal"]').addEventListener('click', function() {
            modalTitle.textContent = 'Nueva Categoría';
            formAction.value = 'create';
            form.reset();
            categoryId.value = '';
        });
    });
    </script>
</body>
</html>