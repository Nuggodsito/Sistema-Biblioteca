<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!Auth::isLoggedIn() || !Auth::hasPermission('reportes')) {
    header('Location: ../login.php');
    exit;
}

require_once '../classes/Reservation.php';
require_once '../classes/Book.php';
require_once '../classes/ReportGenerator.php';

$reservationModel = new Reservation();
$bookModel = new Book();

// Array de meses en español para traducción
$meses_es = [
    'January' => 'Enero',
    'February' => 'Febrero',
    'March' => 'Marzo',
    'April' => 'Abril',
    'May' => 'Mayo',
    'June' => 'Junio',
    'July' => 'Julio',
    'August' => 'Agosto',
    'September' => 'Septiembre',
    'October' => 'Octubre',
    'November' => 'Noviembre',
    'December' => 'Diciembre'
];

// Traducir el mes actual
$mes_ingles = date('F');
$mes_espanol = $meses_es[$mes_ingles] ?? $mes_ingles;
$mes_actual_es = $mes_espanol . ' ' . date('Y');

// Procesar generación de reportes
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'reporte_libros':
            $libros = $bookModel->getAllWithCategory();
            ReportGenerator::generateBooksReport($libros);
            break;
            
        case 'reporte_reservas':
            $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
            $fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-t');
            $reservas = $reservationModel->getByDateRange($fecha_inicio, $fecha_fin);
            ReportGenerator::generateReservationsReport($reservas);
            break;
            
        case 'estadisticas_libros':
            $fecha_inicio = $_POST['fecha_inicio_est'] ?? date('Y-m-01');
            $fecha_fin = $_POST['fecha_fin_est'] ?? date('Y-m-t');
            $estadisticas = $reservationModel->getMostReservedBooks(20, $fecha_inicio, $fecha_fin);
            $periodo = date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin));
            ReportGenerator::generateStatisticsReport($estadisticas, $periodo);
            break;
    }
}

// Obtener estadísticas para mostrar
$fecha_inicio_mes = date('Y-m-01');
$fecha_fin_mes = date('Y-m-t');
$libros_populares = $reservationModel->getMostReservedBooks(10, $fecha_inicio_mes, $fecha_fin_mes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Estadísticas - Sistema de Biblioteca</title>
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
                    <h1 class="h2">Reportes y Estadísticas</h1>
                </div>

                <div class="row">
                    <!-- Generar Reportes -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Generar Reportes</h5>
                            </div>
                            <div class="card-body">
                                <!-- Reporte de Libros -->
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="action" value="reporte_libros">
                                    <h6>Reporte de Libros</h6>
                                    <p class="text-muted">Genera un reporte completo de todos los libros en el sistema.</p>
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-file-excel"></i> Descargar Reporte de Libros
                                    </button>
                                </form>
                                
                                <hr>
                                
                                <!-- Reporte de Reservas -->
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="action" value="reporte_reservas">
                                    <h6>Reporte de Reservas por Fecha</h6>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Fecha Inicio</label>
                                            <input type="date" name="fecha_inicio" class="form-control" 
                                                   value="<?php echo date('Y-m-01'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Fecha Fin</label>
                                            <input type="date" name="fecha_fin" class="form-control" 
                                                   value="<?php echo date('Y-m-t'); ?>">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        <i class="fas fa-file-excel"></i> Descargar Reporte de Reservas
                                    </button>
                                </form>
                                
                                <hr>
                                
                                <!-- Estadísticas de Libros -->
                                <form method="POST">
                                    <input type="hidden" name="action" value="estadisticas_libros">
                                    <h6>Estadísticas de Libros Más Utilizados</h6>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Periodo Inicio</label>
                                            <input type="date" name="fecha_inicio_est" class="form-control" 
                                                   value="<?php echo date('Y-m-01'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Periodo Fin</label>
                                            <input type="date" name="fecha_fin_est" class="form-control" 
                                                   value="<?php echo date('Y-m-t'); ?>">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-outline-info w-100">
                                        <i class="fas fa-chart-bar"></i> Descargar Estadísticas
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Estadísticas del Mes -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    Estadísticas del Mes (<?php echo $mes_actual_es; ?>)
                                </h5>
                            </div>
                            <div class="card-body">
                                <h6>Libros Más Reservados</h6>
                                <?php if (empty($libros_populares)): ?>
                                    <p class="text-muted">No hay datos de reservas este mes.</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($libros_populares as $index => $libro): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($libro['titulo'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($libro['autor'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary rounded-pill"><?php echo $libro['total_reservas']; ?></span>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($libro['categoria'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <hr>
                                
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border rounded p-2">
                                            <h4 class="text-primary mb-0">
                                                <?php
                                                $reservas_mes = $reservationModel->getByDateRange($fecha_inicio_mes, $fecha_fin_mes);
                                                echo count($reservas_mes);
                                                ?>
                                            </h4>
                                            <small class="text-muted">Total Reservas</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-2">
                                            <h4 class="text-success mb-0">
                                                <?php
                                                $reservas_activas = $reservationModel->getActiveReservations();
                                                echo count($reservas_activas);
                                                ?>
                                            </h4>
                                            <small class="text-muted">Reservas Activas</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
</body>
</html>