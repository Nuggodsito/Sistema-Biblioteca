<?php
require_once 'database.php';
require_once 'validator.php';

class Auth {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    // Login de usuario
    public function login($username, $password) {
        $username = Validator::sanitize($username);
        
        try {
            $query = "SELECT u.*, r.nombre as rol_nombre, r.permisos 
                      FROM usuarios u 
                      INNER JOIN roles r ON u.rol_id = r.id 
                      WHERE u.username = :username AND u.activo = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar contraseña
                if (password_verify($password, $user['password'])) {
                    // Configurar sesión
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['rol_nombre'];
                    $_SESSION['role_id'] = $user['rol_id'];
                    $_SESSION['permisos'] = explode(',', $user['permisos']);
                    $_SESSION['nombre_completo'] = $user['primer_nombre'] . ' ' . $user['primer_apellido'];
                    
                    return true;
                }
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            return false;
        }
    }
    
    // Verificar si usuario está logueado
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Verificar permisos
    public static function hasPermission($permiso) {
        return isset($_SESSION['permisos']) && in_array($permiso, $_SESSION['permisos']);
    }
    
    // Logout
    public static function logout() {
        session_destroy();
        session_start();
    }
    
    // Obtener información del usuario actual
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'nombre_completo' => $_SESSION['nombre_completo']
            ];
        }
        return null;
    }
    
    // Verificar rol
    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'Administrador';
    }
    
    public static function isBibliotecario() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'Bibliotecario';
    }
    
    public static function isEstudiante() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'Estudiante';
    }
}
?>