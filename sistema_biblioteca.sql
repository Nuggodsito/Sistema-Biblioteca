-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 08, 2025 at 06:12 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistema_biblioteca`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `created_at`, `activo`) VALUES
(1, 'Química', 'Libros relacionados con química', '2025-11-29 21:56:32', 1),
(2, 'Sistemas', 'Libros de sistemas computacionales', '2025-11-29 21:56:32', 1),
(3, 'Lógica', 'Libros de lógica matemática', '2025-11-29 21:56:32', 1),
(4, 'Matemática', 'Libros de matemáticas', '2025-11-29 21:56:32', 1),
(5, 'Estadística', 'Libros de estadística', '2025-11-29 21:56:32', 1),
(6, 'Literatura', 'Libros de literatura', '2025-11-30 05:21:37', 1);

-- --------------------------------------------------------

--
-- Table structure for table `estudiantes`
--

DROP TABLE IF EXISTS `estudiantes`;
CREATE TABLE IF NOT EXISTS `estudiantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cip_identificacion` varchar(20) NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `carrera` varchar(100) NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `activo` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cip_identificacion` (`cip_identificacion`),
  UNIQUE KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `cip_identificacion`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `fecha_nacimiento`, `carrera`, `usuario_id`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'E8211273', 'Luis', 'Alejandro', 'Calderón', 'Espinosa', '2005-05-09', 'Ingeniería de Software', 2, 1, '2025-11-30 03:46:03', '2025-11-30 06:57:35'),
(2, '81031938', 'Luis', 'Arturo', 'Ortega', 'Guerra', '2005-02-02', 'Ingeniería de Software', 4, 1, '2025-11-30 05:13:13', '2025-11-30 06:58:44'),
(3, 'E82112152', 'Ander', 'Papi', 'Sapin', 'Ron', '2005-02-03', 'Ingeniería Industrial', NULL, 0, '2025-11-30 05:15:40', '2025-11-30 05:15:43'),
(4, 'E8211345', 'Ander', 'Jonas', 'Pereira', 'Colón', '2007-06-30', 'Ingeniería Industrial', 5, 1, '2025-11-30 10:22:27', '2025-11-30 10:23:34');

-- --------------------------------------------------------

--
-- Table structure for table `libros`
--

DROP TABLE IF EXISTS `libros`;
CREATE TABLE IF NOT EXISTS `libros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `isbn` varchar(20) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `autor` varchar(255) NOT NULL,
  `descripcion` text,
  `costo` decimal(10,2) NOT NULL,
  `existencias` int DEFAULT '0',
  `categoria_id` int NOT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `imagen_thumbnail` varchar(255) DEFAULT NULL,
  `activo` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `categoria_id` (`categoria_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `libros`
--

INSERT INTO `libros` (`id`, `isbn`, `titulo`, `autor`, `descripcion`, `costo`, `existencias`, `categoria_id`, `imagen_portada`, `imagen_thumbnail`, `activo`, `created_at`, `updated_at`) VALUES
(1, '978-8437604934', 'Estadística Avanzada para Ingenieros', 'Pascal Antony', 'Libro de Estadística Avanzada para Ingenieros octava edición', 15.00, 28, 5, 'book_1_692bf6dc07138_1764488924.png', 'book_1_692bf6dc07138_1764488924_thumb.png', 1, '2025-11-30 03:25:44', '2025-12-07 21:07:48'),
(2, '978-8437604947', 'Fundamentos de la Química', 'Maluma Castillo', 'Fundamentos de la Química, Decima cuarta edición', 15.00, 45, 1, 'book_2_692bf6e629cbc_1764488934.png', 'book_2_692bf6e629cbc_1764488934_thumb.png', 1, '2025-11-30 05:18:09', '2025-11-30 10:21:28'),
(3, '978-8437604478', 'La Sombra del Viento', 'Carlos Ruiz', 'La Sombra del Viento literatura', 45.00, 60, 6, 'book_692bf870455c0_1764489328.png', 'book_692bf870455c0_1764489328_thumb.png', 1, '2025-11-30 07:55:28', '2025-11-30 07:55:50'),
(4, '978-84376049234', 'Lógica avanzada', 'Carlos Mateo', 'Lógica avanzada octava edición', 30.00, 12, 3, 'book_692c0fef3a38f_1764495343.png', 'book_692c0fef3a38f_1764495343_thumb.png', 1, '2025-11-30 09:35:43', '2025-11-30 10:21:30'),
(5, '978-8437601243', 'Pensar en Sistemas', 'Donella Meadowns', 'Pensar en Sistemas', 30.00, 34, 2, 'book_692c1cbe966a3_1764498622.png', 'book_692c1cbe966a3_1764498622_thumb.png', 1, '2025-11-30 10:30:22', '2025-11-30 10:33:57'),
(6, '978-843760231', 'Sistemas de Información', 'Effy Oz', 'Sistemas de Información', 10.00, 56, 2, 'book_692c203ed94d6_1764499518.png', 'book_692c203ed94d6_1764499518_thumb.png', 1, '2025-11-30 10:45:18', '2025-12-01 02:30:49'),
(7, '978-8437604432', 'Genios de la Química', 'Donella Meadowns', 'Genios de la Química', 23.00, 10, 1, 'book_692d99107fbe8_1764595984.png', 'book_692d99107fbe8_1764595984_thumb.png', 1, '2025-12-01 13:33:04', '2025-12-01 13:33:04');

