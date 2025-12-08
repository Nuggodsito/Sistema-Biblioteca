<?php
class Validator {
    
    // Sanitizar datos
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    // Validar email
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    // Validar número
    public static function validateNumber($number, $min = null, $max = null) {
        if (!is_numeric($number)) return false;
        if ($min !== null && $number < $min) return false;
        if ($max !== null && $number > $max) return false;
        return true;
    }
    
    // Validar texto (solo letras y espacios)
    public static function validateText($text, $minLength = 1, $maxLength = 255) {
        $text = trim($text);
        $length = strlen($text);
        
        if ($length < $minLength || $length > $maxLength) return false;
        return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $text);
    }
    
    // Validar CIP/Identificación
    public static function validateCIP($cip) {
        return preg_match('/^[A-Z0-9]{4,20}$/', $cip);
    }
    
    // Validar fecha
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    // Validar archivo de imagen
    public static function validateImage($file, $maxSize = 2097152) { // 2MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        if ($file['size'] > $maxSize) return false;
        if (!in_array($file['type'], $allowedTypes)) return false;
        
        return true;
    }
    
    // Validar contraseña
    public static function validatePassword($password) {
        // Mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }
}
?>