<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/functions.php';

$settings = get_settings($pdo);
$categories = get_categories($pdo);
$supportedCountries = get_supported_countries();
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
$defaultWhatsappMessage = trim((string) ($settings['whatsapp_message_general'] ?? 'Hola, quiero mas informacion sobre el catalogo digital.'));
$whatsappMessageCo = trim((string) ($settings['whatsapp_message_co'] ?? $defaultWhatsappMessage));
$whatsappMessageEs = trim((string) ($settings['whatsapp_message_es'] ?? $defaultWhatsappMessage));
$whatsappMessageMx = trim((string) ($settings['whatsapp_message_mx'] ?? $defaultWhatsappMessage));
$whatsappMessageUs = trim((string) ($settings['whatsapp_message_us'] ?? 'Hello, I want more information about the digital catalog.'));
$defaultPaymentMethodsCo = trim((string) <<<TEXT
🔰MEDIOS DE PAGO COLOMBIA 🔰

🟡NEQUI 3197128850
🔴DAVIPLATA 3197128850
⚪MOVII 3197128850
🟢CLARO PAY 3197128850
⚫DALE 3197128850

🟥AHORROS DAVIVIENDA
477500036140

🟨AHORROS BANCOLOMBIA
91286093448

🟩LULOBANK 477165563567

🟪AHORROS NU BANK 30597840

📌LLAVE NEQUI @3197128850
TEXT);
$paymentMethodsByCountry = [
    'CO' => trim((string) ($settings['payment_methods_co'] ?? $defaultPaymentMethodsCo)),
    'ES' => trim((string) ($settings['payment_methods_es'] ?? $defaultPaymentMethodsCo)),
    'MX' => trim((string) ($settings['payment_methods_mx'] ?? $defaultPaymentMethodsCo)),
    'US' => trim((string) ($settings['payment_methods_us'] ?? $defaultPaymentMethodsCo)),
];
$paymentMethodsJson = json_encode($paymentMethodsByCountry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

require __DIR__ . '/includes/header.php';
?>

<nav class="navbar navbar-expand-lg sticky-top navbar-light glass-nav">
    <div class="container py-2">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
            <img src="assets/img/logo.png" alt="<?= e($settings['site_name'] ?? 'Pixel Play'); ?>" class="brand-logo">
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
                    data-whatsapp-message-co="<?= e($whatsappMessageCo); ?>"
                    data-whatsapp-message-es="<?= e($whatsappMessageEs); ?>"
                    data-whatsapp-message-mx="<?= e($whatsappMessageMx); ?>"
                    data-whatsapp-message-us="<?= e($whatsappMessageUs); ?>"
                >
                    <?php foreach ($supportedCountries as $countryCode => $countryName): ?>
                        <option value="<?= e($countryCode); ?>" data-i18n="country_<?= strtolower($countryCode); ?>"><?= e($countryName); ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="visually-hidden" for="langSelector">Idioma</label>
                <select id="langSelector" class="form-select nav-lang-select ms-2" aria-label="Idioma">
                    <option value="es-CO">Español</option>
                    <option value="en-US">English</option>
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

<div id="countryGate" class="country-gate" aria-hidden="true">
    <div class="country-gate-card">
        <h2>Seleccione el pais de origen</h2>
        <p>Necesitamos tu pais para mostrar precios y contenido local.</p>
        <label for="countryGateSelect" class="visually-hidden">Seleccione el pais de origen</label>
        <select id="countryGateSelect" class="form-select">
            <?php foreach ($supportedCountries as $countryCode => $countryName): ?>
                <option value="<?= e($countryCode); ?>"><?= e($countryName); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" id="countryGateConfirm" class="btn btn-light rounded-pill w-100">Ingresar</button>
    </div>
</div>

<div id="heroSlider" class="carousel slide hero-slider" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#heroSlider" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="assets/img/sliders/slider-01.jpg" class="d-block w-100 hero-slide-img" alt="Streaming">
            <div class="carousel-caption hero-slide-caption">
                <span class="slide-eyebrow" data-i18n="hero_badge">Catalogo digital automatizado</span>
                <h2 data-i18n="hero_title">Combos, cuentas y pantallas listas para vender</h2>
                <a href="#productos" class="btn btn-light rounded-pill px-5 mt-2" data-i18n="hero_cta_catalog">Ver catalogo</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="assets/img/sliders/slider-02.jpg" class="d-block w-100 hero-slide-img" alt="Pantallas premium">
            <div class="carousel-caption hero-slide-caption">
                <span class="slide-eyebrow" data-i18n="hero_slide2_eyebrow">Pantallas y cuentas</span>
                <h2 data-i18n="hero_slide2_title">Los mejores precios en streaming premium</h2>
                <a href="#productos" class="btn btn-light rounded-pill px-5 mt-2" data-i18n="hero_cta_catalog">Ver catalogo</a>
            </div>
        </div>
        <div class="carousel-item">
            <img src="assets/img/sliders/slider-03.jpg" class="d-block w-100 hero-slide-img" alt="Digital global">
            <div class="carousel-caption hero-slide-caption">
                <span class="slide-eyebrow" data-i18n="hero_slide3_eyebrow">Colombia &middot; España &middot; M&eacute;xico &middot; USA</span>
                <h2 data-i18n="hero_slide3_title">Disponible para todo el mundo</h2>
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
            <p class="hero-main-subtitle" data-i18n="hero_subtitle">Pantallas streaming con entrega inmediata, precios claros y atencion directa por WhatsApp.</p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="#productos" class="btn btn-primary btn-lg rounded-pill px-5" data-i18n="hero_cta_catalog">Ver catalogo</a>
                <a
                    href="#"
                    target="_blank"
                    class="btn btn-whatsapp btn-lg rounded-pill px-5 js-country-whatsapp-link"
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
                <a href="index.php#productos" class="btn btn-sm rounded-pill <?= $activeCategory ? 'btn-outline-light' : 'btn-light'; ?> js-category-filter-all" data-i18n="all">Todos</a>
                <?php foreach ($categories as $category): ?>
                    <?php $categoryCountryCodes = normalize_country_codes(explode(',', (string) ($category['country_codes'] ?: $category['country_code']))); ?>
                    <a href="?categoria=<?= (int) $category['id']; ?>#productos"
                       class="btn btn-sm rounded-pill js-category-filter <?= $activeCategory === (int) $category['id'] ? 'btn-light' : 'btn-outline-light'; ?>"
                       data-cat-id="<?= (int) $category['id']; ?>"
                       data-country-codes="<?= e(implode(',', $categoryCountryCodes)); ?>"
                       data-cat-field="name"
                       data-original="<?= e($category['name']); ?>">
                        <?= e($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row g-4" id="productsGrid">
            <div class="col-12" id="productsEmptyState"<?= $products ? ' style="display:none;"' : ''; ?>>
                <div class="empty-state glass-panel p-5 text-center">
                    <h3 class="text-white" data-i18n="no_products">No hay productos en esta categoria</h3>
                    <p class="text-secondary mb-0" data-i18n="no_products_help">Agrega productos desde el panel administrativo.</p>
                </div>
            </div>

            <?php foreach ($products as $product):
                $image = $product['image_url'] ?: 'https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?auto=format&fit=crop&w=900&q=80';
                $basePriceCop = (int) preg_replace('/\D+/', '', (string) $product['price_label']);
                $cleanCopLabel = '$' . number_format($basePriceCop, 0, ',', '.');
                $productCountryCodes = normalize_country_codes(explode(',', (string) ($product['country_codes'] ?: $product['country_code'])));
            ?>
                <div class="col-sm-6 col-lg-4 col-xl-3 js-product-item" data-country-codes="<?= e(implode(',', $productCountryCodes)); ?>">
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
                                <th class="text-secondary" data-i18n="table_unit_price">Precio</th>
                                <th class="text-secondary" data-i18n="table_quantity">Cant.</th>
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
                <?php $categoryCountryCodes = normalize_country_codes(explode(',', (string) ($category['country_codes'] ?: $category['country_code']))); ?>
                <div class="col-md-6 col-xl-3 js-category-card" data-country-codes="<?= e(implode(',', $categoryCountryCodes)); ?>">
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
                    <span class="eyebrow" data-i18n="payment_methods">Medios de pago</span>
                    <div id="paymentMethodsContent" class="payment-methods-content"></div>
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
                    ><i class="bi bi-whatsapp"></i><span data-i18n="contact_cta">Hablar por WhatsApp</span></a>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="application/json" id="paymentMethodsByCountryData"><?= $paymentMethodsJson ? str_replace('</', '<\/', $paymentMethodsJson) : '{}'; ?></script>

<footer class="footer py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <p class="mb-0 text-secondary">© <?= date('Y'); ?> <?= e($settings['site_name'] ?? 'Tienda digital'); ?>.</p>
        <p class="mb-0 text-secondary" data-i18n="footer_dev">Desarrollado con Bootstrap 5, PHP y MySQL.</p>
    </div>
</footer>

<?php require __DIR__ . '/includes/footer.php'; ?>
