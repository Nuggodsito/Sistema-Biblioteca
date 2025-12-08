<?php
class ImageHandler {
    private $uploadDir;
    private $thumbnailDir;
    private $maxFileSize;
    private $allowedTypes;
    
    public function __construct() {
        $basePath = $_SERVER['DOCUMENT_ROOT'] . '/WEB/';
        $this->uploadDir = $basePath . 'assets/uploads/images/';
        $this->thumbnailDir = $basePath . 'assets/uploads/thumbnails/';
        $this->maxFileSize = 2 * 1024 * 1024; // 2MB
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        // Verificación básica de permisos
        $this->checkPermissions();
    }
    
    private function checkPermissions() {
        // Verificación simple de permisos de escritura
        if (!is_writable($this->uploadDir)) {
            throw new Exception('El directorio de imágenes no tiene permisos de escritura: ' . $this->uploadDir);
        }
        
        if (!is_writable($this->thumbnailDir)) {
            throw new Exception('El directorio de thumbnails no tiene permisos de escritura: ' . $this->thumbnailDir);
        }
    }
    
    public function uploadBookImage($file, $bookId = 0) {
        try {
            // Validar archivo
            $this->validateImage($file);
            
            // Generar nombres únicos
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $baseName = 'book_' . ($bookId > 0 ? $bookId . '_' : '') . uniqid() . '_' . time();
            $imageName = $baseName . '.' . $extension;
            $thumbnailName = $baseName . '_thumb.' . $extension;
            
            // Rutas completas
            $imagePath = $this->uploadDir . $imageName;
            $thumbnailPath = $this->thumbnailDir . $thumbnailName;
            
            // Mover archivo original
            if (!move_uploaded_file($file['tmp_name'], $imagePath)) {
                throw new Exception('Error al subir la imagen.');
            }
            
            // Crear thumbnail
            $thumbnailCreated = $this->createThumbnail($imagePath, $thumbnailPath, 300, 400);
            
            // Si no se pudo crear el thumbnail, usar la imagen original
            if (!$thumbnailCreated) {
                $thumbnailName = $imageName;
                copy($imagePath, $thumbnailPath);
            }
            
            return [
                'image' => $imageName,
                'thumbnail' => $thumbnailName
            ];
            
        } catch (Exception $e) {
            // Limpiar archivos subidos en caso de error
            if (isset($imagePath) && file_exists($imagePath)) {
                @unlink($imagePath);
            }
            if (isset($thumbnailPath) && file_exists($thumbnailPath)) {
                @unlink($thumbnailPath);
            }
            
            throw new Exception('Error procesando imagen: ' . $e->getMessage());
        }
    }
    
    private function validateImage($file) {
        // Verificar error de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor.',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario.',
                UPLOAD_ERR_PARTIAL => 'El archivo fue solo parcialmente subido.',
                UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo.',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal.',
                UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo.'
            ];
            throw new Exception($errorMessages[$file['error']] ?? 'Error desconocido en la subida del archivo.');
        }
        
        // Verificar tipo de archivo
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten: JPG, PNG, GIF y WebP.');
        }
        
        // Verificar tamaño
        if ($file['size'] > $this->maxFileSize) {
            throw new Exception('El archivo es demasiado grande. Tamaño máximo: 2MB.');
        }
        
        // Verificar que sea una imagen real
        $imageInfo = @getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            throw new Exception('El archivo no es una imagen válida.');
        }
        
        return true;
    }
    
    private function createThumbnail($sourcePath, $destPath, $maxWidth, $maxHeight) {
        try {
            // Obtener información de la imagen
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }
            
            list($origWidth, $origHeight, $type) = $imageInfo;
            
            // Crear imagen según el tipo
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return false;
            }
            
            if (!$sourceImage) {
                return false;
            }
            
            // Calcular nuevas dimensiones manteniendo proporción
            $ratio = $origWidth / $origHeight;
            
            if ($maxWidth / $maxHeight > $ratio) {
                $newWidth = $maxHeight * $ratio;
                $newHeight = $maxHeight;
            } else {
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $ratio;
            }
            
            // Redondear a enteros para PHP 8.1+
            $newWidth = (int)round($newWidth);
            $newHeight = (int)round($newHeight);
            
            // Asegurar dimensiones mínimas
            $newWidth = max(1, $newWidth);
            $newHeight = max(1, $newHeight);
            
            // Crear imagen thumbnail
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparencia para PNG y GIF
            if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
                imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
            }
            
            // Redimensionar
            imagecopyresampled(
                $thumbnail, $sourceImage, 
                0, 0, 0, 0, 
                $newWidth, $newHeight, 
                $origWidth, $origHeight
            );
            
            // Guardar thumbnail
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($thumbnail, $destPath, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($thumbnail, $destPath, 8);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($thumbnail, $destPath);
                    break;
                case IMAGETYPE_WEBP:
                    imagewebp($thumbnail, $destPath, 85);
                    break;
            }
            
            // Liberar memoria
            imagedestroy($sourceImage);
            imagedestroy($thumbnail);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error creando thumbnail: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteBookImages($imageName, $thumbnailName) {
        try {
            if ($imageName && file_exists($this->uploadDir . $imageName)) {
                unlink($this->uploadDir . $imageName);
            }
            if ($thumbnailName && file_exists($this->thumbnailDir . $thumbnailName)) {
                unlink($this->thumbnailDir . $thumbnailName);
            }
            return true;
        } catch (Exception $e) {
            error_log("Error eliminando imágenes: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getImageUrl($imageName, $type = 'original') {
        if (!$imageName) return '';
        
        if ($type === 'thumbnail') {
            return '../assets/uploads/thumbnails/' . $imageName;
        } else {
            return '../assets/uploads/images/' . $imageName;
        }
    }
}
?>