<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/validator.php';

if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

require_once '../classes/User.php';
$userModel = new User();

$message = '';
$messageType = '';

// Obtener usuario actual
$usuario_actual = $userModel->getById($_SESSION['user_id']);

// Cambiar contraseña
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'];
    $nueva_password = $_POST['nueva_password'];
    $confirmar_password = $_POST['confirmar_password'];
    
    try {
        if (!password_verify($password_actual, $usuario_actual['password'])) {
            throw new Exception('La contraseña actual es incorrecta');
        }
        
        if ($nueva_password !== $confirmar_password) {
            throw new Exception('Las contraseñas no coinciden');
        }
        
        if (!Validator::validatePassword($nueva_password)) {
            throw new Exception('La contraseña debe tener mínimo 8 caracteres, con mayúsculas, minúsculas y números');
        }
        
        // CORRECCIÓN: Usar un método de la clase User en lugar de acceder directamente a $conn
        if ($userModel->updatePassword($_SESSION['user_id'], $nueva_password)) {
            $message = 'Contraseña actualizada exitosamente';
            $messageType = 'success';
        } else {
            throw new Exception('Error al actualizar la contraseña');
        }
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Sistema de Biblioteca</title>
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
                    <h1 class="h2">Mi Perfil</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Información Personal</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Usuario:</strong> <?php echo $usuario_actual['username']; ?></p>
                                <p><strong>Email:</strong> <?php echo $usuario_actual['email']; ?></p>
                                <p><strong>Nombre:</strong> <?php echo $usuario_actual['primer_nombre'] . ' ' . $usuario_actual['primer_apellido']; ?></p>
                                <p><strong>Rol:</strong> <?php echo $_SESSION['role']; ?></p>
                                <p><strong>Fecha Registro:</strong> <?php echo formatDate($usuario_actual['created_at']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Cambiar Contraseña</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="cambiar_password" value="1">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Contraseña Actual</label>
                                        <input type="password" class="form-control" name="password_actual" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nueva Contraseña</label>
                                        <input type="password" class="form-control" name="nueva_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Confirmar Contraseña</label>
                                        <input type="password" class="form-control" name="confirmar_password" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                                </form>
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