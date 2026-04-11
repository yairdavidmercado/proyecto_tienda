<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/functions.php';

$settings = get_settings($pdo);
$categories = get_categories($pdo);
$activeCategory = isset($_GET['categoria']) ? (int) $_GET['categoria'] : null;
$products = get_products($pdo, $activeCategory ?: null);
$rateCopToEur = isset($settings['rate_cop_to_eur']) && is_numeric($settings['rate_cop_to_eur']) ? (float) $settings['rate_cop_to_eur'] : 0.00023;
$rateCopToMxn = isset($settings['rate_cop_to_mxn']) && is_numeric($settings['rate_cop_to_mxn']) ? (float) $settings['rate_cop_to_mxn'] : 0.0044;
$rateCopToUsd = isset($settings['rate_cop_to_usd']) && is_numeric($settings['rate_cop_to_usd']) ? (float) $settings['rate_cop_to_usd'] : 0.00026;
$defaultWhatsapp = (string) preg_replace('/\D+/', '', $settings['whatsapp_number'] ?? '573000000000');
$whatsappCo = (string) preg_replace('/\D+/', '', $settings['whatsapp_number_co'] ?? $defaultWhatsapp);
$whatsappEs = (string) preg_replace('/\D+/', '', $settings['whatsapp_number_es'] ?? $defaultWhatsapp);
$whatsappMx = (string) preg_replace('/\D+/', '', $settings['whatsapp_number_mx'] ?? $defaultWhatsapp);
$whatsappUs = (string) preg_replace('/\D+/', '', $settings['whatsapp_number_us'] ?? $defaultWhatsapp);

require __DIR__ . '/includes/header.php';
?>

<nav class="navbar navbar-expand-lg sticky-top navbar-light glass-nav">
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
                <li class="nav-item"><a class="nav-link" href="#categorias" data-i18n="nav_categories">Categorias</a></li>
                <li class="nav-item"><a class="nav-link" href="#productos" data-i18n="nav_products">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="#contacto" data-i18n="nav_contact">Contacto</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 nav-session-actions">
                <label class="visually-hidden" for="countrySelector" data-i18n="country_select_label">Selecciona tu pais</label>
                <select
                    id="countrySelector"
                    class="form-select nav-country-select"
                    aria-label="Selecciona tu pais"
                    data-rate-cop-to-eur="<?= e((string) $rateCopToEur); ?>"
                    data-rate-cop-to-mxn="<?= e((string) $rateCopToMxn); ?>"
                    data-rate-cop-to-usd="<?= e((string) $rateCopToUsd); ?>"
                    data-whatsapp-co="<?= e($whatsappCo); ?>"
                    data-whatsapp-es="<?= e($whatsappEs); ?>"
                    data-whatsapp-mx="<?= e($whatsappMx); ?>"
                    data-whatsapp-us="<?= e($whatsappUs); ?>"
                >
                    <option value="CO" data-i18n="country_co">🇨🇴 Colombia</option>
                    <option value="ES" data-i18n="country_es">🇪🇸 Espana</option>
                    <option value="MX" data-i18n="country_mx">🇲🇽 Mexico</option>
                    <option value="US" data-i18n="country_us">🇺🇸 Estados Unidos</option>
                </select>
                <a href="#carrito" id="openCartBtn" class="btn btn-outline-light rounded-pill px-3 position-relative">
                    <span data-i18n="cart_nav">Carrito</span>
                    <span id="cartCountBadge" class="badge rounded-pill text-bg-light ms-1">0</span>
                </a>
                <a href="admin/login.php" class="btn btn-light rounded-pill px-4" data-i18n="login">Iniciar sesion</a>
            </div>
        </div>
    </div>
</nav>

