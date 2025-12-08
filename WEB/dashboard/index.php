<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

require_once '../classes/Book.php';
require_once '../classes/Student.php';
require_once '../classes/Reservation.php';
require_once '../classes/User.php';

$bookModel = new Book();
$studentModel = new Student();
$reservationModel = new Reservation();
$userModel = new User();

if (Auth::isEstudiante()) {
    // VISTA PARA ESTUDIANTES - Solo información personal
    $estudiante_actual = $studentModel->getByUserId($_SESSION['user_id']);
    $mis_reservas_activas = [];
    $mis_reservas_totales = [];
    
    if ($estudiante_actual) {
        $todas_reservas = $reservationModel->getAllWithDetails();
        foreach ($todas_reservas as $reserva) {
            if ($reserva['estudiante_id'] == $estudiante_actual['id']) {
                $mis_reservas_totales[] = $reserva;
                if ($reserva['estado'] == 'reservado') {
                    $mis_reservas_activas[] = $reserva;
                }
            }
        }
    }
    
    $libros_disponibles = $bookModel->getAvailableBooks();
    
} else {
    // VISTA PARA ADMINISTRADORES 
    $totalLibros = $bookModel->count();
    $totalEstudiantes = $studentModel->count();
    $reservasActivas = count($reservationModel->getActiveReservations());
    $totalUsuarios = $userModel->count();

    // Libros más reservados (último mes)
    $fechaInicio = date('Y-m-01');
    $fechaFin = date('Y-m-t');
    $librosPopulares = $reservationModel->getMostReservedBooks(5, $fechaInicio, $fechaFin);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Biblioteca</title>
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="text-muted">Bienvenido, <?php echo $_SESSION['nombre_completo']; ?></span>
                    </div>
                </div>

                <?php if (Auth::isEstudiante()): ?>
                    <!-- DASHBOARD PARA ESTUDIANTES -->
                    <div class="row">
                        <!-- Bienvenida -->
                        <div class="col-12 mb-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h4 class="card-title">
                                        <i class="fas fa-user-graduate"></i>
                                        Bienvenido, <?php echo $estudiante_actual ? $estudiante_actual['primer_nombre'] : 'Estudiante'; ?>
                                    </h4>
                                    <p class="card-text">Sistema de Reservas de Biblioteca</p>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas Personales -->
                        <div class="col-md-3 mb-4">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo count($libros_disponibles); ?></h4>
                                            <p class="mb-0">Libros Disponibles</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-book fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo count($mis_reservas_activas); ?></h4>
                                            <p class="mb-0">Mis Reservas Activas</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clipboard-list fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card text-white bg-info">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo count($mis_reservas_totales); ?></h4>
                                            <p class="mb-0">Total de Mis Reservas</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-history fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones Rápidas para Estudiantes -->
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Acciones Rápidas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <a href="../public/catalog.php" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-book"></i><br>
                                                <small>Ver Catálogo</small>
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <a href="../public/catalog.php" class="btn btn-outline-success w-100">
                                                <i class="fas fa-clipboard-list"></i><br>
                                                <small>Mis Reservas</small>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mis Reservas Activas -->
                        <?php if (!empty($mis_reservas_activas)): ?>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Mis Reservas Activas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Libro</th>
                                                    <th>Fecha Reserva</th>
                                                    <th>Fecha Devolución</th>
                                                    <th>Días Restantes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mis_reservas_activas as $reserva): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($reserva['libro_titulo']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($reserva['libro_autor']); ?></small>
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
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                <?php else: ?>
                    <!-- DASHBOARD PARA ADMINISTRADORES (código original) -->
                    <div class="row">
                        <!-- Estadísticas -->
                        <div class="col-md-3 mb-4">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $totalLibros; ?></h4>
                                            <p class="mb-0">Total Libros</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-book fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $totalEstudiantes; ?></h4>
                                            <p class="mb-0">Estudiantes</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card text-white bg-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $reservasActivas; ?></h4>
                                            <p class="mb-0">Reservas Activas</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clipboard-list fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <div class="card text-white bg-info">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $totalUsuarios; ?></h4>
                                            <p class="mb-0">Usuarios</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-cog fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Libros más populares -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Libros Más Populares</h5>
                                    <small class="text-muted"><?php echo date('F Y'); ?></small>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($librosPopulares)): ?>
                                        <p class="text-muted">No hay datos de reservas este mes.</p>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($librosPopulares as $index => $libro): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo $libro['titulo']; ?></h6>
                                                        <small class="text-muted"><?php echo $libro['autor']; ?></small>
                                                    </div>
                                                    <span class="badge bg-primary rounded-pill"><?php echo $libro['total_reservas']; ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Acciones rápidas -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Acciones Rápidas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-2">
                                        <?php if (Auth::hasPermission('libros')): ?>
                                        <div class="col-6">
                                            <a href="books.php" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-book"></i><br>
                                                <small>Gestionar Libros</small>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (Auth::hasPermission('reservas')): ?>
                                        <div class="col-6">
                                            <a href="reservations.php" class="btn btn-outline-success w-100">
                                                <i class="fas fa-clipboard-list"></i><br>
                                                <small>Ver Reservas</small>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (Auth::hasPermission('estudiantes')): ?>
                                        <div class="col-6">
                                            <a href="students.php" class="btn btn-outline-info w-100">
                                                <i class="fas fa-users"></i><br>
                                                <small>Estudiantes</small>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (Auth::hasPermission('reportes')): ?>
                                        <div class="col-6">
                                            <a href="reports.php" class="btn btn-outline-warning w-100">
                                                <i class="fas fa-chart-bar"></i><br>
                                                <small>Reportes</small>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
</body>
</html>