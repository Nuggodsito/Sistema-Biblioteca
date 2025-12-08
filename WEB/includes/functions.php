<?php
// Funciones auxiliares globales

// Redireccionar
function redirect($url) {
    header("Location: $url");
    exit;
}

// Mostrar mensajes de alerta
function showAlert($message, $type = 'info') {
    $alertClass = '';
    switch ($type) {
        case 'success':
            $alertClass = 'alert-success';
            break;
        case 'error':
        case 'danger':
            $alertClass = 'alert-danger';
            break;
        case 'warning':
            $alertClass = 'alert-warning';
            break;
        default:
            $alertClass = 'alert-info';
    }
    
    return "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Formatear fecha
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

// Calcular días entre fechas
function daysBetween($start, $end) {
    $start = new DateTime($start);
    $end = new DateTime($end);
    $interval = $start->diff($end);
    return $interval->days;
}

// Obtener array de carreras
function getCarreras() {
    return [
        'Ingeniería de Software',
        'Ingeniería en Sistemas Computacionales',
        'Ingeniería Civil',
        'Ingeniería Eléctrica',
        'Ingeniería Mecánica',
        'Ingeniería Industrial',
        'Licenciatura en Administración',
        'Licenciatura en Contabilidad',
        'Licenciatura en Marketing'
    ];
}

// Obtener array de materias
function getMaterias() {
    return [
        'Química',
        'Sistemas',
        'Lógica',
        'Matemática',
        'Estadística',
        'Física',
        'Programación',
        'Base de Datos',
        'Redes',
        'Ingeniería de Software'
    ];
}

// Verificar si es una solicitud AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Generar opciones para select
function generateOptions($array, $selected = '') {
    $options = '';
    foreach ($array as $value) {
        $isSelected = ($value == $selected) ? 'selected' : '';
        $options .= "<option value='$value' $isSelected>$value</option>";
    }
    return $options;
}
?>