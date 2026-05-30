CREATE DATABASE IF NOT EXISTS tienda_digital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tienda_digital;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    short_label VARCHAR(8) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'CO',
    country_codes VARCHAR(64) NOT NULL DEFAULT 'CO',
    sort_order INT NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'CO',
    country_codes VARCHAR(64) NOT NULL DEFAULT 'CO',
    name VARCHAR(150) NOT NULL,
    short_description TEXT DEFAULT NULL,
    price_label VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL
);

INSERT INTO users (name, email, password, status) VALUES
('Administrador', 'admin@demo.com', '$2y$12$P0.ok8SjohrfOeuwBZKj7eTHWnw0PHEcSBD2IXbporwdLoBx0dlLi', 1);

INSERT INTO categories (name, short_label, description, country_code, country_codes, sort_order, status) VALUES
('Combos', 'COM', 'Combos de plataformas digitales listos para vender con diferentes niveles y precios.', 'CO', 'CO', 1, 1),
('Pantallas individuales', 'PI', 'Categoría lista para publicar accesos por pantalla o perfil individual.', 'CO', 'CO', 2, 1);

INSERT INTO products (category_id, country_code, name, short_description, price_label, image_url, featured, status) VALUES
(1, 'CO', 'Combo Especial', 'Incluye Netflix y ViX. Opción de entrada para clientes que buscan un combo económico y fácil de vender.', '$15.000', 'assets/img/products/combo-premium.svg', 0, 1),
(1, 'CO', 'Combo Estándar 1', 'Incluye Netflix, HBO Max y ViX. Alternativa balanceada para ofrecer más contenido a un precio accesible.', '$18.000', 'assets/img/products/combo-premium.svg', 0, 1),
(1, 'CO', 'Combo Estándar 2', 'Incluye Netflix, Prime Video y ViX. Pensado para clientes que priorizan series, películas y variedad.', '$19.000', 'assets/img/products/combo-premium.svg', 0, 1),
(1, 'CO', 'Combo Básico 1', 'Incluye Netflix, Crunchyroll y Paramount+. Ideal para combinar entretenimiento general con anime.', '$19.000', 'assets/img/products/combo-premium.svg', 0, 1),
(1, 'CO', 'Combo Básico 2', 'Incluye Netflix, ChatGPT y ViX. Mezcla entretenimiento y productividad en un mismo paquete.', '$28.000', 'assets/img/products/combo-premium.svg', 0, 1),
(1, 'CO', 'Combo Ideal 1', 'Incluye Netflix, Disney+ y Paramount+. Recomendado para hogares que buscan contenido familiar y estrenos.', '$22.000', 'assets/img/products/combo-premium.svg', 0, 1),
(1, 'CO', 'Combo Ideal 2', 'Incluye Netflix, HBO Max, Prime Video y Paramount+. Combo completo para clientes que quieren más catálogo sin saltar al plan más alto.', '$22.000', 'assets/img/products/combo-premium.svg', 0, 1),
(1, 'CO', 'Combo Plus', 'Incluye Netflix, HBO Max, Prime Video, Disney+, Paramount+ y ViX. Uno de los combos más fuertes y atractivos del catálogo.', '$29.000', 'assets/img/products/combo-premium.svg', 1, 1),
(1, 'CO', 'Combo Perfecto', 'Incluye Netflix, HBO Max, Prime Video, Disney+, Paramount+, ViX, Crunchyroll y un servicio premium adicional. Diseñado para una oferta de alto valor.', '$35.000', 'assets/img/products/combo-premium.svg', 1, 1),
(1, 'CO', 'Combo Extra', 'Incluye Netflix, ChatGPT, Canva, CapCut y ViX. Orientado a clientes que buscan entretenimiento y herramientas digitales.', '$50.000', 'assets/img/products/combo-premium.svg', 1, 1),
(1, 'CO', 'Combo Extra 2', 'Incluye Netflix, Spotify y ViX. Ideal para vender entretenimiento de video y música en una sola oferta.', '$23.000', 'assets/img/products/combo-premium.svg', 0, 1),
(1, 'CO', 'Combo Extra 3', 'Incluye Netflix, HBO Max, Crunchyroll y ViX. Una combinación atractiva para público general y fans del anime.', '$23.000', 'assets/img/products/combo-premium.svg', 0, 1),
(1, 'CO', 'Combo Extra 4', 'Incluye Netflix, YouTube y ViX. Opción simple y comercial para clientes que consumen contenido diario.', '$23.000', 'assets/img/products/combo-premium.svg', 0, 1),
(1, 'CO', 'Combo Extra 5', 'Incluye ViX, Paramount+ y un servicio premium adicional. Propuesta compacta para ampliar el catálogo con ticket bajo.', '$18.000', 'assets/img/products/combo-premium.svg', 0, 1),
(2, 'CO', 'Netflix', 'Pantalla individual de Netflix para clientes que buscan acceso rápido y una opción de alta rotación.', '$15.000', 'assets/img/products/pantalla-individual.svg', 1, 1),
(2, 'CO', 'HBO Max', 'Pantalla individual de HBO Max con catálogo de series y películas premium.', '$8.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Prime Video', 'Acceso individual a Prime Video como opción económica y fácil de vender.', '$9.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'ViX', 'Pantalla individual de ViX para clientes interesados en contenido latino y deportivo.', '$7.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Paramount+', 'Acceso individual a Paramount+ ideal para complementar otros servicios del catálogo.', '$8.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Deezer Mes', 'Suscripción mensual de Deezer para clientes que buscan música en streaming.', '$10.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Deezer Año', 'Plan anual de Deezer con mejor percepción de ahorro para el cliente final.', '$70.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Spotify Mes', 'Suscripción mensual de Spotify con entrega rápida para venta recurrente.', '$14.000', 'assets/img/products/pantalla-individual.svg', 1, 1),
(2, 'CO', 'Spotify 3 Meses', 'Plan de Spotify por tres meses pensado para ofrecer mejor valor frente al plan mensual.', '$35.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Spotify Año', 'Suscripción anual de Spotify para clientes que buscan estabilidad y ahorro.', '$90.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Disney Premium', 'Acceso premium de Disney para clientes interesados en películas, series y contenido familiar.', '$14.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Disney 2 ESPN', 'Plan combinado de Disney con ESPN como alternativa atractiva para hogares y aficionados al deporte.', '$10.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Canva Mes', 'Suscripción mensual de Canva para diseño rápido y uso personal o comercial.', '$20.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Canva Año', 'Plan anual de Canva con mejor ticket para clientes que usan diseño de forma continua.', '$70.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'ChatGPT Mes', 'Acceso mensual a ChatGPT orientado a estudio, trabajo y productividad.', '$20.000', 'assets/img/products/pantalla-individual.svg', 1, 1),
(2, 'CO', 'CapCut Mes', 'Suscripción mensual de CapCut ideal para edición de video y creación de contenido.', '$25.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Gemini Mes', 'Acceso mensual a Gemini para clientes que buscan herramientas de inteligencia artificial.', '$25.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Gemini Año', 'Plan anual de Gemini orientado a usuarios frecuentes de IA.', '$120.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Jellyfin Mes', 'Acceso mensual a Jellyfin como opción digital especializada para clientes recurrentes.', '$22.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Jellyfin Cuenta 3 Dispositivos', 'Cuenta Jellyfin para tres dispositivos, pensada para compartir en hogar o grupo pequeño.', '$40.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'FlujoTV Mes', 'Suscripción mensual de FlujoTV para clientes que buscan contenido en streaming con fácil renovación.', '$22.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'FlujoTV Cuenta 3 Dispositivos', 'Cuenta de FlujoTV para tres dispositivos con mejor relación precio-beneficio.', '$35.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Win Mes', 'Acceso mensual a Win ideal para clientes interesados en contenido deportivo.', '$22.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'DGO Mes con Win', 'Plan mensual de DGO con Win para una oferta más completa orientada a deporte y entretenimiento.', '$40.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'DGO sin Win', 'Plan mensual de DGO sin Win como alternativa más económica dentro del catálogo.', '$30.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Porhub Mes', 'Acceso mensual a la plataforma indicada en tu catálogo con venta de renovación periódica.', '$18.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'YouTube Mes', 'Suscripción mensual de YouTube para clientes que prefieren contenido sin interrupciones.', '$14.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Office 365 Año', 'Licencia anual de Office 365 orientada a estudio, oficina y trabajo remoto.', '$50.000', 'assets/img/products/pantalla-individual.svg', 0, 1),
(2, 'CO', 'Office de por Vida', 'Licencia permanente de Office como producto de mayor ticket en la categoría.', '$170.000', 'assets/img/products/pantalla-individual.svg', 1, 1);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Pixel Play Store'),
/* ('site_description', 'Catálogo digital moderno con pedidos rápidos por WhatsApp.'),
('hero_title', 'Combos, cuentas y pantallas en una vitrina premium'),
('hero_subtitle', 'Pantallas streaming con entrega inmediata, precios claros y atencion directa por WhatsApp.'), */
('whatsapp_number', '573001234567'),
('whatsapp_number_co', '573001234567'),
('whatsapp_number_es', '34600111222'),
('whatsapp_number_mx', '5215512345678'),
('whatsapp_number_us', '12025550123'),
('whatsapp_message_co', 'Hola, quiero informacion sobre las pantallas streaming disponibles para Colombia.'),
('whatsapp_message_es', 'Hola, quiero informacion sobre las pantallas streaming disponibles para Espana.'),
('whatsapp_message_mx', 'Hola, quiero informacion sobre las pantallas streaming disponibles para Mexico.'),
('whatsapp_message_us', 'Hello, I want information about the available streaming screens for the United States.'),
('payment_methods_co', '🔰MEDIOS DE PAGO COLOMBIA 🔰\n\n🟡NEQUI 3197128850\n🔴DAVIPLATA 3197128850\n⚪MOVII 3197128850\n🟢CLARO PAY 3197128850\n⚫DALE 3197128850\n\n🟥AHORROS DAVIVIENDA\n477500036140\n\n🟨AHORROS BANCOLOMBIA\n91286093448\n\n🟩LULOBANK 477165563567\n\n🟪AHORROS NU BANK 30597840\n\n📌LLAVE NEQUI @3197128850'),
('payment_methods_es', 'Configura aqui los medios de pago para Espana.'),
('payment_methods_mx', 'Configura aqui los medios de pago para Mexico.'),
('payment_methods_us', 'Configure payment methods for the United States here.'),
('rate_cop_to_eur', '0.00023'),
('rate_cop_to_mxn', '0.0044'),
('rate_cop_to_usd', '0.00026');


CREATE TABLE orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    reference VARCHAR(80) NOT NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'CO',
    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    amount_cop INT UNSIGNED NOT NULL DEFAULT 0,
    amount_in_cents INT UNSIGNED NOT NULL DEFAULT 0,
    currency CHAR(3) NOT NULL DEFAULT 'COP',
    status VARCHAR(30) NOT NULL DEFAULT 'PENDING',
    wompi_transaction_id VARCHAR(120) DEFAULT NULL,
    wompi_payment_method VARCHAR(80) DEFAULT NULL,
    wompi_status_message VARCHAR(255) DEFAULT NULL,
    raw_event LONGTEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orders_reference (reference),
    KEY idx_orders_status (status),
    KEY idx_orders_transaction (wompi_transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(180) NOT NULL,
    category_name VARCHAR(180) DEFAULT NULL,
    unit_price_cop INT UNSIGNED NOT NULL DEFAULT 0,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    subtotal_cop INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_order_items_order (order_id),
    KEY idx_order_items_product (product_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;