-- --------------------------------------------------------

--
-- Table structure for table `reservas`
--

DROP TABLE IF EXISTS `reservas`;
CREATE TABLE IF NOT EXISTS `reservas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libro_id` int NOT NULL,
  `estudiante_id` int NOT NULL,
  `usuario_reserva_id` int NOT NULL,
  `fecha_reserva` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_devolucion_estimada` date DEFAULT NULL,
  `fecha_devolucion_real` timestamp NULL DEFAULT NULL,
  `estado` enum('reservado','devuelto','vencido') DEFAULT 'reservado',
  `dias_reserva` int DEFAULT '7',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `libro_id` (`libro_id`),
  KEY `estudiante_id` (`estudiante_id`),
  KEY `usuario_reserva_id` (`usuario_reserva_id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reservas`
--

INSERT INTO `reservas` (`id`, `libro_id`, `estudiante_id`, `usuario_reserva_id`, `fecha_reserva`, `fecha_devolucion_estimada`, `fecha_devolucion_real`, `estado`, `dias_reserva`, `created_at`) VALUES
(1, 2, 1, 1, '2025-11-30 05:36:10', '2025-12-07', '2025-11-30 05:51:04', 'devuelto', 7, '2025-11-30 05:36:10'),
(2, 1, 2, 1, '2025-11-30 05:51:20', '2025-12-21', '2025-11-30 05:51:28', 'devuelto', 21, '2025-11-30 05:51:20'),
(3, 1, 2, 1, '2025-11-30 05:51:37', '2025-12-21', '2025-11-30 05:52:09', 'devuelto', 21, '2025-11-30 05:51:37'),
(4, 1, 1, 1, '2025-11-30 05:52:03', '2025-12-21', '2025-11-30 05:52:07', 'devuelto', 21, '2025-11-30 05:52:03'),
(5, 1, 1, 2, '2025-11-30 06:59:15', '2025-12-07', '2025-11-30 07:00:21', 'devuelto', 7, '2025-11-30 06:59:15'),
(6, 2, 1, 2, '2025-11-30 06:59:23', '2025-12-07', '2025-11-30 07:00:19', 'devuelto', 7, '2025-11-30 06:59:23'),
(7, 3, 1, 1, '2025-11-30 07:55:46', '2025-12-14', '2025-11-30 07:55:50', 'devuelto', 14, '2025-11-30 07:55:46'),
(8, 4, 1, 1, '2025-11-30 09:36:18', '2025-12-21', '2025-11-30 09:36:26', 'devuelto', 21, '2025-11-30 09:36:18'),
(9, 1, 1, 2, '2025-11-30 09:38:55', '2025-12-07', '2025-11-30 09:40:20', 'devuelto', 7, '2025-11-30 09:38:55'),
(10, 4, 1, 2, '2025-11-30 09:38:59', '2025-12-07', '2025-11-30 09:40:22', 'devuelto', 7, '2025-11-30 09:38:59'),
(11, 2, 1, 2, '2025-11-30 09:55:32', '2025-12-07', '2025-11-30 10:08:13', 'devuelto', 7, '2025-11-30 09:55:32'),
(12, 2, 2, 4, '2025-11-30 10:09:57', '2025-12-07', '2025-11-30 10:21:28', 'devuelto', 7, '2025-11-30 10:09:57'),
(13, 4, 2, 4, '2025-11-30 10:10:00', '2025-12-07', '2025-11-30 10:21:30', 'devuelto', 7, '2025-11-30 10:10:00'),
(14, 1, 1, 2, '2025-11-30 10:32:50', '2025-12-07', '2025-11-30 10:33:59', 'devuelto', 7, '2025-11-30 10:32:50'),
(15, 5, 1, 2, '2025-11-30 10:32:54', '2025-12-07', '2025-11-30 10:33:57', 'devuelto', 7, '2025-11-30 10:32:54'),
(16, 1, 1, 2, '2025-11-30 10:47:32', '2025-12-07', '2025-11-30 10:48:42', 'devuelto', 7, '2025-11-30 10:47:32'),
(17, 6, 1, 2, '2025-11-30 10:47:39', '2025-12-07', '2025-11-30 10:48:40', 'devuelto', 7, '2025-11-30 10:47:39'),
(18, 1, 1, 2, '2025-12-01 02:30:11', '2025-12-08', '2025-12-01 02:30:47', 'devuelto', 7, '2025-12-01 02:30:11'),
(19, 6, 1, 2, '2025-12-01 02:30:15', '2025-12-08', '2025-12-01 02:30:49', 'devuelto', 7, '2025-12-01 02:30:15'),
(20, 1, 1, 2, '2025-12-01 13:35:34', '2025-12-08', '2025-12-01 13:36:29', 'devuelto', 7, '2025-12-01 13:35:34'),
(21, 1, 1, 2, '2025-12-07 19:18:40', '2025-12-14', '2025-12-07 20:49:00', 'devuelto', 7, '2025-12-07 19:18:40'),
(22, 1, 1, 2, '2025-12-07 21:07:23', '2025-12-14', '2025-12-07 21:07:48', 'devuelto', 7, '2025-12-07 21:07:23');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  `permisos` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `permisos`, `created_at`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', 'usuarios,estudiantes,libros,reservas,reportes', '2025-11-29 21:56:32'),
(2, 'Bibliotecario', 'Gestión de libros y reservas', 'estudiantes,libros,reservas', '2025-11-29 21:56:32'),
(3, 'Estudiante', 'Acceso a reservas públicas', 'reservas_publicas', '2025-11-29 21:56:32');

-- --------------------------------------------------------

--
-- Table structure for table `solicitudes_libros`
--

DROP TABLE IF EXISTS `solicitudes_libros`;
CREATE TABLE IF NOT EXISTS `solicitudes_libros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo_solicitado` varchar(255) NOT NULL,
  `autor_solicitado` varchar(255) DEFAULT NULL,
  `materia` varchar(100) NOT NULL,
  `estudiante_id` int NOT NULL,
  `justificacion` text,
  `estado` enum('pendiente','atendida','rechazada') DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `estudiante_id` (`estudiante_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `solicitudes_libros`
