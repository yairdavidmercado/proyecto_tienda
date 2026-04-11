CREATE DATABASE IF NOT EXISTS tienda_digital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tienda_digital;

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
    sort_order INT NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    short_description TEXT DEFAULT NULL,
    price_label VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
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

INSERT INTO categories (name, short_label, description, sort_order, status) VALUES
('Combos por pantallas', 'CP', 'Paquetes pensados para clientes que compran acceso por perfil o pantalla.', 1, 1),
('Combos por cuentas', 'CC', 'Opciones completas para quienes necesitan cuentas listas para usar.', 2, 1),
('Pantallas individuales', 'PI', 'Productos unitarios para venta rápida y ticket de entrada bajo.', 3, 1),
('Cuentas completas', 'CT', 'Planes full para clientes que prefieren control total del acceso.', 4, 1);

INSERT INTO products (category_id, name, short_description, price_label, image_url, featured, status) VALUES
(1, 'Combo Streaming Plus', 'Incluye varias plataformas en un solo paquete con entrega rápida.', '$35.000 / mes', 'https://images.unsplash.com/photo-1586892478025-2b5472316f22?auto=format&fit=crop&w=900&q=80', 1, 1),
(2, 'Cuenta Full Premium', 'Acceso completo ideal para usuarios que desean una cuenta dedicada.', '$52.000 / mes', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80', 1, 1),
(3, 'Pantalla Individual Pro', 'Alternativa económica y clara para compras inmediatas.', '$14.000 / mes', 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=900&q=80', 0, 1),
(4, 'Cuenta Completa Max', 'Cuenta completa con enfoque en continuidad y mayor valor percibido.', '$65.000 / mes', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80', 0, 1);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Pixel Play Store'),
('site_description', 'Catálogo digital moderno con pedidos rápidos por WhatsApp.'),
('hero_title', 'Combos, cuentas y pantallas en una vitrina premium'),
('hero_subtitle', 'Una página moderna, limpia y responsiva para mostrar productos digitales y convertir visitas en ventas.'),
('whatsapp_number', '573001234567'),
('whatsapp_number_co', '573001234567'),
('whatsapp_number_es', '34600111222'),
('whatsapp_number_mx', '5215512345678'),
('whatsapp_number_us', '12025550123'),
('whatsapp_message_general', 'Hola, quiero información sobre los productos digitales disponibles.'),
('rate_cop_to_eur', '0.00023'),
('rate_cop_to_mxn', '0.0044'),
('rate_cop_to_usd', '0.00026');
