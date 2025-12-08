<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$auth = new Auth();

// Si ya está logueado, redirigir al dashboard
if (Auth::isLoggedIn()) {
    header('Location: dashboard/');
    exit;
}

// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = Validator::sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if ($auth->login($username, $password)) {
        header('Location: dashboard/');
        exit;
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
    <title>Login - Sistema de Biblioteca</title>
    <link href="<?php echo BOOTSTRAP_CSS; ?>" rel="stylesheet">
    <link href="<?php echo FONTAWESOME; ?>" rel="stylesheet">
</head>
<body class="login-body">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <h4 class="mb-0">Sistema de Biblioteca</h4>
                        <small>Iniciar Sesión</small>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       required autofocus value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
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
                                ¿Problemas para acceder? Contacta al administrador
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="public/" class="btn btn-outline-secondary btn-sm">
                        Acceso Público para Estudiantes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
</body>
</html>