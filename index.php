<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/functions.php';

$settings = get_settings($pdo);
$categories = get_categories($pdo);
$activeCategory = isset($_GET['categoria']) ? (int) $_GET['categoria'] : null;
$products = get_products($pdo, $activeCategory ?: null);

require __DIR__ . '/includes/header.php';
?>

<nav class="navbar navbar-expand-lg sticky-top navbar-dark glass-nav">
    <div class="container py-2">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
            <span class="brand-dot"></span>
            <?= e($settings['site_name'] ?? 'Pixel Play'); ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto me-3">
                <li class="nav-item"><a class="nav-link" href="#categorias">Categorías</a></li>
                <li class="nav-item"><a class="nav-link" href="#productos">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
            </ul>
            <a href="admin/login.php" class="btn btn-light rounded-pill px-4">Iniciar sesión</a>
        </div>
    </div>
</nav>

<header class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-100 py-5">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="glass-panel p-4 p-md-5">
                    <span class="badge rounded-pill bg-secondary-subtle text-light border border-secondary mb-3">Catálogo digital automatizado</span>
                    <h1 class="display-4 fw-bold text-white mb-3"><?= e($settings['hero_title'] ?? 'Combos, cuentas y pantallas listas para vender'); ?></h1>
                    <p class="lead text-secondary mb-4"><?= e($settings['hero_subtitle'] ?? 'Diseño limpio, visual premium y experiencia rápida para convertir visitas en pedidos.'); ?></p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#productos" class="btn btn-primary btn-lg rounded-pill px-4">Ver catálogo</a>
                        <a href="<?= e(whatsapp_link($settings['whatsapp_number'] ?? '573000000000', $settings['whatsapp_message_general'] ?? 'Hola, quiero más información sobre el catálogo digital.')); ?>" target="_blank" class="btn btn-outline-light btn-lg rounded-pill px-4">Pedir por WhatsApp</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-cards-wrap">
                    <div class="showcase-card floating-card">
                        <div class="small text-secondary mb-2">Destacado</div>
                        <h3 class="text-white fw-bold">Experiencia visual moderna</h3>
                        <p class="text-secondary mb-0">Estilo negro, blanco y gris con transparencias, tarjetas curvas y sensación premium tipo ecosistema Mac.</p>
                    </div>
                    <div class="showcase-card secondary-card">
                        <div class="stat-bubble">
                            <strong>4</strong>
                            <span>Categorías</span>
                        </div>
                        <ul class="list-unstyled text-secondary mb-0 mt-3 small">
                            <li>Combos por pantallas</li>
                            <li>Combos por cuentas</li>
                            <li>Pantallas individuales</li>
                            <li>Cuentas completas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section id="categorias" class="section-padding bg-soft-dark">
    <div class="container">
        <div class="section-title text-center mb-5">
            <span class="eyebrow">Explora</span>
            <h2 class="text-white">Categorías</h2>
            <p class="text-secondary">Organiza el catálogo en bloques simples para que el cliente entienda rápido qué puede comprar.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-6 col-xl-3">
                    <a href="?categoria=<?= (int) $category['id']; ?>#productos" class="category-card text-decoration-none d-block h-100">
                        <div class="category-icon"><?= e($category['short_label'] ?: strtoupper(mb_substr($category['name'], 0, 2))); ?></div>
                        <h3 class="h5 text-white"><?= e($category['name']); ?></h3>
                        <p class="text-secondary mb-0"><?= e($category['description']); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="productos" class="section-padding">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <span class="eyebrow">Catálogo</span>
                <h2 class="text-white mb-1">Productos disponibles</h2>
                <p class="text-secondary mb-0">Selecciona un producto y envía el pedido directo a WhatsApp.</p>
            </div>
            <div class="filter-pills d-flex flex-wrap gap-2">
                <a href="index.php#productos" class="btn btn-sm rounded-pill <?= $activeCategory ? 'btn-outline-light' : 'btn-light'; ?>">Todos</a>
                <?php foreach ($categories as $category): ?>
                    <a href="?categoria=<?= (int) $category['id']; ?>#productos" class="btn btn-sm rounded-pill <?= $activeCategory === (int) $category['id'] ? 'btn-light' : 'btn-outline-light'; ?>">
                        <?= e($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!$products): ?>
                <div class="col-12">
                    <div class="empty-state glass-panel p-5 text-center">
                        <h3 class="text-white">No hay productos en esta categoría</h3>
                        <p class="text-secondary mb-0">Agrega productos desde el panel administrativo.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($products as $product):
                $message = "Hola, quiero pedir el producto {$product['name']} ({$product['category_name']}) por {$product['price_label']}.";
                $image = $product['image_url'] ?: 'https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?auto=format&fit=crop&w=900&q=80';
            ?>
                <div class="col-md-6 col-xl-4">
                    <div class="product-card h-100">
                        <div class="product-image-wrap">
                            <img src="<?= e($image); ?>" class="product-image" alt="<?= e($product['name']); ?>">
                            <?php if ((int) $product['featured'] === 1): ?>
                                <span class="featured-badge">Popular</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-content p-4 d-flex flex-column h-100">
                            <div class="small text-secondary mb-2"><?= e($product['category_name']); ?></div>
                            <h3 class="h5 text-white"><?= e($product['name']); ?></h3>
                            <p class="text-secondary"><?= e($product['short_description']); ?></p>
                            <div class="mt-auto d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <span class="price-tag"><?= e($product['price_label']); ?></span>
                                </div>
                                <a href="<?= e(whatsapp_link($settings['whatsapp_number'] ?? '573000000000', $message)); ?>" target="_blank" class="btn btn-primary rounded-pill px-3">Pedir</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-padding bg-soft-dark">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <div class="glass-panel p-4 p-lg-5 h-100">
                    <span class="eyebrow">Beneficios</span>
                    <h2 class="text-white">Diseñado para vender fácil</h2>
                    <ul class="benefits-list">
                        <li>Vista rápida desde celular, tablet o computador.</li>
                        <li>Productos organizados en categorías fáciles de entender.</li>
                        <li>Botón directo a WhatsApp para cerrar pedidos.</li>
                        <li>Panel básico para editar textos, precios y productos.</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6" id="contacto">
                <div class="contact-card p-4 p-lg-5">
                    <span class="eyebrow">Contacto</span>
                    <h2 class="text-white">Atención inmediata</h2>
                    <p class="text-secondary">Conecta al cliente con un mensaje precargado para acelerar la compra.</p>
                    <a class="btn btn-light rounded-pill px-4" target="_blank" href="<?= e(whatsapp_link($settings['whatsapp_number'] ?? '573000000000', $settings['whatsapp_message_general'] ?? 'Hola, quiero más información sobre el catálogo digital.')); ?>">Hablar por WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <p class="mb-0 text-secondary">© <?= date('Y'); ?> <?= e($settings['site_name'] ?? 'Tienda digital'); ?>.</p>
        <p class="mb-0 text-secondary">Desarrollado con Bootstrap 5, PHP y MySQL.</p>
    </div>
</footer>

<?php require __DIR__ . '/includes/footer.php'; ?>
