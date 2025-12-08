<?php
require_once 'Model.php';

class BookRequest extends Model {
    public function __construct() {
        parent::__construct('solicitudes_libros');
    }
    
    // Crear solicitud
    public function create($data) {
        $query = "INSERT INTO solicitudes_libros (titulo_solicitado, autor_solicitado, materia, estudiante_id, justificacion) 
                  VALUES (:titulo, :autor, :materia, :estudiante_id, :justificacion)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':titulo', $data['titulo_solicitado']);
        $stmt->bindParam(':autor', $data['autor_solicitado']);
        $stmt->bindParam(':materia', $data['materia']);
        $stmt->bindParam(':estudiante_id', $data['estudiante_id']);
        $stmt->bindParam(':justificacion', $data['justificacion']);
        
        return $stmt->execute();
    }
    
    // Obtener solicitudes con información de estudiante
    public function getAllWithStudentInfo() {
        $query = "SELECT s.*, 
                  CONCAT(e.primer_nombre, ' ', e.primer_apellido) as estudiante_nombre,
                  e.cip_identificacion as estudiante_cip,
                  e.carrera as estudiante_carrera
                  FROM solicitudes_libros s 
                  INNER JOIN estudiantes e ON s.estudiante_id = e.id 
                  ORDER BY s.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Actualizar estado de solicitud
    public function updateStatus($id, $estado) {
        $query = "UPDATE solicitudes_libros SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // Obtener solicitudes pendientes
    public function getPendingRequests() {
        $query = "SELECT s.*, 
                  CONCAT(e.primer_nombre, ' ', e.primer_apellido) as estudiante_nombre,
                  e.cip_identificacion as estudiante_cip
                  FROM solicitudes_libros s 
                  INNER JOIN estudiantes e ON s.estudiante_id = e.id 
                  WHERE s.estado = 'pendiente' 
                  ORDER BY s.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Obtener solicitudes por estudiante
public function getByStudentId($estudiante_id) {
    $query = "SELECT s.*, 
              CONCAT(e.primer_nombre, ' ', e.primer_apellido) as estudiante_nombre,
              e.cip_identificacion as estudiante_cip,
              e.carrera as estudiante_carrera
              FROM solicitudes_libros s 
              INNER JOIN estudiantes e ON s.estudiante_id = e.id 
              WHERE s.estudiante_id = :estudiante_id
              ORDER BY s.created_at DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':estudiante_id', $estudiante_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener solicitudes pendientes por estudiante
public function getPendingRequestsByStudent($estudiante_id) {
    $query = "SELECT s.*, 
              CONCAT(e.primer_nombre, ' ', e.primer_apellido) as estudiante_nombre,
              e.cip_identificacion as estudiante_cip
              FROM solicitudes_libros s 
              INNER JOIN estudiantes e ON s.estudiante_id = e.id 
              WHERE s.estado = 'pendiente' AND s.estudiante_id = :estudiante_id
              ORDER BY s.created_at DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':estudiante_id', $estudiante_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>