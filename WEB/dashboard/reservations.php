<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!Auth::isLoggedIn() || !Auth::hasPermission('reservas')) {
    header('Location: ../login.php');
    exit;
}

require_once '../classes/Reservation.php';
require_once '../classes/Book.php';
require_once '../classes/Student.php';
require_once '../classes/User.php';

$reservationModel = new Reservation();
$bookModel = new Book();
$studentModel = new Student();
$userModel = new User();

$message = '';
$messageType = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $data = [
                    'libro_id' => $_POST['libro_id'],
                    'estudiante_id' => $_POST['estudiante_id'],
                    'usuario_reserva_id' => $_SESSION['user_id'],
                    'dias_reserva' => $_POST['dias_reserva'],
                    'fecha_devolucion_estimada' => date('Y-m-d', strtotime("+{$_POST['dias_reserva']} days"))
                ];
                
                // Verificar disponibilidad
                $book = $bookModel->getById($data['libro_id']);
                if (!$book || $book['existencias'] <= 0) {
                    throw new Exception('El libro no está disponible');
                }
                
                // Verificar si ya tiene reserva activa
                if ($reservationModel->hasActiveReservation($data['estudiante_id'], $data['libro_id'])) {
                    throw new Exception('El estudiante ya tiene una reserva activa de este libro');
                }
                
                if ($reservationModel->create($data)) {
                    // Disminuir existencias
                    $bookModel->updateStock($data['libro_id'], -1);
                    
                    $message = 'Reserva creada exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'return':
                $reserva_id = $_POST['reserva_id'];
                $reserva = $reservationModel->getById($reserva_id);
                
                if ($reserva && $reserva['estado'] == 'reservado') {
                    if ($reservationModel->returnBook($reserva_id)) {
                        // Aumentar existencias
                        $bookModel->updateStock($reserva['libro_id'], 1);
                        
                        $message = 'Libro devuelto exitosamente';
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
$reservas = $reservationModel->getAllWithDetails();
$reservas_activas = $reservationModel->getActiveReservations();
$libros_disponibles = $bookModel->getAvailableBooks();
$estudiantes = $studentModel->getAllWithDetails();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservas - Sistema de Biblioteca</title>
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
                    <h1 class="h2">Gestión de Reservas</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reservationModal">
                        <i class="fas fa-plus"></i> Nueva Reserva
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Reservas Activas -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            Reservas Activas (<?php echo count($reservas_activas); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($reservas_activas)): ?>
                            <p class="text-muted">No hay reservas activas en este momento.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Libro</th>
                                            <th>Estudiante</th>
                                            <th>Fecha Reserva</th>
                                            <th>Fecha Devolución</th>
                                            <th>Días Restantes</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reservas_activas as $reserva): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($reserva['libro_titulo']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($reserva['libro_autor']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($reserva['estudiante_nombre']); ?><br>
                                                <small class="text-muted"><?php echo $reserva['estudiante_cip']; ?></small>
                                            </td>
                                            <td><?php echo formatDate($reserva['fecha_reserva']); ?></td>
                                            <td><?php echo formatDate($reserva['fecha_devolucion_estimada']); ?></td>
                                            <td>
                                                <?php
                                                $hoy = new DateTime();
                                                $devolucion = new DateTime($reserva['fecha_devolucion_estimada']);
                                                $dias_restantes = $hoy->diff($devolucion)->days;
                                                $dias_restantes = $devolucion > $hoy ? $dias_restantes : -$dias_restantes;
                                                
                                                if ($dias_restantes > 0) {
                                                    echo "<span class='badge bg-success'>$dias_restantes días</span>";
                                                } else {
                                                    echo "<span class='badge bg-danger'>Vencido</span>";
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('¿Registrar devolución de este libro?')">
                                                    <input type="hidden" name="action" value="return">
                                                    <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-undo"></i> Devolver
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Historial de Reservas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Historial de Reservas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Libro</th>
                                        <th>Estudiante</th>
                                        <th>Fecha Reserva</th>
                                        <th>Fecha Devolución</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservas as $reserva): ?>
                                    <tr>
                                        <td><?php echo $reserva['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($reserva['libro_titulo']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($reserva['libro_autor']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($reserva['estudiante_nombre']); ?><br>
                                            <small class="text-muted"><?php echo $reserva['estudiante_cip']; ?></small>
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
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para nueva reserva -->
    <div class="modal fade" id="reservationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="reservationForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Nueva Reserva</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-3">
                            <label for="estudiante_id" class="form-label">Estudiante *</label>
                            <select class="form-select" id="estudiante_id" name="estudiante_id" required>
                                <option value="">Seleccionar Estudiante</option>
                                <?php foreach ($estudiantes as $estudiante): ?>
                                    <option value="<?php echo $estudiante['id']; ?>">
                                        <?php echo htmlspecialchars($estudiante['nombre_completo'] . ' - ' . $estudiante['cip_identificacion']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="libro_id" class="form-label">Libro *</label>
                            <select class="form-select" id="libro_id" name="libro_id" required>
                                <option value="">Seleccionar Libro</option>
                                <?php foreach ($libros_disponibles as $libro): ?>
                                    <option value="<?php echo $libro['id']; ?>" data-existencias="<?php echo $libro['existencias']; ?>">
                                        <?php echo htmlspecialchars($libro['titulo'] . ' - ' . $libro['autor'] . ' (' . $libro['existencias'] . ' disp.)'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text" id="libroInfo"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="dias_reserva" class="form-label">Días de Préstamo *</label>
                            <select class="form-select" id="dias_reserva" name="dias_reserva" required>
                                <option value="7">7 días</option>
                                <option value="14">14 días</option>
                                <option value="21">21 días</option>
                                <option value="30">30 días</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <small>
                                <strong>Fecha estimada de devolución:</strong><br>
                                <span id="fechaDevolucion"><?php echo date('d/m/Y', strtotime('+7 days')); ?></span>
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Reserva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const libroSelect = document.getElementById('libro_id');
        const libroInfo = document.getElementById('libroInfo');
        const diasReserva = document.getElementById('dias_reserva');
        const fechaDevolucion = document.getElementById('fechaDevolucion');
        
        // Actualizar información del libro seleccionado
        libroSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const existencias = selectedOption.dataset.existencias;
            
            if (existencias) {
                libroInfo.textContent = 'Existencias disponibles: ' + existencias;
                libroInfo.className = 'form-text text-success';
            } else {
                libroInfo.textContent = '';
            }
        });
        
        // Calcular fecha de devolución
        function calcularFechaDevolucion() {
            const dias = parseInt(diasReserva.value);
            const fecha = new Date();
            fecha.setDate(fecha.getDate() + dias);
            
            const dia = fecha.getDate().toString().padStart(2, '0');
            const mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
            const año = fecha.getFullYear();
            
            fechaDevolucion.textContent = `${dia}/${mes}/${año}`;
        }
        
        diasReserva.addEventListener('change', calcularFechaDevolucion);
        calcularFechaDevolucion(); // Inicializar
    });
    </script>
</body>
</html>