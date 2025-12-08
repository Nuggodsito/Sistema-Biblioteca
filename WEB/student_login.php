<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $cip = trim($_POST['cip_identificacion']);
    
    try {
        $db = new Database();
        $query = "SELECT * FROM estudiantes WHERE cip_identificacion = :cip AND email = :email";
        $stmt = $db->conn->prepare($query);
        $stmt->bindParam(':cip', $cip);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($estudiante) {
            $_SESSION['student_id'] = $estudiante['id'];
            $_SESSION['student_name'] = $estudiante['primer_nombre'] . ' ' . $estudiante['primer_apellido'];
            $_SESSION['student_cip'] = $estudiante['cip_identificacion'];
            $_SESSION['student_logged_in'] = true;
            
            header('Location: catalog.php');
            exit;
        } else {
            $error = 'CIP/Identificación o email incorrectos';
        }
    } catch (PDOException $e) {
        $error = 'Error al iniciar sesión';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Estudiante - Sistema de Biblioteca</title>
    <link href="<?php echo BOOTSTRAP_CSS; ?>" rel="stylesheet">
    <link href="<?php echo FONTAWESOME; ?>" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="card login-card">
                        <div class="card-header bg-primary text-white text-center">
                            <h4><i class="fas fa-user-graduate"></i> Login Estudiante</h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">CIP/Identificación</label>
                                    <input type="text" class="form-control" name="cip_identificacion" required 
                                           placeholder="Ej: CIP2024001">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required 
                                           placeholder="tu.email@ejemplo.com">
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-sign-in-alt"></i> Ingresar
                                </button>
                            </form>
                            
                            <div class="text-center mt-3">
                                <a href="../index.php" class="text-muted">
                                    <i class="fas fa-arrow-left"></i> Volver al inicio
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
</body>
</html>