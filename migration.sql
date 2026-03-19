-- Ejecutar en phpMyAdmin o MySQL CLI
-- Agrega columna bloqueado a la tabla usuarios
ALTER TABLE usuarios ADD COLUMN bloqueado TINYINT DEFAULT 0;
