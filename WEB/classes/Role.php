<?php
require_once 'Model.php';

class Role extends Model {
    public function __construct() {
        parent::__construct('roles');
    }
    
    // Obtener todos los roles 
    public function getAllRoles() {
        $query = "SELECT * FROM roles ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Crear rol
    public function create($data) {
        $query = "INSERT INTO roles (nombre, descripcion, permisos) VALUES (:nombre, :descripcion, :permisos)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':permisos', $data['permisos']);
        return $stmt->execute();
    }
    
    // Actualizar rol
    public function update($id, $data) {
        $query = "UPDATE roles SET nombre = :nombre, descripcion = :descripcion, permisos = :permisos WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':permisos', $data['permisos']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>