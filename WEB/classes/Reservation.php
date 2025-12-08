<?php
require_once 'Model.php';

class Reservation extends Model {
    public function __construct() {
        parent::__construct('reservas');
    }
    
    // Crear reserva
    public function create($data) {
        $query = "INSERT INTO reservas (libro_id, estudiante_id, usuario_reserva_id, fecha_devolucion_estimada, dias_reserva) 
                  VALUES (:libro_id, :estudiante_id, :usuario_reserva_id, :fecha_devolucion_estimada, :dias_reserva)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':libro_id', $data['libro_id']);
        $stmt->bindParam(':estudiante_id', $data['estudiante_id']);
        $stmt->bindParam(':usuario_reserva_id', $data['usuario_reserva_id']);
        $stmt->bindParam(':fecha_devolucion_estimada', $data['fecha_devolucion_estimada']);
        $stmt->bindParam(':dias_reserva', $data['dias_reserva']);
        
        return $stmt->execute();
    }
    
    // Obtener reservas con información completa
    public function getAllWithDetails() {
        $query = "SELECT r.*, 
                  l.titulo as libro_titulo, l.autor as libro_autor, l.isbn as libro_isbn,
                  CONCAT(e.primer_nombre, ' ', e.primer_apellido) as estudiante_nombre,
                  e.cip_identificacion as estudiante_cip,
                  CONCAT(u.primer_nombre, ' ', u.primer_apellido) as usuario_nombre,
                  c.nombre as categoria_nombre
                  FROM reservas r 
                  INNER JOIN libros l ON r.libro_id = l.id 
                  INNER JOIN estudiantes e ON r.estudiante_id = e.id 
                  INNER JOIN usuarios u ON r.usuario_reserva_id = u.id 
                  INNER JOIN categorias c ON l.categoria_id = c.id 
                  ORDER BY r.fecha_reserva DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /*Obtener reservas activas*/
    public function getActiveReservations() {
        $query = "SELECT r.*, 
                  l.titulo as libro_titulo, l.autor as libro_autor,
                  CONCAT(e.primer_nombre, ' ', e.primer_apellido) as estudiante_nombre,
                  e.cip_identificacion as estudiante_cip
                  FROM reservas r 
                  INNER JOIN libros l ON r.libro_id = l.id 
                  INNER JOIN estudiantes e ON r.estudiante_id = e.id 
                  WHERE r.estado = 'reservado' 
                  ORDER BY r.fecha_devolucion_estimada ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /*Registrar devolución de libro*/
    public function returnBook($reserva_id) {
        $query = "UPDATE reservas SET estado = 'devuelto', fecha_devolucion_real = NOW() WHERE id = :id AND estado = 'reservado'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $reserva_id);
        return $stmt->execute();
    }
    
    /*Verificar si estudiante ya tiene reserva activa del libro*/
    public function hasActiveReservation($estudiante_id, $libro_id) {
        $query = "SELECT id FROM reservas WHERE estudiante_id = :estudiante_id AND libro_id = :libro_id AND estado = 'reservado'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->bindParam(':libro_id', $libro_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    /*Obtener reserva por ID*/
    public function getById($id) {
        $query = "SELECT r.*, 
                  l.titulo as libro_titulo, l.autor as libro_autor,
                  CONCAT(e.primer_nombre, ' ', e.primer_apellido) as estudiante_nombre,
                  e.cip_identificacion as estudiante_cip
                  FROM reservas r 
                  INNER JOIN libros l ON r.libro_id = l.id 
                  INNER JOIN estudiantes e ON r.estudiante_id = e.id 
                  WHERE r.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener reservas por rango de fechas
    public function getByDateRange($fecha_inicio, $fecha_fin) {
        $query = "SELECT r.*, 
                  l.titulo as libro_titulo, l.autor as libro_autor,
                  CONCAT(e.primer_nombre, ' ', e.primer_apellido) as estudiante_nombre,
                  e.cip_identificacion as estudiante_cip,
                  c.nombre as categoria_nombre
                  FROM reservas r 
                  INNER JOIN libros l ON r.libro_id = l.id 
                  INNER JOIN estudiantes e ON r.estudiante_id = e.id 
                  INNER JOIN categorias c ON l.categoria_id = c.id 
                  WHERE DATE(r.fecha_reserva) BETWEEN :fecha_inicio AND :fecha_fin 
                  ORDER BY r.fecha_reserva DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener estadísticas de libros más reservados
    public function getMostReservedBooks($limit = 10, $periodo_inicio = null, $periodo_fin = null) {
        $query = "SELECT l.titulo, l.autor, c.nombre as categoria, COUNT(r.id) as total_reservas
                  FROM reservas r 
                  INNER JOIN libros l ON r.libro_id = l.id 
                  INNER JOIN categorias c ON l.categoria_id = c.id 
                  WHERE 1=1";
        
        if ($periodo_inicio && $periodo_fin) {
            $query .= " AND DATE(r.fecha_reserva) BETWEEN :periodo_inicio AND :periodo_fin";
        }
        
        $query .= " GROUP BY l.id 
                    ORDER BY total_reservas DESC 
                    LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        
        if ($periodo_inicio && $periodo_fin) {
            $stmt->bindParam(':periodo_inicio', $periodo_inicio);
            $stmt->bindParam(':periodo_fin', $periodo_fin);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>