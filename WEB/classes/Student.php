<?php
require_once 'Model.php';

class Student extends Model {
    public function __construct() {
        parent::__construct('estudiantes');
    }
    
    // Obtener estudiante por ID de usuario
    public function getByUserId($usuario_id) {
        $query = "SELECT e.*, 
                  CONCAT(e.primer_nombre, ' ', e.primer_apellido) as nombre_completo,
                  TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) as edad
                  FROM estudiantes e 
                  WHERE e.usuario_id = :usuario_id AND e.activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Crear estudiante con usuario_id
    public function create($data) {
        $query = "INSERT INTO estudiantes (cip_identificacion, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, carrera, usuario_id) 
                  VALUES (:cip, :primer_nombre, :segundo_nombre, :primer_apellido, :segundo_apellido, :fecha_nacimiento, :carrera, :usuario_id)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':cip', $data['cip_identificacion']);
        $stmt->bindParam(':primer_nombre', $data['primer_nombre']);
        $stmt->bindParam(':segundo_nombre', $data['segundo_nombre']);
        $stmt->bindParam(':primer_apellido', $data['primer_apellido']);
        $stmt->bindParam(':segundo_apellido', $data['segundo_apellido']);
        $stmt->bindParam(':fecha_nacimiento', $data['fecha_nacimiento']);
        $stmt->bindParam(':carrera', $data['carrera']);
        $stmt->bindParam(':usuario_id', $data['usuario_id']);
        
        return $stmt->execute();
    }
    
    // Actualizar estudiante ACTUALIZADO con usuario_id
    public function update($id, $data) {
        $query = "UPDATE estudiantes SET 
                  cip_identificacion = :cip, 
                  primer_nombre = :primer_nombre, 
                  segundo_nombre = :segundo_nombre,
                  primer_apellido = :primer_apellido, 
                  segundo_apellido = :segundo_apellido,
                  fecha_nacimiento = :fecha_nacimiento, 
                  carrera = :carrera,
                  usuario_id = :usuario_id
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':cip', $data['cip_identificacion']);
        $stmt->bindParam(':primer_nombre', $data['primer_nombre']);
        $stmt->bindParam(':segundo_nombre', $data['segundo_nombre']);
        $stmt->bindParam(':primer_apellido', $data['primer_apellido']);
        $stmt->bindParam(':segundo_apellido', $data['segundo_apellido']);
        $stmt->bindParam(':fecha_nacimiento', $data['fecha_nacimiento']);
        $stmt->bindParam(':carrera', $data['carrera']);
        $stmt->bindParam(':usuario_id', $data['usuario_id']);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    // Verificar si CIP existe
    public function cipExists($cip, $excludeId = null) {
        $query = "SELECT id FROM estudiantes WHERE cip_identificacion = :cip AND activo = 1";
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cip', $cip);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Verificar si usuario ya está asociado
    public function usuarioExists($usuario_id, $excludeId = null) {
        $query = "SELECT id FROM estudiantes WHERE usuario_id = :usuario_id AND activo = 1";
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Obtener estudiantes con información completa
    public function getAllWithDetails() {
        $query = "SELECT e.*, 
                  CONCAT(e.primer_nombre, ' ', COALESCE(e.segundo_nombre, ''), ' ', e.primer_apellido, ' ', COALESCE(e.segundo_apellido, '')) as nombre_completo,
                  TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) as edad,
                  u.username as usuario_nombre
                  FROM estudiantes e 
                  LEFT JOIN usuarios u ON e.usuario_id = u.id
                  WHERE e.activo = 1 
                  ORDER BY e.primer_apellido, e.primer_nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Buscar estudiantes
    public function search($term) {
        $query = "SELECT e.*, 
                  CONCAT(e.primer_nombre, ' ', COALESCE(e.segundo_nombre, ''), ' ', e.primer_apellido, ' ', COALESCE(e.segundo_apellido, '')) as nombre_completo
                  FROM estudiantes e 
                  WHERE e.activo = 1 AND 
                  (e.cip_identificacion LIKE :term OR 
                   e.primer_nombre LIKE :term OR 
                   e.primer_apellido LIKE :term OR 
                   e.carrera LIKE :term)
                  ORDER BY e.primer_apellido, e.primer_nombre";
        
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%$term%";
        $stmt->bindParam(':term', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener estudiante por ID
    public function getById($id) {
        $query = "SELECT e.*, 
                  CONCAT(e.primer_nombre, ' ', COALESCE(e.segundo_nombre, ''), ' ', e.primer_apellido, ' ', COALESCE(e.segundo_apellido, '')) as nombre_completo,
                  TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) as edad
                  FROM estudiantes e 
                  WHERE e.id = :id AND e.activo = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Eliminar estudiante 
    public function delete($id) {
        $query = "UPDATE estudiantes SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // Obtener total de estudiantes activos
    public function getTotalActive() {
        $query = "SELECT COUNT(*) as total FROM estudiantes WHERE activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Obtener estudiantes por carrera
    public function getByCareer($carrera) {
        $query = "SELECT e.*, 
                  CONCAT(e.primer_nombre, ' ', COALESCE(e.segundo_nombre, ''), ' ', e.primer_apellido, ' ', COALESCE(e.segundo_apellido, '')) as nombre_completo
                  FROM estudiantes e 
                  WHERE e.carrera = :carrera AND e.activo = 1
                  ORDER BY e.primer_apellido, e.primer_nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':carrera', $carrera);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener estudiantes sin usuario asociado
    public function getWithoutUser() {
        $query = "SELECT e.*, 
                  CONCAT(e.primer_nombre, ' ', COALESCE(e.segundo_nombre, ''), ' ', e.primer_apellido, ' ', COALESCE(e.segundo_apellido, '')) as nombre_completo
                  FROM estudiantes e 
                  WHERE e.usuario_id IS NULL AND e.activo = 1
                  ORDER BY e.primer_apellido, e.primer_nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>