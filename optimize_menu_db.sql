-- OPTIMIZACIÓN DE BASE DE DATOS: Módulo de Menú
-- Ejecute estos comandos en su terminal de PostgreSQL o pgAdmin

-- 1. Índices para las claves foráneas en la tabla de relación
-- Esto acelera significativamente los JOINs entre módulos y submódulos
CREATE INDEX IF NOT EXISTS idx_msm_idmodulofk ON modulo_sub_modulo (idmodulofk);

CREATE INDEX IF NOT EXISTS idx_msm_idsubmodulofk ON modulo_sub_modulo (idsubmodulofk);

-- 2. Índices para las columnas de ordenamiento
-- Mejora la velocidad del ORDER BY usado en getMenuRelaciones
CREATE INDEX IF NOT EXISTS idx_modulos_orden ON modulos (orden);

CREATE INDEX IF NOT EXISTS idx_modulo_sub_modulo_orden ON modulo_sub_modulo (orden);

-- 3. Análisis de las tablas para actualizar estadísticas
ANALYZE modulos;

ANALYZE sub_modulos;

ANALYZE modulo_sub_modulo;