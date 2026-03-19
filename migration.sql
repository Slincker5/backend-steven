-- Ejecutar en phpMyAdmin o MySQL CLI

-- Agrega columna bloqueado a la tabla usuarios
ALTER TABLE usuarios ADD COLUMN bloqueado TINYINT DEFAULT 0;

-- Soporte de emojis en mensajes personalizados y categorias
ALTER TABLE mensajes_personalizados CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE categoria_mensaje CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE base CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
