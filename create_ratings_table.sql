CREATE TABLE IF NOT EXISTS `servicio_calificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alquiler_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `puntuacion` int(1) NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `alquiler_id` (`alquiler_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_calificacion_alquiler` FOREIGN KEY (`alquiler_id`) REFERENCES `alquileres` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_calificacion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