--

INSERT INTO `solicitudes_libros` (`id`, `titulo_solicitado`, `autor_solicitado`, `materia`, `estudiante_id`, `justificacion`, `estado`, `created_at`) VALUES
(1, 'Matematicas superiores', 'Fermin Pineda', 'Matemática', 1, 'Estudios', 'atendida', '2025-11-30 07:01:06'),
(2, 'Naturaleza', 'Fermin Capo', 'Lógica', 1, 'estudio', 'atendida', '2025-11-30 09:39:42'),
(3, 'Naturaleza', 'Dr Gregorio', 'Lógica', 1, 'estudios', 'rechazada', '2025-11-30 10:33:23'),
(4, 'Matematicas Inferiores', 'Fermin Pineda', 'Matemática', 1, 'si', 'rechazada', '2025-11-30 10:47:59'),
(5, 'si', 'oscar', 'Lógica', 2, 'as', 'rechazada', '2025-12-01 02:39:49'),
(6, 'Matematicas superiores 2', 'Dr Gregorio', 'Matemática', 1, 'Estudios', 'rechazada', '2025-12-01 13:35:55');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `rol_id` int NOT NULL,
  `activo` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `password`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `rol_id`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@biblioteca.edu', '$2y$10$kq1lGcQ0upwQEQswjv9aiOVbCWl83yqxNwiliojX2uMqNhLfQijF6', 'Admin', NULL, 'Sistema', NULL, 1, 1, '2025-11-29 21:56:32', '2025-11-30 06:01:34'),
(2, 'luisillo', 'alejocalderon09@gmail.com', '$2y$10$5ElXNV/iTJQendGgWeJxIeM9Bb2kUonTmV4HfBXabGrXFrjHNXVkO', 'Luis', 'Alejandro', 'Calderón', 'Espinosa', 3, 1, '2025-11-30 05:35:39', '2025-11-30 05:35:39'),
(3, 'Profesor', 'prof@gmail.com', '$2y$10$vPZ7s.xB.r86IVsh1MRwB.WwMuGZDSwfpi/X1Q.dTjum0A5Ee04F.', 'Emilio', 'Jonas', 'Don', 'Dimadon', 2, 1, '2025-11-30 05:53:31', '2025-11-30 05:53:31'),
(4, 'neeko', 'ortega@gmail.com', '$2y$10$2Pv4dLsbJR56nrNWeOq5J.7QJcGN/7DoeeXxUnSMpr6X1HvcEt4XO', 'Luis', 'Arturo', 'Ortega', 'Guerra', 3, 1, '2025-11-30 06:58:28', '2025-11-30 06:58:28'),
(5, 'pere', 'pere@gmail.com', '$2y$10$CvtJHxcXPjPpYNBJjN4ceOEC9gY7c9vJS8pHqrPTpAEIS/ktQQpR.', 'Ander', 'Jonas', 'Pereira', 'Colón', 3, 1, '2025-11-30 10:23:09', '2025-11-30 10:23:25'),
(6, 'Eloy', 'eloy@gmail.com', '$2y$10$IyXu0TPUatgpXUX0W4NLyeyOoWgYN0V0F.14rD2BsnhLD69ofVZam', 'Abraham', 'Elhoy', 'García', 'Castrejon', 2, 1, '2025-11-30 10:25:09', '2025-11-30 10:25:09');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
