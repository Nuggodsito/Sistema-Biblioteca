<?php
require_once 'Model.php';

class User extends Model {
    public function __construct() {
        parent::__construct('usuarios');
    }
    
    // Crear usuario
    public function create($data) {
        $query = "INSERT INTO usuarios (username, email, password, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, rol_id) 
                  VALUES (:username, :email, :password, :primer_nombre, :segundo_nombre, :primer_apellido, :segundo_apellido, :rol_id)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash de contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':primer_nombre', $data['primer_nombre']);
        $stmt->bindParam(':segundo_nombre', $data['segundo_nombre']);
        $stmt->bindParam(':primer_apellido', $data['primer_apellido']);
        $stmt->bindParam(':segundo_apellido', $data['segundo_apellido']);
        $stmt->bindParam(':rol_id', $data['rol_id']);
        
        return $stmt->execute();
    }
    
    // Actualizar usuario
    public function update($id, $data) {
        $query = "UPDATE usuarios SET 
                  username = :username, email = :email, primer_nombre = :primer_nombre, 
                  segundo_nombre = :segundo_nombre, primer_apellido = :primer_apellido, 
                  segundo_apellido = :segundo_apellido, rol_id = :rol_id 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':primer_nombre', $data['primer_nombre']);
        $stmt->bindParam(':segundo_nombre', $data['segundo_nombre']);
        $stmt->bindParam(':primer_apellido', $data['primer_apellido']);
        $stmt->bindParam(':segundo_apellido', $data['segundo_apellido']);
        $stmt->bindParam(':rol_id', $data['rol_id']);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    // Verificar si username existe
    public function usernameExists($username, $excludeId = null) {
        $query = "SELECT id FROM usuarios WHERE username = :username AND activo = 1";
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Obtener usuarios con información de rol
    public function getUsersWithRole() {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM usuarios u 
                  INNER JOIN roles r ON u.rol_id = r.id 
                  WHERE u.activo = 1 
                  ORDER BY u.primer_apellido, u.primer_nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
public function updatePassword($user_id, $new_password) {
    try {
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE usuarios SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $user_id);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al actualizar contraseña: " . $e->getMessage());
        return false;
    }
}   
}

?>