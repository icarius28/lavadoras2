-- =====================================================
-- SISTEMA DE AUDITORÍA Y LOGS
-- =====================================================
-- Tabla para registrar todas las acciones importantes del sistema

-- Crear tabla de logs de auditoría
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    usuario_nombre VARCHAR(255) NULL,
    usuario_rol VARCHAR(50) NULL COMMENT 'admin, negocio, domiciliario, cliente',
    accion VARCHAR(100) NOT NULL COMMENT 'CREATE, UPDATE, DELETE, LOGIN, LOGOUT, etc',
    tabla_afectada VARCHAR(100) NULL COMMENT 'Tabla de la base de datos afectada',
    registro_id INT NULL COMMENT 'ID del registro afectado',
    descripcion TEXT NULL COMMENT 'Descripción detallada de la acción',
    datos_anteriores JSON NULL COMMENT 'Datos antes del cambio (para UPDATE y DELETE)',
    datos_nuevos JSON NULL COMMENT 'Datos después del cambio (para CREATE y UPDATE)',
    ip_address VARCHAR(45) NULL COMMENT 'Dirección IP del usuario',
    user_agent TEXT NULL COMMENT 'Navegador/dispositivo del usuario',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_tabla (tabla_afectada),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- EJEMPLOS DE CONSULTAS ÚTILES
-- =====================================================

-- Ver todos los logs de un usuario específico
-- SELECT * FROM audit_logs WHERE usuario_id = 123 ORDER BY fecha_creacion DESC LIMIT 50;

-- Ver todas las modificaciones a una tabla específica
-- SELECT * FROM audit_logs WHERE tabla_afectada = 'usuarios' ORDER BY fecha_creacion DESC;

-- Ver todas las eliminaciones
-- SELECT * FROM audit_logs WHERE accion = 'DELETE' ORDER BY fecha_creacion DESC;

-- Ver logs de las últimas 24 horas
-- SELECT * FROM audit_logs WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY fecha_creacion DESC;

-- Ver logs por tipo de usuario
-- SELECT * FROM audit_logs WHERE usuario_rol = 'admin' ORDER BY fecha_creacion DESC;

-- Estadísticas de acciones por día
-- SELECT DATE(fecha_creacion) as fecha, accion, COUNT(*) as total 
-- FROM audit_logs 
-- GROUP BY DATE(fecha_creacion), accion 
-- ORDER BY fecha DESC;

-- =====================================================
-- ÍNDICES ADICIONALES (OPCIONAL - para mejor rendimiento)
-- =====================================================

-- Índice compuesto para búsquedas frecuentes
CREATE INDEX idx_usuario_fecha ON audit_logs(usuario_id, fecha_creacion);
CREATE INDEX idx_tabla_accion ON audit_logs(tabla_afectada, accion);

-- =====================================================
-- LIMPIEZA AUTOMÁTICA DE LOGS ANTIGUOS (OPCIONAL)
-- =====================================================
-- Ejecutar periódicamente para mantener la tabla manejable
-- Eliminar logs mayores a 6 meses:
-- DELETE FROM audit_logs WHERE fecha_creacion < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- O crear un evento automático (requiere permisos EVENT):
/*
DELIMITER $$
CREATE EVENT IF NOT EXISTS cleanup_old_logs
ON SCHEDULE EVERY 1 WEEK
DO
BEGIN
    DELETE FROM audit_logs WHERE fecha_creacion < DATE_SUB(NOW(), INTERVAL 6 MONTH);
END$$
DELIMITER ;
*/

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

-- Verificar que la tabla se creó correctamente
SHOW CREATE TABLE audit_logs;

-- Ver estructura de la tabla
DESCRIBE audit_logs;

-- Contar registros actuales
SELECT COUNT(*) as total_logs FROM audit_logs;
