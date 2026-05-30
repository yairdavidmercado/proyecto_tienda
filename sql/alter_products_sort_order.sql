USE tienda_digital;

SET @db_name := DATABASE();

-- 1) Crear la columna sort_order si no existe
SET @sql := IF(
  (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'sort_order'
  ) = 0,
  'ALTER TABLE products ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER image_url',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Asignar un orden inicial usando el ID actual
-- Se agrega "AND id > 0" para que sea compatible con safe update mode
SET @sql := IF(
  (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'sort_order'
  ) = 1,
  'UPDATE products SET sort_order = id WHERE sort_order = 0 AND id > 0',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Crear índice para mejorar consultas por orden si no existe
SET @sql := IF(
  (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'products'
      AND INDEX_NAME = 'idx_products_sort_order'
  ) = 0,
  'CREATE INDEX idx_products_sort_order ON products(sort_order)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE products
ADD COLUMN price_base_currency VARCHAR(10) NOT NULL DEFAULT 'COP'
AFTER price_label;