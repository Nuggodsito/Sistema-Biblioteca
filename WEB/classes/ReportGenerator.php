<?php
require_once '../includes/config.php';

class ReportGenerator {
    
    // Generar reporte de libros
    public static function generateBooksReport($libros) {
        // CABECERAS PARA UTF-8 Y EXCEL
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="reporte_libros_' . date('Y-m-d') . '.xls"');
        
        echo "<html>";
        echo "<head>";
        echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
        echo "</head>";
        echo "<body>";
        
        echo "<table border='1'>";
        echo "<tr>
                <th>ISBN</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Categoría</th>
                <th>Costo</th>
                <th>Existencias</th>
                <th>Estado</th>
              </tr>";
        
        foreach ($libros as $libro) {
            $estado = $libro['existencias'] > 0 ? 'Disponible' : 'Agotado';
            
            // CONVERTIR A UTF-8
            $titulo = htmlspecialchars($libro['titulo'], ENT_QUOTES, 'UTF-8');
            $autor = htmlspecialchars($libro['autor'], ENT_QUOTES, 'UTF-8');
            $categoria = htmlspecialchars($libro['categoria_nombre'], ENT_QUOTES, 'UTF-8');
            
            echo "<tr>
                    <td>{$libro['isbn']}</td>
                    <td>{$titulo}</td>
                    <td>{$autor}</td>
                    <td>{$categoria}</td>
                    <td>{$libro['costo']}</td>
                    <td>{$libro['existencias']}</td>
                    <td>{$estado}</td>
                  </tr>";
        }
        
        echo "</table>";
        echo "</body>";
        echo "</html>";
        exit;
    }
    
    // Generar reporte de reservas
    public static function generateReservationsReport($reservas) {
        // CABECERAS PARA UTF-8 Y EXCEL
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="reporte_reservas_' . date('Y-m-d') . '.xls"');
        
        echo "<html>";
        echo "<head>";
        echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
        echo "</head>";
        echo "<body>";
        
        echo "<table border='1'>";
        echo "<tr>
                <th>ID Reserva</th>
                <th>Libro</th>
                <th>Autor</th>
                <th>Estudiante</th>
                <th>CIP</th>
                <th>Fecha Reserva</th>
                <th>Fecha Devolución</th>
                <th>Días Reserva</th>
                <th>Estado</th>
              </tr>";
        
        foreach ($reservas as $reserva) {
            $estado = $reserva['estado'] == 'reservado' ? 'Prestado' : 
                     ($reserva['estado'] == 'devuelto' ? 'Devuelto' : 'Vencido');
            
            // CONVERTIR A UTF-8
            $libro_titulo = htmlspecialchars($reserva['libro_titulo'], ENT_QUOTES, 'UTF-8');
            $libro_autor = htmlspecialchars($reserva['libro_autor'], ENT_QUOTES, 'UTF-8');
            $estudiante_nombre = htmlspecialchars($reserva['estudiante_nombre'], ENT_QUOTES, 'UTF-8');
            
            echo "<tr>
                    <td>{$reserva['id']}</td>
                    <td>{$libro_titulo}</td>
                    <td>{$libro_autor}</td>
                    <td>{$estudiante_nombre}</td>
                    <td>{$reserva['estudiante_cip']}</td>
                    <td>{$reserva['fecha_reserva']}</td>
                    <td>{$reserva['fecha_devolucion_estimada']}</td>
                    <td>{$reserva['dias_reserva']}</td>
                    <td>{$estado}</td>
                  </tr>";
        }
        
        echo "</table>";
        echo "</body>";
        echo "</html>";
        exit;
    }
    
    // Generar reporte de estadísticas
    public static function generateStatisticsReport($estadisticas, $periodo) {
        // CABECERAS PARA UTF-8 Y EXCEL
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="estadisticas_libros_' . date('Y-m-d') . '.xls"');
        
        echo "<html>";
        echo "<head>";
        echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
        echo "</head>";
        echo "<body>";
        
        echo "<table border='1'>";
        echo "<tr><th colspan='5'>Estadísticas de Libros Más Utilizados - Periodo: {$periodo}</th></tr>";
        echo "<tr>
                <th>#</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Categoría</th>
                <th>Total Reservas</th>
              </tr>";
        
        $contador = 1;
        foreach ($estadisticas as $est) {
            // CONVERTIR A UTF-8
            $titulo = htmlspecialchars($est['titulo'], ENT_QUOTES, 'UTF-8');
            $autor = htmlspecialchars($est['autor'], ENT_QUOTES, 'UTF-8');
            $categoria = htmlspecialchars($est['categoria'], ENT_QUOTES, 'UTF-8');
            
            echo "<tr>
                    <td>{$contador}</td>
                    <td>{$titulo}</td>
                    <td>{$autor}</td>
                    <td>{$categoria}</td>
                    <td>{$est['total_reservas']}</td>
                  </tr>";
            $contador++;
        }
        
        echo "</table>";
        echo "</body>";
        echo "</html>";
        exit;
    }
}
?>