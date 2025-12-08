<?php
require_once 'Model.php';

class Book extends Model {
    public function __construct() {
        parent::__construct('libros');
    }
    
    // Crear libro
    public function create($data) {
        $query = "INSERT INTO libros (isbn, titulo, autor, descripcion, costo, existencias, categoria_id, imagen_portada, imagen_thumbnail) 
                  VALUES (:isbn, :titulo, :autor, :descripcion, :costo, :existencias, :categoria_id, :imagen_portada, :imagen_thumbnail)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':isbn', $data['isbn']);
        $stmt->bindParam(':titulo', $data['titulo']);
        $stmt->bindParam(':autor', $data['autor']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':costo', $data['costo']);
        $stmt->bindParam(':existencias', $data['existencias']);
        $stmt->bindParam(':categoria_id', $data['categoria_id']);
        $stmt->bindParam(':imagen_portada', $data['imagen_portada']);
        $stmt->bindParam(':imagen_thumbnail', $data['imagen_thumbnail']);
        
        return $stmt->execute();
    }
    
    // Actualizar libro
    public function update($id, $data) {
        $query = "UPDATE libros SET 
                  isbn = :isbn, titulo = :titulo, autor = :autor, descripcion = :descripcion,
                  costo = :costo, existencias = :existencias, categoria_id = :categoria_id";
        
        // Agregar campos de imagen 
        if (isset($data['imagen_portada'])) {
            $query .= ", imagen_portada = :imagen_portada";
        }
        if (isset($data['imagen_thumbnail'])) {
            $query .= ", imagen_thumbnail = :imagen_thumbnail";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':isbn', $data['isbn']);
        $stmt->bindParam(':titulo', $data['titulo']);
        $stmt->bindParam(':autor', $data['autor']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':costo', $data['costo']);
        $stmt->bindParam(':existencias', $data['existencias']);
        $stmt->bindParam(':categoria_id', $data['categoria_id']);
        $stmt->bindParam(':id', $id);
        
        if (isset($data['imagen_portada'])) {
            $stmt->bindParam(':imagen_portada', $data['imagen_portada']);
        }
        if (isset($data['imagen_thumbnail'])) {
            $stmt->bindParam(':imagen_thumbnail', $data['imagen_thumbnail']);
        }
        
        return $stmt->execute();
    }
    
    // Obtener libros con información de categoría
    public function getAllWithCategory() {
        $query = "SELECT l.*, c.nombre as categoria_nombre 
                  FROM libros l 
                  INNER JOIN categorias c ON l.categoria_id = c.id 
                  WHERE l.activo = 1 
                  ORDER BY l.titulo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Buscar libros
    public function search($term, $categoria_id = null) {
        $query = "SELECT l.*, c.nombre as categoria_nombre 
                  FROM libros l 
                  INNER JOIN categorias c ON l.categoria_id = c.id 
                  WHERE l.activo = 1 AND 
                  (l.titulo LIKE :term OR l.autor LIKE :term OR l.isbn LIKE :term)";
        
        if ($categoria_id) {
            $query .= " AND l.categoria_id = :categoria_id";
        }
        
        $query .= " ORDER BY l.titulo";
        
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%$term%";
        $stmt->bindParam(':term', $searchTerm);
        if ($categoria_id) {
            $stmt->bindParam(':categoria_id', $categoria_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener libros disponibles 
    public function getAvailableBooks() {
        $query = "SELECT l.*, c.nombre as categoria_nombre 
                  FROM libros l 
                  INNER JOIN categorias c ON l.categoria_id = c.id 
                  WHERE l.activo = 1 AND l.existencias > 0 
                  ORDER BY l.titulo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Actualizar existencias
    public function updateStock($id, $cantidad) {
        $query = "UPDATE libros SET existencias = existencias + :cantidad WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // Verificar si ISBN existe
    public function isbnExists($isbn, $excludeId = null) {
        if (empty($isbn)) return false;
        
        $query = "SELECT id FROM libros WHERE isbn = :isbn AND activo = 1";
        if ($excludeId) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':isbn', $isbn);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Obtener libros que necesitan thumbnails
    public function getBooksWithoutThumbnails() {
        $query = "SELECT id, titulo, imagen_portada, imagen_thumbnail 
                  FROM libros 
                  WHERE activo = 1 AND imagen_portada IS NOT NULL 
                  AND (imagen_thumbnail IS NULL OR imagen_thumbnail = '')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>