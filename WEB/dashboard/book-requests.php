<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

require_once '../classes/BookRequest.php';
require_once '../classes/Student.php';

$requestModel = new BookRequest();
$studentModel = new Student();

$message = '';
$messageType = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'update_status':
                // CORRECCIÓN: Verificar si es estudiante (solo admin puede cambiar estados)
                if (Auth::isEstudiante()) {
                    throw new Exception('No tienes permisos para esta acción');
                }
                
                $id = $_POST['id'];
                $estado = $_POST['estado'];
                
                if ($requestModel->updateStatus($id, $estado)) {
                    $message = 'Estado actualizado exitosamente';
                    $messageType = 'success';
                }
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Obtener datos según el rol
if (Auth::isEstudiante()) {
    // Para estudiantes: solo sus propias solicitudes
    $estudiante_actual = $studentModel->getByUserId($_SESSION['user_id']);
    if ($estudiante_actual) {
        $solicitudes = $requestModel->getByStudentId($estudiante_actual['id']);
        $solicitudes_pendientes = $requestModel->getPendingRequestsByStudent($estudiante_actual['id']);
    } else {
        $solicitudes = [];
        $solicitudes_pendientes = [];
    }
} else {
    // Para administradores: todas las solicitudes
    $solicitudes = $requestModel->getAllWithStudentInfo();
    $solicitudes_pendientes = $requestModel->getPendingRequests();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de Libros - Sistema de Biblioteca</title>
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
                    <h1 class="h2">
                        <?php echo Auth::isEstudiante() ? 'Mis Solicitudes de Libros' : 'Solicitudes de Libros'; ?>
                    </h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Solo mostrar sección de pendientes para administradores -->
                <?php if (!Auth::isEstudiante() && !empty($solicitudes_pendientes)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-circle"></i>
                            Solicitudes Pendientes (<?php echo count($solicitudes_pendientes); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($solicitudes_pendientes as $solicitud): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($solicitud['titulo_solicitado']); ?></h6>
                                        <p class="card-text">
                                            <strong>Autor:</strong> <?php echo htmlspecialchars($solicitud['autor_solicitado']); ?><br>
                                            <strong>Materia:</strong> <?php echo htmlspecialchars($solicitud['materia']); ?><br>
                                            <strong>Estudiante:</strong> <?php echo htmlspecialchars($solicitud['estudiante_nombre']); ?><br>
                                            <strong>CIP:</strong> <?php echo $solicitud['estudiante_cip']; ?>
                                        </p>
                                        <?php if ($solicitud['justificacion']): ?>
                                            <p class="card-text">
                                                <strong>Justificación:</strong><br>
                                                <em><?php echo htmlspecialchars($solicitud['justificacion']); ?></em>
                                            </p>
                                        <?php endif; ?>
                                        <div class="mt-3">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="id" value="<?php echo $solicitud['id']; ?>">
                                                <input type="hidden" name="estado" value="atendida">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Marcar como Atendida
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="id" value="<?php echo $solicitud['id']; ?>">
                                                <input type="hidden" name="estado" value="rechazada">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('¿Rechazar esta solicitud?')">
                                                    <i class="fas fa-times"></i> Rechazar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted">
                                        <small>Solicitado: <?php echo formatDate($solicitud['created_at']); ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Historial de Solicitudes -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?php echo Auth::isEstudiante() ? 'Mi Historial de Solicitudes' : 'Historial de Solicitudes'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Título Solicitado</th>
                                        <th>Autor</th>
                                        <th>Materia</th>
                                        <?php if (!Auth::isEstudiante()): ?>
                                            <th>Estudiante</th>
                                        <?php endif; ?>
                                        <th>Fecha Solicitud</th>
                                        <th>Estado</th>
                                        <?php if (!Auth::isEstudiante()): ?>
                                            <th>Acciones</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($solicitudes)): ?>
                                        <tr>
                                            <td colspan="<?php echo Auth::isEstudiante() ? '5' : '7'; ?>" class="text-center text-muted">
                                                No hay solicitudes registradas.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($solicitudes as $solicitud): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($solicitud['titulo_solicitado']); ?></td>
                                            <td><?php echo htmlspecialchars($solicitud['autor_solicitado']); ?></td>
                                            <td><?php echo htmlspecialchars($solicitud['materia']); ?></td>
                                            <?php if (!Auth::isEstudiante()): ?>
                                                <td>
                                                    <?php echo htmlspecialchars($solicitud['estudiante_nombre']); ?><br>
                                                    <small class="text-muted"><?php echo $solicitud['estudiante_cip']; ?></small>
                                                </td>
                                            <?php endif; ?>
                                            <td><?php echo formatDate($solicitud['created_at']); ?></td>
                                            <td>
                                                <?php
                                                $estado_class = [
                                                    'pendiente' => 'warning',
                                                    'atendida' => 'success',
                                                    'rechazada' => 'danger'
                                                ];
                                                $estado_text = [
                                                    'pendiente' => 'Pendiente',
                                                    'atendida' => 'Atendida',
                                                    'rechazada' => 'Rechazada'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $estado_class[$solicitud['estado']]; ?>">
                                                    <?php echo $estado_text[$solicitud['estado']]; ?>
                                                </span>
                                            </td>
                                            <?php if (!Auth::isEstudiante()): ?>
                                                <td>
                                                    <?php if ($solicitud['estado'] == 'pendiente'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="id" value="<?php echo $solicitud['id']; ?>">
                                                            <input type="hidden" name="estado" value="atendida">
                                                            <button type="submit" class="btn btn-success btn-sm">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="update_status">
                                                            <input type="hidden" name="id" value="<?php echo $solicitud['id']; ?>">
                                                            <input type="hidden" name="estado" value="rechazada">
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
</body>
</html>