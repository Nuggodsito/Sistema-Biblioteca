<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/validator.php';

if (!Auth::isLoggedIn() || !Auth::isEstudiante()) {
    header('Location: index.php');
    exit;
}

require_once '../classes/Book.php';
require_once '../classes/Reservation.php';
require_once '../classes/Student.php';
require_once '../classes/BookRequest.php';

$bookModel = new Book();
$reservationModel = new Reservation();
$studentModel = new Student();
$requestModel = new BookRequest();

$message = '';
$messageType = '';

// Obtener estudiante actual
$estudiante_actual = $studentModel->getByUserId($_SESSION['user_id']);

if (!$estudiante_actual) {
    $message = 'No se encontró información del estudiante asociada a tu usuario. Contacta al administrador.';
    $messageType = 'danger';
}

// Procesar reserva
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        switch ($action) {
            case 'reservar':
                $libro_id = $_POST['libro_id'];
                
                if (!$estudiante_actual) {
                    throw new Exception('No se encontró información del estudiante asociada a tu usuario.');
                }
                
                $data = [
                    'libro_id' => $libro_id,
                    'estudiante_id' => $estudiante_actual['id'],
                    'usuario_reserva_id' => $_SESSION['user_id'],
                    'dias_reserva' => 7,
                    'fecha_devolucion_estimada' => date('Y-m-d', strtotime('+7 days'))
                ];
                
                // Verificar disponibilidad
                $book = $bookModel->getById($libro_id);
                if (!$book || $book['existencias'] <= 0) {
                    throw new Exception('El libro no está disponible en este momento');
                }
                
                // Verificar si ya tiene reserva activa
                if ($reservationModel->hasActiveReservation($estudiante_actual['id'], $libro_id)) {
                    throw new Exception('Ya tienes una reserva activa de este libro');
                }
                
                if ($reservationModel->create($data)) {
                    // Disminuir existencias
                    $bookModel->updateStock($libro_id, -1);
                    
                    $message = 'Libro reservado exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'solicitar_libro':
                if (!$estudiante_actual) {
                    throw new Exception('No se encontró información del estudiante. No puedes solicitar libros.');
                }
                
                $data = [
                    'titulo_solicitado' => Validator::sanitize($_POST['titulo_solicitado']),
                    'autor_solicitado' => Validator::sanitize($_POST['autor_solicitado']),
                    'materia' => Validator::sanitize($_POST['materia']),
                    'estudiante_id' => $estudiante_actual['id'],
                    'justificacion' => Validator::sanitize($_POST['justificacion'])
                ];
                
                if ($requestModel->create($data)) {
                    $message = 'Solicitud enviada exitosamente';
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
$libros = $bookModel->getAvailableBooks();
$categorias = [];
foreach ($libros as $libro) {
    if (!in_array($libro['categoria_nombre'], $categorias)) {
        $categorias[] = $libro['categoria_nombre'];
    }
}

// Obtener reservas del estudiante
$reservas_estudiante = [];
if ($estudiante_actual) {
    $todas_reservas = $reservationModel->getAllWithDetails();
    foreach ($todas_reservas as $reserva) {
        if ($reserva['estudiante_id'] == $estudiante_actual['id']) {
            $reservas_estudiante[] = $reserva;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Biblioteca Virtual</title>
    <link href="<?php echo BOOTSTRAP_CSS; ?>" rel="stylesheet">
    <link href="<?php echo FONTAWESOME; ?>" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
    <style>
        .book-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .book-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .navbar-public {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .image-placeholder {
            height: 200px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navbar Pública -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-public">
        <div class="container">
            <a class="navbar-brand fw-bold" href="catalog.php">
                <i class="fas fa-book"></i> Biblioteca Virtual
            </a>
            
            <div class="navbar-nav ms-auto align-items-center">
                <!-- Botón Volver al Dashboard -->
                <div class="nav-item me-3">
                    <a href="../dashboard/index.php" class="btn btn-volver">
                        <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
                    </a>
                </div>
                
                <!-- Menú de usuario -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-graduate"></i> 
                        <?php echo $estudiante_actual ? $estudiante_actual['primer_nombre'] : 'Estudiante'; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#reservasModal">
                            <i class="fas fa-clipboard-list"></i> Mis Reservas
                        </a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#solicitudModal">
                            <i class="fas fa-plus-circle"></i> Solicitar Libro
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- El resto del código permanece exactamente igual -->
    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Información del estudiante -->
        <?php if ($estudiante_actual): ?>
            <div class="alert alert-success">
                <strong>Bienvenido:</strong> <?php echo $estudiante_actual['primer_nombre'] . ' ' . $estudiante_actual['primer_apellido']; ?> 
                | <strong>CIP:</strong> <?php echo $estudiante_actual['cip_identificacion']; ?>
                | <strong>Carrera:</strong> <?php echo $estudiante_actual['carrera']; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <strong>Advertencia:</strong> No se encontró información del estudiante asociada a tu usuario. 
                Contacta al administrador para que asocie tu usuario a un estudiante.
            </div>
        <?php endif; ?>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar libros por título, autor o ISBN...">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="categoryFilter">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria; ?>"><?php echo $categoria; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title">Libros Disponibles</h5>
                        <h2 class="card-text"><?php echo count($libros); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <h5 class="card-title">Mis Reservas</h5>
                        <h2 class="card-text"><?php echo count($reservas_estudiante); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catálogo de Libros -->
        <h3 class="mb-3">Libros Disponibles</h3>
        <div class="row" id="booksContainer">
            <?php foreach ($libros as $libro): ?>
            <div class="col-md-4 col-lg-3 mb-4 book-item" 
                 data-title="<?php echo strtolower($libro['titulo']); ?>"
                 data-author="<?php echo strtolower($libro['autor']); ?>"
                 data-category="<?php echo $libro['categoria_nombre']; ?>">
                <div class="card book-card">
                    
                    <?php 
                    // Determinar la ruta correcta de la imagen
                    $imagen_path = "";
                    $imagen_existe = false;

                    if (!empty($libro['imagen_thumbnail'])) {
                        $ruta_posible_1 = "../assets/uploads/thumbnails/" . $libro['imagen_thumbnail'];
                        $ruta_posible_2 = "assets/uploads/thumbnails/" . $libro['imagen_thumbnail'];
                        $ruta_posible_3 = "../uploads/thumbnails/" . $libro['imagen_thumbnail'];
                        
                        if (file_exists($ruta_posible_1)) {
                            $imagen_path = $ruta_posible_1;
                            $imagen_existe = true;
                        } elseif (file_exists($ruta_posible_2)) {
                            $imagen_path = $ruta_posible_2;
                            $imagen_existe = true;
                        } elseif (file_exists($ruta_posible_3)) {
                            $imagen_path = $ruta_posible_3;
                            $imagen_existe = true;
                        }
                    }
                    ?>

                    <?php if ($imagen_existe): ?>
                        <img src="<?php echo $imagen_path; ?>" 
                             class="card-img-top book-image" 
                             alt="<?php echo htmlspecialchars($libro['titulo']); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="image-placeholder" style="display: none;">
                            <div class="text-center">
                                <i class="fas fa-book fa-2x mb-2"></i>
                                <br>
                                <small><?php echo htmlspecialchars($libro['titulo']); ?></small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="image-placeholder">
                            <div class="text-center">
                                <i class="fas fa-book fa-2x mb-2"></i>
                                <br>
                                <small><?php echo htmlspecialchars($libro['titulo']); ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($libro['titulo']); ?></h6>
                        <p class="card-text">
                            <small class="text-muted"><?php echo htmlspecialchars($libro['autor']); ?></small><br>
                            <span class="badge bg-secondary"><?php echo $libro['categoria_nombre']; ?></span>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-<?php echo $libro['existencias'] > 0 ? 'success' : 'danger'; ?>">
                                <?php echo $libro['existencias']; ?> disponibles
                            </span>
                            <?php if ($estudiante_actual): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="reservar">
                                <input type="hidden" name="libro_id" value="<?php echo $libro['id']; ?>">
                                <button type="submit" class="btn btn-primary btn-sm" 
                                        onclick="return confirm('¿Confirmar reserva de este libro?')"
                                        <?php echo $libro['existencias'] <= 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-bookmark"></i> 
                                    <?php echo $libro['existencias'] > 0 ? 'Reservar' : 'No disponible'; ?>
                                </button>
                            </form>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled title="Se requiere información del estudiante">
                                    No disponible
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($libros)): ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No hay libros disponibles en este momento</h4>
                <p class="text-muted">Puedes solicitar un libro usando el formulario de solicitudes.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Mis Reservas -->
    <div class="modal fade" id="reservasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mis Reservas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($reservas_estudiante)): ?>
                        <p class="text-muted">No tienes reservas activas.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Libro</th>
                                        <th>Fecha Reserva</th>
                                        <th>Fecha Devolución</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservas_estudiante as $reserva): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($reserva['libro_titulo']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($reserva['libro_autor']); ?></small>
                                        </td>
                                        <td><?php echo formatDate($reserva['fecha_reserva']); ?></td>
                                        <td>
                                            <?php 
                                            if ($reserva['fecha_devolucion_real']) {
                                                echo formatDate($reserva['fecha_devolucion_real']);
                                            } else {
                                                echo formatDate($reserva['fecha_devolucion_estimada']);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $estado_class = [
                                                'reservado' => 'warning',
                                                'devuelto' => 'success',
                                                'vencido' => 'danger'
                                            ];
                                            $estado_text = [
                                                'reservado' => 'Prestado',
                                                'devuelto' => 'Devuelto',
                                                'vencido' => 'Vencido'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $estado_class[$reserva['estado']]; ?>">
                                                <?php echo $estado_text[$reserva['estado']]; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Solicitar Libro -->
    <div class="modal fade" id="solicitudModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Solicitar Libro No Disponible</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="solicitar_libro">
                        
                        <div class="mb-3">
                            <label class="form-label">Título del Libro *</label>
                            <input type="text" class="form-control" name="titulo_solicitado" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Autor</label>
                            <input type="text" class="form-control" name="autor_solicitado">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Materia/Asignatura *</label>
                            <input type="text" class="form-control" name="materia" required 
                                   placeholder="Ej: Matemática, Programación, etc.">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Justificación</label>
                            <textarea class="form-control" name="justificacion" rows="3" 
                                      placeholder="¿Por qué necesitas este libro?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" <?php echo !$estudiante_actual ? 'disabled' : ''; ?>>
                            Enviar Solicitud
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
    <script>
    // Búsqueda y filtros en tiempo real
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const bookItems = document.querySelectorAll('.book-item');
        
        function filterBooks() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedCategory = categoryFilter.value;
            
            bookItems.forEach(item => {
                const title = item.dataset.title;
                const author = item.dataset.author;
                const category = item.dataset.category;
                
                const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm);
                const matchesCategory = !selectedCategory || category === selectedCategory;
                
                item.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
            });
        }
        
        searchInput.addEventListener('input', filterBooks);
        categoryFilter.addEventListener('change', filterBooks);
    });
    </script>
</body>
</html>