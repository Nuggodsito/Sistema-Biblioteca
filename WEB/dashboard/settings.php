<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Sistema de Biblioteca</title>
    <link href="<?php echo BOOTSTRAP_CSS; ?>" rel="stylesheet">
    <link href="<?php echo FONTAWESOME; ?>" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Configuración</h1>
                </div>

                <div class="alert alert-info">
                    <h5>Configuración del Sistema</h5>
                    <p class="mb-0">Esta sección está en desarrollo. Aquí podrás configurar los parámetros del sistema.</p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5>Información del Sistema</h5>
                        <ul class="list-unstyled">
                            <li><strong>Versión:</strong> <?php echo APP_VERSION; ?></li>
                            <li><strong>Nombre:</strong> <?php echo APP_NAME; ?></li>
                            <li><strong>URL Base:</strong> <?php echo BASE_URL; ?></li>
                            <li><strong>Usuario:</strong> <?php echo $_SESSION['nombre_completo']; ?></li>
                            <li><strong>Rol:</strong> <?php echo $_SESSION['role']; ?></li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
</body>
</html>