-- Script para agregar campos de seguridad a tbl_votantes
-- Ejecutar este script una sola vez

-- Agregar campo ultimo_acceso (guarda la fecha del último login exitoso)
ALTER TABLE tbl_votantes
ADD COLUMN IF NOT EXISTS ultimo_acceso DATETIME NULL DEFAULT NULL
COMMENT 'Fecha y hora del último acceso exitoso';

-- Agregar campo intentos_login (contador de intentos fallidos)
ALTER TABLE tbl_votantes
ADD COLUMN IF NOT EXISTS intentos_login INT NOT NULL DEFAULT 0
COMMENT 'Número de intentos fallidos de login consecutivos';

-- Agregar campo cuenta_bloqueada_hasta (fecha hasta la que está bloqueada la cuenta)
ALTER TABLE tbl_votantes
ADD COLUMN IF NOT EXISTS cuenta_bloqueada_hasta DATETIME NULL DEFAULT NULL
COMMENT 'Fecha hasta la que la cuenta está bloqueada por intentos fallidos';

-- Verificar que los campos se agregaron correctamente
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, IS_NULLABLE, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'tbl_votantes'
AND COLUMN_NAME IN ('ultimo_acceso', 'intentos_login', 'cuenta_bloqueada_hasta');
