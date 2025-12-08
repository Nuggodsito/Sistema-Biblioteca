<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/validator.php';

if (!Auth::isLoggedIn() || !Auth::hasPermission('usuarios')) {
    header('Location: ../login.php');
    exit;
}

require_once '../classes/User.php';
require_once '../classes/Role.php';

$userModel = new User();
$roleModel = new Role();

$message = '';
$messageType = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $data = [
                    'username' => Validator::sanitize($_POST['username']),
                    'email' => Validator::sanitize($_POST['email']),
                    'password' => $_POST['password'],
                    'primer_nombre' => Validator::sanitize($_POST['primer_nombre']),
                    'segundo_nombre' => Validator::sanitize($_POST['segundo_nombre']),
                    'primer_apellido' => Validator::sanitize($_POST['primer_apellido']),
                    'segundo_apellido' => Validator::sanitize($_POST['segundo_apellido']),
                    'rol_id' => $_POST['rol_id']
                ];
                
                if (!Validator::validateEmail($data['email'])) {
                    throw new Exception('Email no válido');
                }
                
                if ($userModel->usernameExists($data['username'])) {
                    throw new Exception('El nombre de usuario ya existe');
                }
                
                if ($userModel->create($data)) {
                    $message = 'Usuario creado exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $data = [
                    'username' => Validator::sanitize($_POST['username']),
                    'email' => Validator::sanitize($_POST['email']),
                    'primer_nombre' => Validator::sanitize($_POST['primer_nombre']),
                    'segundo_nombre' => Validator::sanitize($_POST['segundo_nombre']),
                    'primer_apellido' => Validator::sanitize($_POST['primer_apellido']),
                    'segundo_apellido' => Validator::sanitize($_POST['segundo_apellido']),
                    'rol_id' => $_POST['rol_id']
                ];
                
                if (!Validator::validateEmail($data['email'])) {
                    throw new Exception('Email no válido');
                }
                
                if ($userModel->usernameExists($data['username'], $id)) {
                    throw new Exception('El nombre de usuario ya existe');
                }
                
                if ($userModel->update($id, $data)) {
                    $message = 'Usuario actualizado exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                if ($userModel->delete($id)) {
                    $message = 'Usuario eliminado exitosamente';
                    $messageType = 'success';
                }
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Obtener datos
$users = $userModel->getUsersWithRole();
$roles = $roleModel->getAllRoles();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema de Biblioteca</title>
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
                    <h1 class="h2">Gestión de Usuarios</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Nombre Completo</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($user['primer_nombre'] . ' ' . $user['primer_apellido']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $user['rol_nombre']; ?></span>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-user" 
                                                    data-user='<?php echo json_encode($user); ?>'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este usuario?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

    <!-- Modal para agregar/editar usuario -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="userForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="userId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Usuario *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="primer_nombre" class="form-label">Primer Nombre *</label>
                                <input type="text" class="form-control" id="primer_nombre" name="primer_nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="segundo_nombre" class="form-label">Segundo Nombre</label>
                                <input type="text" class="form-control" id="segundo_nombre" name="segundo_nombre">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="primer_apellido" class="form-label">Primer Apellido *</label>
                                <input type="text" class="form-control" id="primer_apellido" name="primer_apellido" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="segundo_apellido" class="form-label">Segundo Apellido</label>
                                <input type="text" class="form-control" id="segundo_apellido" name="segundo_apellido">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rol_id" class="form-label">Rol *</label>
                            <select class="form-select" id="rol_id" name="rol_id" required>
                                <option value="">Seleccionar Rol</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>"><?php echo $role['nombre']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="passwordField">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Mínimo 8 caracteres, con mayúsculas, minúsculas y números</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BOOTSTRAP_JS; ?>"></script>
    <script>
    // Script para el modal de usuarios
    document.addEventListener('DOMContentLoaded', function() {
        const userModal = new bootstrap.Modal(document.getElementById('userModal'));
        const form = document.getElementById('userForm');
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const userId = document.getElementById('userId');
        const passwordField = document.getElementById('passwordField');
        const passwordInput = document.getElementById('password');
        
        // Botones de editar
        document.querySelectorAll('.edit-user').forEach(button => {
            button.addEventListener('click', function() {
                const user = JSON.parse(this.dataset.user);
                
                modalTitle.textContent = 'Editar Usuario';
                formAction.value = 'update';
                userId.value = user.id;
                
                document.getElementById('username').value = user.username;
                document.getElementById('email').value = user.email;
                document.getElementById('primer_nombre').value = user.primer_nombre;
                document.getElementById('segundo_nombre').value = user.segundo_nombre || '';
                document.getElementById('primer_apellido').value = user.primer_apellido;
                document.getElementById('segundo_apellido').value = user.segundo_apellido || '';
                document.getElementById('rol_id').value = user.rol_id;
                
                // Ocultar campo de contraseña en edición
                passwordField.style.display = 'none';
                passwordInput.removeAttribute('required');
                
                userModal.show();
            });
        });
        
        // Botón nuevo usuario
        document.querySelector('[data-bs-target="#userModal"]').addEventListener('click', function() {
            modalTitle.textContent = 'Nuevo Usuario';
            formAction.value = 'create';
            form.reset();
            userId.value = '';
            
            // Mostrar campo de contraseña
            passwordField.style.display = 'block';
            passwordInput.setAttribute('required', 'required');
        });
        
        // Validación de formulario
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            if (formAction.value === 'create' && !isValidPassword(password)) {
                e.preventDefault();
                alert('La contraseña debe tener mínimo 8 caracteres, con mayúsculas, minúsculas y números');
            }
        });
        
        function isValidPassword(password) {
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(password);
        }
    });
    </script>
</body>
</html>