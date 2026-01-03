-- =====================================================
-- SCRIPT DE MIGRACIÓN: CENTRALIZACIÓN DE PRECIOS
-- =====================================================
-- Este script migra los precios de negocios individuales a precios globales
-- Ejecutar en el servidor de producción

-- PASO 1: Hacer backup de la tabla actual
-- IMPORTANTE: Ejecutar este comando ANTES de cualquier cambio
CREATE TABLE precios_lavado_backup AS SELECT * FROM precios_lavado;

-- PASO 2: Verificar precios actuales por negocio
-- Este query te muestra todos los precios existentes
SELECT id_negocio, tipo_lavadora, tipo_servicio, precio 
FROM precios_lavado 
ORDER BY id_negocio, tipo_lavadora, tipo_servicio;

-- PASO 3: Eliminar todos los precios existentes (opcional)
-- Solo ejecutar si quieres empezar desde cero
-- DELETE FROM precios_lavado WHERE id_negocio != 0;

-- PASO 4: Insertar precios globales (id_negocio = 0)
-- Opción A: Copiar precios de un negocio específico como base
-- Reemplaza '1' con el ID del negocio que quieres usar como referencia
INSERT INTO precios_lavado (tipo_lavadora, tipo_servicio, precio, id_negocio)
SELECT tipo_lavadora, tipo_servicio, precio, 0 as id_negocio
FROM precios_lavado 
WHERE id_negocio = 1
ON DUPLICATE KEY UPDATE precio = VALUES(precio);

-- Opción B: Insertar precios globales manualmente
-- Descomenta y ajusta los valores según tus necesidades
/*
DELETE FROM precios_lavado WHERE id_negocio = 0;

INSERT INTO precios_lavado (tipo_lavadora, tipo_servicio, precio, id_negocio) VALUES
-- Manual doble tina sin bomba
('Manual doble tina sin bomba', 'normal', 15000, 0),
('Manual doble tina sin bomba', '24horas', 20000, 0),
('Manual doble tina sin bomba', 'nocturno', 18000, 0),

-- Manual doble tina con bomba
('Manual doble tina con bomba', 'normal', 18000, 0),
('Manual doble tina con bomba', '24horas', 23000, 0),
('Manual doble tina con bomba', 'nocturno', 21000, 0),

-- Automática de 18 libras
('Automática de 18 libras', 'normal', 20000, 0),
('Automática de 18 libras', '24horas', 25000, 0),
('Automática de 18 libras', 'nocturno', 23000, 0),

-- Automática de 24 libras
('Automática de 24 libras', 'normal', 25000, 0),
('Automática de 24 libras', '24horas', 30000, 0),
('Automática de 24 libras', 'nocturno', 28000, 0);
*/

-- PASO 5: Verificar que los precios globales se crearon correctamente
SELECT * FROM precios_lavado WHERE id_negocio = 0 
ORDER BY tipo_lavadora, tipo_servicio;

-- PASO 6: (OPCIONAL) Eliminar precios por negocio después de verificar
-- Solo ejecutar después de confirmar que todo funciona correctamente
-- DELETE FROM precios_lavado WHERE id_negocio != 0;

-- PASO 7: Verificar que solo existen precios globales
SELECT COUNT(*) as total_precios_globales FROM precios_lavado WHERE id_negocio = 0;
SELECT COUNT(*) as total_precios_negocio FROM precios_lavado WHERE id_negocio != 0;

-- =====================================================
-- QUERIES DE VERIFICACIÓN
-- =====================================================

-- Ver todos los precios actuales
SELECT 
    CASE WHEN id_negocio = 0 THEN 'GLOBAL' ELSE CONCAT('Negocio ', id_negocio) END as tipo,
    tipo_lavadora,
    tipo_servicio,
    precio
FROM precios_lavado
ORDER BY id_negocio, tipo_lavadora, tipo_servicio;

-- Contar precios por tipo
SELECT 
    CASE WHEN id_negocio = 0 THEN 'GLOBAL' ELSE 'POR NEGOCIO' END as tipo,
    COUNT(*) as cantidad
FROM precios_lavado
GROUP BY CASE WHEN id_negocio = 0 THEN 'GLOBAL' ELSE 'POR NEGOCIO' END;

-- =====================================================
-- ROLLBACK (en caso de necesitar revertir)
-- =====================================================
/*
-- Restaurar desde backup
DELETE FROM precios_lavado;
INSERT INTO precios_lavado SELECT * FROM precios_lavado_backup;

-- Verificar restauración
SELECT COUNT(*) FROM precios_lavado;
SELECT COUNT(*) FROM precios_lavado_backup;
*/
