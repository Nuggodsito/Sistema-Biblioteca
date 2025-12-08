<?php
require_once '../includes/database.php';
require_once '../includes/validator.php';
require_once '../includes/functions.php';

class Model {
    protected $db;
    protected $conn;
    protected $table;
    
    public function __construct($table) {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->table = $table;
    }
    
    // Obtener todos los registros
public function getAll() {
    $query = "SELECT * FROM " . $this->table . " WHERE activo = 1";
    error_log("DEBUG: Ejecutando query: " . $query); // ← Agregar esta línea
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    // Obtener por ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id AND activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Eliminar 
    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // Contar registros activos
    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>