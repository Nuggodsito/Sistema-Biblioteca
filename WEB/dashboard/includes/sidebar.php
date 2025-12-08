<?php
// Sidebar del dashboard
require_once '../classes/Reservation.php';
require_once '../classes/BookRequest.php';
?>
<div class="col-md-3 col-lg-2 bg-light sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            
            <?php if (Auth::hasPermission('usuarios') || Auth::isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users-cog"></i>
                    Usuarios
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (Auth::hasPermission('estudiantes')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>" href="students.php">
                    <i class="fas fa-user-graduate"></i>
                    Estudiantes
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (Auth::hasPermission('libros')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                    <i class="fas fa-tags"></i>
                    Categorías
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>" href="books.php">
                    <i class="fas fa-book"></i>
                    Libros
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (Auth::hasPermission('reservas')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>" href="reservations.php">
                    <i class="fas fa-clipboard-list"></i>
                    Reservas
                    <?php
                    $reservationModel = new Reservation();
                    $reservasActivas = count($reservationModel->getActiveReservations());
                    if ($reservasActivas > 0): ?>
                        <span class="badge bg-danger"><?php echo $reservasActivas; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (Auth::hasPermission('reportes')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    Reportes
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'book-requests.php' ? 'active' : ''; ?>" href="book-requests.php">
                    <i class="fas fa-clipboard-check"></i>
                    Solicitudes
                    <?php
                    $requestModel = new BookRequest();
                    $solicitudesPendientes = count($requestModel->getPendingRequests());
                    if ($solicitudesPendientes > 0): ?>
                        <span class="badge bg-warning"><?php echo $solicitudesPendientes; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
        
        <hr>
        
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                    <i class="fas fa-user"></i>
                    Mi Perfil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cog"></i>
                    Configuración
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../public/" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    Vista Pública
                </a>
            </li>
        </ul>
    </div>
</div>