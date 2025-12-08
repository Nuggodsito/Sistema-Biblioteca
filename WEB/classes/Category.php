<?php
require_once 'Model.php';

class Category extends Model {
    public function __construct() {
        parent::__construct('categorias');
    }
    
    // Crear categoría
    public function create($data) {
        $query = "INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        return $stmt->execute();
    }
    
    // Actualizar categoría
    public function update($id, $data) {
        $query = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // Verificar si nombre de categoría existe
    public function nameExists($nombre, $excludeId = null) {
        $query = "SELECT id FROM categorias WHERE nombre = :nombre";
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Obtener categorías con conteo de libros
    public function getWithBookCount() {
        $query = "SELECT c.*, COUNT(l.id) as total_libros 
                  FROM categorias c 
                  LEFT JOIN libros l ON c.id = l.categoria_id AND l.activo = 1 
                  GROUP BY c.id 
                  ORDER BY c.nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>