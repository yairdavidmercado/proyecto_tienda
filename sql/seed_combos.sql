USE tienda_digital;

SET @db_name := DATABASE();

SET @sql := IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'country_code') = 0,
  'ALTER TABLE categories ADD COLUMN country_code CHAR(2) NOT NULL DEFAULT ''CO'' AFTER description',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'country_codes') = 0,
  'ALTER TABLE categories ADD COLUMN country_codes VARCHAR(64) NOT NULL DEFAULT ''CO'' AFTER country_code',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'products' AND COLUMN_NAME = 'country_code') = 0,
  'ALTER TABLE products ADD COLUMN country_code CHAR(2) NOT NULL DEFAULT ''CO'' AFTER category_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'products' AND COLUMN_NAME = 'country_codes') = 0,
  'ALTER TABLE products ADD COLUMN country_codes VARCHAR(64) NOT NULL DEFAULT ''CO'' AFTER country_code',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE categories
SET country_code = 'CO'
WHERE id > 0
  AND (country_code IS NULL OR country_code = '');

UPDATE categories
SET country_codes = country_code
WHERE id > 0
  AND (country_codes IS NULL OR country_codes = '');

UPDATE products
SET country_code = 'CO'
WHERE id > 0
  AND (country_code IS NULL OR country_code = '');

UPDATE products
SET country_codes = country_code
WHERE id > 0
  AND (country_codes IS NULL OR country_codes = '');

INSERT INTO categories (name, short_label, description, country_code, country_codes, sort_order, status)
SELECT 'Combos', 'COM', 'Combos de plataformas digitales listos para vender con diferentes niveles y precios.', 'CO', 'CO', 1, 1
WHERE NOT EXISTS (
  SELECT 1 FROM categories WHERE name = 'Combos' AND country_code = 'CO'
);

INSERT INTO categories (name, short_label, description, country_code, country_codes, sort_order, status)
SELECT 'Pantallas individuales', 'PI', 'Categoría lista para publicar accesos por pantalla o perfil individual.', 'CO', 'CO', 2, 1
WHERE NOT EXISTS (
  SELECT 1 FROM categories WHERE name = 'Pantallas individuales' AND country_code = 'CO'
);

SET @combos_id := (SELECT id FROM categories WHERE name = 'Combos' AND country_code = 'CO' ORDER BY id ASC LIMIT 1);
SET @pantallas_id := (SELECT id FROM categories WHERE name = 'Pantallas individuales' AND country_code = 'CO' ORDER BY id ASC LIMIT 1);

DELETE FROM products
WHERE category_id = @combos_id
  AND name IN (
    'Combo Especial',
    'Combo Estándar 1',
    'Combo Estándar 2',
    'Combo Básico 1',
    'Combo Básico 2',
    'Combo Ideal 1',
    'Combo Ideal 2',
    'Combo Plus',
    'Combo Perfecto',
    'Combo Extra',
    'Combo Extra 2',
    'Combo Extra 3',
    'Combo Extra 4',
    'Combo Extra 5'
  );

DELETE FROM products
WHERE category_id = @pantallas_id
  AND name IN (
    'Netflix',
    'HBO Max',
    'Prime Video',
    'ViX',
    'Paramount+',
    'Deezer Mes',
    'Deezer Año',
    'Spotify Mes',
    'Spotify 3 Meses',
    'Spotify Año',
    'Disney Premium',
    'Disney 2 ESPN',
    'Canva Mes',
    'Canva Año',
    'ChatGPT Mes',
    'CapCut Mes',
    'Gemini Mes',
    'Gemini Año',
    'Jellyfin Mes',
    'Jellyfin Cuenta 3 Dispositivos',
    'FlujoTV Mes',
    'FlujoTV Cuenta 3 Dispositivos',
    'Win Mes',
    'DGO Mes con Win',
    'DGO sin Win',
    'Porhub Mes',
    'YouTube Mes',
    'Office 365 Año',
    'Office de por Vida'
  );

