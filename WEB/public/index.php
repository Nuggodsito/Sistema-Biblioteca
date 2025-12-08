<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Si ya está logueado, redirigir al catálogo
if (Auth::isLoggedIn() && Auth::isEstudiante()) {
    header('Location: catalog.php');
    exit;
}

// Login para estudiantes
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = Validator::sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $auth = new Auth();
    if ($auth->login($username, $password)) {
        if (Auth::isEstudiante()) {
            header('Location: catalog.php');
            exit;
        } else {
            $error = 'Acceso solo para estudiantes';
            Auth::logout();
        }
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Estudiantes - Biblioteca Virtual</title>
    <link href="<?php echo BOOTSTRAP_CSS; ?>" rel="stylesheet">
    <link href="<?php echo FONTAWESOME; ?>" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
    <style>
        .public-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="public-header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Biblioteca Virtual</h1>
            <p class="lead">Sistema de Reservas para Estudiantes</p>
        </div>
    </header>

    <div class="container">
        <div class="row justify-content-center">
            <!-- Login Form -->
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Acceso Estudiantes</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       required autofocus>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Ingresar</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                ¿Problemas para acceder? Contacta a la biblioteca
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="../dashboard/" class="btn btn-outline-secondary btn-sm">
                        Acceso Administrativo
                    </a>
                </div>
            </div>
            
            <!-- Features -->
            <div class="col-md-7">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-search fa-3x text-primary mb-3"></i>
                                <h5>Catálogo Completo</h5>
                                <p class="text-muted">Busca entre todos los libros disponibles en la biblioteca</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-3x text-success mb-3"></i>
                                <h5>Reserva Online</h5>
                                <p class="text-muted">Reserva tus libros favoritos desde cualquier lugar</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-history fa-3x text-info mb-3"></i>
                                <h5>Historial</h5>
                                <p class="text-muted">Revisa tu historial de reservas y préstamos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card feature-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                                <h5>Disponibilidad</h5>
                                <p class="text-muted">Consulta la disponibilidad de libros en tiempo real</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 Biblioteca Virtual. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
</body>
</html>