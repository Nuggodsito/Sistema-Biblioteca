<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/validator.php'; 

if (!Auth::isLoggedIn() || !Auth::hasPermission('estudiantes')) {
    header('Location: ../login.php');
    exit;
}

require_once '../classes/Student.php';
require_once '../classes/User.php'; 

$studentModel = new Student();
$userModel = new User(); 

$message = '';
$messageType = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $data = [
                    'cip_identificacion' => Validator::sanitize($_POST['cip_identificacion']),
                    'primer_nombre' => Validator::sanitize($_POST['primer_nombre']),
                    'segundo_nombre' => Validator::sanitize($_POST['segundo_nombre']),
                    'primer_apellido' => Validator::sanitize($_POST['primer_apellido']),
                    'segundo_apellido' => Validator::sanitize($_POST['segundo_apellido']),
                    'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                    'carrera' => Validator::sanitize($_POST['carrera']),
                    'usuario_id' => $_POST['usuario_id'] ?? null 
                ];
                
                if (!Validator::validateCIP($data['cip_identificacion'])) {
                    throw new Exception('CIP/Identificación no válido');
                }
                
                if ($studentModel->cipExists($data['cip_identificacion'])) {
                    throw new Exception('El CIP/Identificación ya existe');
                }
                
                if (!Validator::validateDate($data['fecha_nacimiento'])) {
                    throw new Exception('Fecha de nacimiento no válida');
                }
                
                if ($studentModel->create($data)) {
                    $message = 'Estudiante creado exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $data = [
                    'cip_identificacion' => Validator::sanitize($_POST['cip_identificacion']),
                    'primer_nombre' => Validator::sanitize($_POST['primer_nombre']),
                    'segundo_nombre' => Validator::sanitize($_POST['segundo_nombre']),
                    'primer_apellido' => Validator::sanitize($_POST['primer_apellido']),
                    'segundo_apellido' => Validator::sanitize($_POST['segundo_apellido']),
                    'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                    'carrera' => Validator::sanitize($_POST['carrera']),
                    'usuario_id' => $_POST['usuario_id'] ?? null 
                ];
                
                if (!Validator::validateCIP($data['cip_identificacion'])) {
                    throw new Exception('CIP/Identificación no válido');
                }
                
                if ($studentModel->cipExists($data['cip_identificacion'], $id)) {
                    throw new Exception('El CIP/Identificación ya existe');
                }
                
                if (!Validator::validateDate($data['fecha_nacimiento'])) {
                    throw new Exception('Fecha de nacimiento no válida');
                }
                
                if ($studentModel->update($id, $data)) {
                    $message = 'Estudiante actualizado exitosamente';
                    $messageType = 'success';
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                if ($studentModel->delete($id)) {
                    $message = 'Estudiante eliminado exitosamente';
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
$students = $studentModel->getAllWithDetails();
$carreras = getCarreras();
$usuarios = $userModel->getAll(); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estudiantes - Sistema de Biblioteca</title>
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
                    <h1 class="h2">Gestión de Estudiantes</h1>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#studentModal">
                            <i class="fas fa-plus"></i> Nuevo Estudiante
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="card-title mb-0">Lista de Estudiantes</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Buscar estudiante..." id="searchInput">
                                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th>CIP/ID</th>
                                        <th>Nombre Completo</th>
                                        <th>Fecha Nacimiento</th>
                                        <th>Edad</th>
                                        <th>Carrera</th>
                                        <th>Usuario</th> 
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($student['cip_identificacion']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['nombre_completo']); ?></td>
                                        <td><?php echo formatDate($student['fecha_nacimiento']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $student['edad']; ?> años</span>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['carrera']); ?></td>
                                        <td>
                                            <?php if ($student['usuario_id']): ?>
                                                <span class="badge bg-success">Asociado</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No asociado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-student" 
                                                    data-student='<?php echo json_encode($student); ?>'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este estudiante?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
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

    <!-- Modal para agregar/editar estudiante -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="studentForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nuevo Estudiante</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="studentId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cip_identificacion" class="form-label">CIP/Identificación *</label>
                                <input type="text" class="form-control" id="cip_identificacion" name="cip_identificacion" 
                                       required pattern="[A-Z0-9]{4,20}" title="Solo letras mayúsculas y números (4-20 caracteres)">
                                <div class="form-text">Ejemplo: CIP2024001</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="carrera" class="form-label">Carrera *</label>
                                <select class="form-select" id="carrera" name="carrera" required>
                                    <option value="">Seleccionar Carrera</option>
                                    <?php echo generateOptions($carreras); ?>
                                </select>
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

                        <!-- Campo de selección de usuario -->
                        <div class="mb-3">
                            <label for="usuario_id" class="form-label">Usuario Asociado</label>
                            <select class="form-select" id="usuario_id" name="usuario_id">
                                <option value="">Seleccionar Usuario</option>
                                <?php 
                                foreach ($usuarios as $usuario): 
                                    // Verificar si el usuario ya está asociado a otro estudiante
                                    $asociado = $studentModel->getByUserId($usuario['id']);
                                ?>
                                    <option value="<?php echo $usuario['id']; ?>" <?php echo $asociado ? 'disabled' : ''; ?>>
                                        <?php echo htmlspecialchars($usuario['username'] . ' - ' . $usuario['email']); ?>
                                        <?php echo $asociado ? '(Ya asociado)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Selecciona el usuario que corresponda a este estudiante</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required
                                   max="<?php echo date('Y-m-d'); ?>">
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
    document.addEventListener('DOMContentLoaded', function() {
        const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));
        const form = document.getElementById('studentForm');
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const studentId = document.getElementById('studentId');
        const searchInput = document.getElementById('searchInput');
        const studentsTable = document.getElementById('studentsTable');
        
        // Botones de editar
        document.querySelectorAll('.edit-student').forEach(button => {
            button.addEventListener('click', function() {
                const student = JSON.parse(this.dataset.student);
                
                modalTitle.textContent = 'Editar Estudiante';
                formAction.value = 'update';
                studentId.value = student.id;
                
                document.getElementById('cip_identificacion').value = student.cip_identificacion;
                document.getElementById('primer_nombre').value = student.primer_nombre;
                document.getElementById('segundo_nombre').value = student.segundo_nombre || '';
                document.getElementById('primer_apellido').value = student.primer_apellido;
                document.getElementById('segundo_apellido').value = student.segundo_apellido || '';
                document.getElementById('fecha_nacimiento').value = student.fecha_nacimiento;
                document.getElementById('carrera').value = student.carrera;
                document.getElementById('usuario_id').value = student.usuario_id || '';
                
                studentModal.show();
            });
        });
        
        // Botón nuevo estudiante
        document.querySelector('[data-bs-target="#studentModal"]').addEventListener('click', function() {
            modalTitle.textContent = 'Nuevo Estudiante';
            formAction.value = 'create';
            form.reset();
            studentId.value = '';
        });
        
        // Búsqueda en tiempo real
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = studentsTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });
        
        // Validación de fecha (mínimo 16 años)
        document.getElementById('fecha_nacimiento').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            
            if (age < 16) {
                alert('El estudiante debe tener al menos 16 años');
                this.value = '';
            }
        });
    });
    </script>
</body>
</html>