INSERT INTO products (category_id, country_code, name, short_description, price_label, image_url, featured, status) VALUES
(@combos_id, 'CO', 'Combo Especial', 'Incluye Netflix y ViX. Opción de entrada para clientes que buscan un combo económico y fácil de vender.', '$15.000', 'assets/img/products/combo-premium.svg', 0, 1),
(@combos_id, 'CO', 'Combo Estándar 1', 'Incluye Netflix, HBO Max y ViX. Alternativa balanceada para ofrecer más contenido a un precio accesible.', '$18.000', 'assets/img/products/combo-premium.svg', 0, 1),
(@combos_id, 'CO', 'Combo Estándar 2', 'Incluye Netflix, Prime Video y ViX. Pensado para clientes que priorizan series, películas y variedad.', '$19.000', 'assets/img/products/combo-premium.svg', 0, 1),
(@combos_id, 'CO', 'Combo Básico 1', 'Incluye Netflix, Crunchyroll y Paramount+. Ideal para combinar entretenimiento general con anime.', '$19.000', 'assets/img/products/combo-premium.svg', 0, 1),
(@combos_id, 'CO', 'Combo Básico 2', 'Incluye Netflix, ChatGPT y ViX. Mezcla entretenimiento y productividad en un mismo paquete.', '$28.000', 'assets/img/products/combo-premium.svg', 0, 1),
(@combos_id, 'CO', 'Combo Ideal 1', 'Incluye Netflix, Disney+ y Paramount+. Recomendado para hogares que buscan contenido familiar y estrenos.', '$22.000', 'assets/img/products/combo-premium.svg', 0, 1),
(@combos_id, 'CO', 'Combo Ideal 2', 'Incluye Netflix, HBO Max, Prime Video y Paramount+. Combo completo para clientes que quieren más catálogo sin saltar al plan más alto.', '$22.000', 'assets/img/products/combo-premium.svg', 0, 1),
(@combos_id, 'CO', 'Combo Plus', 'Incluye Netflix, HBO Max, Prime Video, Disney+, Paramount+ y ViX. Uno de los combos más fuertes y atractivos del catálogo.', '$29.000', 'assets/img/products/combo-premium.svg', 1, 1),
(@combos_id, 'CO', 'Combo Perfecto', 'Incluye Netflix, HBO Max, Prime Video, Disney+, Paramount+, ViX, Crunchyroll y un servicio premium adicional. Diseñado para una oferta de alto valor.', '$35.000', 'assets/img/products/combo-premium.svg', 1, 1),
(@combos_id, 'CO', 'Combo Extra', 'Incluye Netflix, ChatGPT, Canva, CapCut y ViX. Orientado a clientes que buscan entretenimiento y herramientas digitales.', '$50.000', 'assets/img/products/combo-premium.svg', 1, 1),
(@combos_id, 'CO', 'Combo Extra 2', 'Incluye Netflix, Spotify y ViX. Ideal para vender entretenimiento de video y música en una sola oferta.', '$23.000', 'assets/img/products/combo-premium.svg', 0, 1),
(@combos_id, 'CO', 'Combo Extra 3', 'Incluye Netflix, HBO Max, Crunchyroll y ViX. Una combinación atractiva para público general y fans del anime.', '$23.000', 'assets/img/products/combo-premium.svg', 0, 1),
(@combos_id, 'CO', 'Combo Extra 4', 'Incluye Netflix, YouTube y ViX. Opción simple y comercial para clientes que consumen contenido diario.', '$23.000', 'assets/img/products/combo-premium.svg', 0, 1),
(@combos_id, 'CO', 'Combo Extra 5', 'Incluye ViX, Paramount+ y un servicio premium adicional. Propuesta compacta para ampliar el catálogo con ticket bajo.', '$18.000', 'assets/img/products/combo-premium.svg', 0, 1);