<div id="heroSlider" class="carousel slide hero-slider" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?auto=format&fit=crop&w=1600&q=80" class="d-block w-100 hero-slide-img" alt="Streaming">
            <div class="carousel-caption hero-slide-caption">
                <span class="slide-eyebrow" data-i18n="hero_badge">Catalogo digital automatizado</span>
                <h2 data-i18n="hero_title">Combos, cuentas y pantallas listas para vender</h2>
                <a href="#productos" class="btn btn-light rounded-pill px-5 mt-2" data-i18n="hero_cta_catalog">Ver catalogo</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="https://images.unsplash.com/photo-1614028674026-a65e31bfd27c?auto=format&fit=crop&w=1600&q=80" class="d-block w-100 hero-slide-img" alt="Pantallas premium">
            <div class="carousel-caption hero-slide-caption">
                <span class="slide-eyebrow">Pantallas y cuentas</span>
                <h2>Los mejores precios en streaming premium</h2>
                <a href="#productos" class="btn btn-light rounded-pill px-5 mt-2" data-i18n="hero_cta_catalog">Ver catalogo</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?auto=format&fit=crop&w=1600&q=80" class="d-block w-100 hero-slide-img" alt="Digital global">
            <div class="carousel-caption hero-slide-caption">
                <span class="slide-eyebrow">Colombia &middot; Espa&ntilde;a &middot; M&eacute;xico &middot; USA</span>
                <h2>Disponible para todo el mundo</h2>
                <a href="#productos" class="btn btn-light rounded-pill px-5 mt-2" data-i18n="hero_cta_catalog">Ver catalogo</a>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>
</div>

<section class="hero-text-section">
    <div class="container">
        <div class="hero-full-blurb">
            <h1 class="hero-main-title" data-i18n="hero_title">Combos, cuentas y pantallas listas para vender</h1>
            <p class="hero-main-subtitle" data-i18n="hero_subtitle">Diseno limpio, visual premium y experiencia rapida para convertir visitas en pedidos.</p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="#productos" class="btn btn-primary btn-lg rounded-pill px-5" data-i18n="hero_cta_catalog">Ver catalogo</a>
                <a
                    href="#"
                    target="_blank"
                    class="btn btn-whatsapp btn-lg rounded-pill px-5 js-country-whatsapp-link"
                    data-whatsapp-message="<?= e($settings['whatsapp_message_general'] ?? 'Hola, quiero mas informacion sobre el catalogo digital.'); ?>"
                ><i class="bi bi-whatsapp"></i><span data-i18n="hero_cta_whatsapp">Pedir por WhatsApp</span></a>
            </div>
        </div>
    </div>
</section>