INSERT INTO products (category_id, country_code, name, short_description, price_label, image_url, featured, status) VALUES
(@pantallas_id, 'CO', 'Netflix', 'Pantalla individual de Netflix para clientes que buscan acceso rápido y una opción de alta rotación.', '$15.000', 'assets/img/products/pantalla-individual.svg', 1, 1),
(@pantallas_id, 'CO', 'HBO Max', 'Pantalla individual de HBO Max con catálogo de series y películas premium.', '$8.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Prime Video', 'Acceso individual a Prime Video como opción económica y fácil de vender.', '$9.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'ViX', 'Pantalla individual de ViX para clientes interesados en contenido latino y deportivo.', '$7.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Paramount+', 'Acceso individual a Paramount+ ideal para complementar otros servicios del catálogo.', '$8.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Deezer Mes', 'Suscripción mensual de Deezer para clientes que buscan música en streaming.', '$10.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Deezer Año', 'Plan anual de Deezer con mejor percepción de ahorro para el cliente final.', '$70.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Spotify Mes', 'Suscripción mensual de Spotify con entrega rápida para venta recurrente.', '$14.000', 'assets/img/products/pantalla-individual.svg', 1, 1),
(@pantallas_id, 'CO', 'Spotify 3 Meses', 'Plan de Spotify por tres meses pensado para ofrecer mejor valor frente al plan mensual.', '$35.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Spotify Año', 'Suscripción anual de Spotify para clientes que buscan estabilidad y ahorro.', '$90.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Disney Premium', 'Acceso premium de Disney para clientes interesados en películas, series y contenido familiar.', '$14.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Disney 2 ESPN', 'Plan combinado de Disney con ESPN como alternativa atractiva para hogares y aficionados al deporte.', '$10.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Canva Mes', 'Suscripción mensual de Canva para diseño rápido y uso personal o comercial.', '$20.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Canva Año', 'Plan anual de Canva con mejor ticket para clientes que usan diseño de forma continua.', '$70.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'ChatGPT Mes', 'Acceso mensual a ChatGPT orientado a estudio, trabajo y productividad.', '$20.000', 'assets/img/products/pantalla-individual.svg', 1, 1),
(@pantallas_id, 'CO', 'CapCut Mes', 'Suscripción mensual de CapCut ideal para edición de video y creación de contenido.', '$25.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Gemini Mes', 'Acceso mensual a Gemini para clientes que buscan herramientas de inteligencia artificial.', '$25.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Gemini Año', 'Plan anual de Gemini orientado a usuarios frecuentes de IA.', '$120.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Jellyfin Mes', 'Acceso mensual a Jellyfin como opción digital especializada para clientes recurrentes.', '$22.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Jellyfin Cuenta 3 Dispositivos', 'Cuenta Jellyfin para tres dispositivos, pensada para compartir en hogar o grupo pequeño.', '$40.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'FlujoTV Mes', 'Suscripción mensual de FlujoTV para clientes que buscan contenido en streaming con fácil renovación.', '$22.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'FlujoTV Cuenta 3 Dispositivos', 'Cuenta de FlujoTV para tres dispositivos con mejor relación precio-beneficio.', '$35.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Win Mes', 'Acceso mensual a Win ideal para clientes interesados en contenido deportivo.', '$22.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'DGO Mes con Win', 'Plan mensual de DGO con Win para una oferta más completa orientada a deporte y entretenimiento.', '$40.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'DGO sin Win', 'Plan mensual de DGO sin Win como alternativa más económica dentro del catálogo.', '$30.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Porhub Mes', 'Acceso mensual a la plataforma indicada en tu catálogo con venta de renovación periódica.', '$18.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'YouTube Mes', 'Suscripción mensual de YouTube para clientes que prefieren contenido sin interrupciones.', '$14.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Office 365 Año', 'Licencia anual de Office 365 orientada a estudio, oficina y trabajo remoto.', '$50.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(@pantallas_id, 'CO', 'Office de por Vida', 'Licencia permanente de Office como producto de mayor ticket en la categoría.', '$170.000', 'assets/img/products/pantalla-individual.svg', 1, 1);