<section id="productos" class="section-padding products-priority-section">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <span class="eyebrow" data-i18n="catalog">Catalogo</span>
                <h2 class="text-white mb-1" data-i18n="products_available">Productos disponibles</h2>
                <p class="text-secondary mb-0" data-i18n="products_help">Selecciona cantidades y agrega al carrito para enviar un pedido completo.</p>
            </div>
            <div class="filter-pills d-flex flex-wrap gap-2">
                <a href="index.php#productos" class="btn btn-sm rounded-pill <?= $activeCategory ? 'btn-outline-light' : 'btn-light'; ?>" data-i18n="all">Todos</a>
                <?php foreach ($categories as $category): ?>
                    <a href="?categoria=<?= (int) $category['id']; ?>#productos"
                       class="btn btn-sm rounded-pill <?= $activeCategory === (int) $category['id'] ? 'btn-light' : 'btn-outline-light'; ?>"
                       data-cat-id="<?= (int) $category['id']; ?>"
                       data-cat-field="name"
                       data-original="<?= e($category['name']); ?>">
                        <?= e($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!$products): ?>
                <div class="col-12">
                    <div class="empty-state glass-panel p-5 text-center">
                        <h3 class="text-white" data-i18n="no_products">No hay productos en esta categoria</h3>
                        <p class="text-secondary mb-0" data-i18n="no_products_help">Agrega productos desde el panel administrativo.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($products as $product):
                $image = $product['image_url'] ?: 'https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?auto=format&fit=crop&w=900&q=80';
                $basePriceCop = (int) preg_replace('/\D+/', '', (string) $product['price_label']);
                $cleanCopLabel = '$' . number_format($basePriceCop, 0, ',', '.');
            ?>
                <div class="col-md-6 col-xl-4">
                    <div class="product-card h-100">
                        <div class="product-image-wrap">
                            <img src="<?= e($image); ?>" class="product-image" alt="<?= e($product['name']); ?>">
                            <?php if ((int) $product['featured'] === 1): ?>
                                <span class="featured-badge" data-i18n="popular">Popular</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-content p-4 d-flex flex-column h-100">
                               <div class="small text-secondary mb-2"
                                   data-cat-id="<?= (int) $product['category_id']; ?>"
                                   data-cat-field="name"
                                   data-original="<?= e($product['category_name']); ?>"
                               ><?= e($product['category_name']); ?></div>
                            <h3 class="h5 text-white"
                                data-product-id="<?= (int) $product['id']; ?>"
                                data-product-field="name"
                                data-original="<?= e($product['name']); ?>"
                            ><?= e($product['name']); ?></h3>
                            <p class="text-secondary"
                               data-product-id="<?= (int) $product['id']; ?>"
                               data-product-field="desc"
                               data-original="<?= e($product['short_description']); ?>"
                            ><?= e($product['short_description']); ?></p>
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center gap-3">
                                    <div class="price-display-block">
                                        <div class="price-caption text-secondary mt-2" data-i18n="table_unit_price">Precio unitario</div>
                                        <span
                                            class="price-tag js-product-price"
                                            data-base-price-cop="<?= (int) $basePriceCop; ?>"
                                            data-default-label="<?= e($cleanCopLabel); ?>"
                                        ><?= e($cleanCopLabel); ?></span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 product-buy-controls">
                                    <label class="text-secondary small mb-0" for="qty-<?= (int) $product['id']; ?>" data-i18n="quantity">Cantidad</label>
                                    <div class="qty-stepper">
                                        <button type="button" class="qty-step-btn js-qty-decrease" aria-label="Disminuir cantidad">-</button>
                                        <input
                                            id="qty-<?= (int) $product['id']; ?>"
                                            type="number"
                                            min="1"
                                            value="1"
                                            class="form-control form-control-sm qty-input js-qty-input"
                                        >
                                        <button type="button" class="qty-step-btn js-qty-increase" aria-label="Aumentar cantidad">+</button>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-primary rounded-pill px-3 js-add-to-cart"
                                    data-product-id="<?= (int) $product['id']; ?>"
                                    data-product-name="<?= e($product['name']); ?>"
                                    data-original-product-name="<?= e($product['name']); ?>"
                                    data-category-name="<?= e($product['category_name']); ?>"
                                    data-original-category-name="<?= e($product['category_name']); ?>"
                                    data-base-price-cop="<?= (int) $basePriceCop; ?>"
                                    data-i18n="add_to_cart"
                                >Anadir al carrito</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="cartOverlay" class="cart-overlay"></div>

        <div id="carrito" class="cart-panel cart-drawer glass-panel p-4 p-lg-5">
            <div class="cart-header mb-4">
                <div class="cart-header-top d-flex align-items-start justify-content-between gap-3">
                    <h3 class="text-white mb-0" data-i18n="cart_title">Carrito de compras</h3>
                    <p class="text-secondary mb-0 text-md-end" data-i18n="cart_help">Ajusta cantidades y envia todo el pedido en un solo mensaje.</p>
                </div>
                <div class="cart-header-actions d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-outline-light rounded-pill" id="closeCartBtn">Cerrar</button>
                    <button type="button" class="btn btn-outline-light rounded-pill" id="clearCartBtn" data-i18n="clear_cart">Vaciar carrito</button>
                    <button type="button" class="btn btn-whatsapp rounded-pill" id="sendCartWhatsappBtn"><i class="bi bi-whatsapp"></i><span data-i18n="send_cart_whatsapp">Enviar pedido por WhatsApp</span></button>
                </div>
            </div>

            <div class="cart-body">
                <div id="cartEmptyState" class="text-secondary" data-i18n="cart_empty">Tu carrito esta vacio. Agrega productos para generar tu pedido.</div>

                <div class="table-responsive" id="cartTableWrap">
                    <table class="table align-middle mb-2 cart-table">
                        <thead>
                            <tr>
                                <th class="text-secondary" data-i18n="table_product">Producto</th>
                                <th class="text-secondary" data-i18n="table_unit_price">Precio unitario</th>
                                <th class="text-secondary" data-i18n="table_quantity">Cantidad</th>
                                <th class="text-secondary text-end" data-i18n="table_subtotal">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cartItemsBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="cart-footer d-flex justify-content-end">
                <div class="cart-total-wrap">
                    <div class="small text-secondary" data-i18n="order_total">Total del pedido</div>
                    <div id="cartTotalLabel" class="h4 text-white mb-0">$0</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="categorias" class="section-padding bg-soft-dark categories-secondary-section">
    <div class="container">
        <div class="section-title text-center mb-5">
            <span class="eyebrow" data-i18n="explore">Explora</span>
            <h2 class="text-white" data-i18n="categories">Categorias</h2>
            <p class="text-secondary" data-i18n="categories_help">Organiza el catalogo en bloques simples para que el cliente entienda rapido que puede comprar.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-6 col-xl-3">
                    <a href="?categoria=<?= (int) $category['id']; ?>#productos" class="category-card text-decoration-none d-block h-100">
                        <div class="category-icon"><?= e($category['short_label'] ?: strtoupper(mb_substr($category['name'], 0, 2))); ?></div>
                        <h3 class="h5 text-white"
                            data-cat-id="<?= (int) $category['id']; ?>"
                            data-cat-field="name"
                            data-original="<?= e($category['name']); ?>"
                        ><?= e($category['name']); ?></h3>
                        <p class="text-secondary mb-0"
                           data-cat-id="<?= (int) $category['id']; ?>"
                           data-cat-field="desc"
                           data-original="<?= e($category['description']); ?>"
                        ><?= e($category['description']); ?></p>
                    </a>
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
                    <span class="eyebrow" data-i18n="benefits">Beneficios</span>
                    <h2 class="text-white" data-i18n="benefits_title">Beneficios para vender cuentas streaming</h2>
                    <ul class="benefits-list">
                        <li data-i18n="benefit_1">Publica cuentas de Netflix, Disney+, Max y mas con fichas claras por plan.</li>
                        <li data-i18n="benefit_2">Muestra precios por pais y moneda para vender a clientes locales e internacionales.</li>
                        <li data-i18n="benefit_3">Recibe pedidos completos por WhatsApp con cantidades y productos listos para confirmar.</li>
                        <li data-i18n="benefit_4">Actualiza tasas, numeros de WhatsApp y catalogo sin tocar codigo.</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6" id="contacto">
                <div class="contact-card p-4 p-lg-5">
                    <span class="eyebrow" data-i18n="contact">Contacto</span>
                    <h2 class="text-white" data-i18n="contact_title">Atencion inmediata</h2>
                    <p class="text-secondary" data-i18n="contact_help">Conecta al cliente con un mensaje precargado para acelerar la compra.</p>
                    <a
                        class="btn btn-whatsapp rounded-pill px-4 js-country-whatsapp-link"
                        target="_blank"
                        href="#"
                        data-whatsapp-message="<?= e($settings['whatsapp_message_general'] ?? 'Hola, quiero mas informacion sobre el catalogo digital.'); ?>"
                    ><i class="bi bi-whatsapp"></i><span data-i18n="contact_cta">Hablar por WhatsApp</span></a>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <p class="mb-0 text-secondary">© <?= date('Y'); ?> <?= e($settings['site_name'] ?? 'Tienda digital'); ?>.</p>
        <p class="mb-0 text-secondary" data-i18n="footer_dev">Desarrollado con Bootstrap 5, PHP y MySQL.</p>
    </div>
</footer>

<?php require __DIR__ . '/includes/footer.php'; ?>
