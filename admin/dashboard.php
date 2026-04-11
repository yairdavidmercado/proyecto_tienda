<?php
require __DIR__ . '/partials.php';

$stats = [
    'products' => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'categories' => (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'featured' => (int) $pdo->query('SELECT COUNT(*) FROM products WHERE featured = 1')->fetchColumn(),
];

admin_header('Resumen general');
?>
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="glass-panel p-4 h-100">
            <div class="text-secondary small">Productos</div>
            <div class="display-6 fw-bold text-white"><?= $stats['products']; ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-panel p-4 h-100">
            <div class="text-secondary small">Categorías</div>
            <div class="display-6 fw-bold text-white"><?= $stats['categories']; ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-panel p-4 h-100">
            <div class="text-secondary small">Productos destacados</div>
            <div class="display-6 fw-bold text-white"><?= $stats['featured']; ?></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="glass-panel p-4 h-100">
            <h2 class="h5 text-white">Qué puedes administrar</h2>
            <ul class="benefits-list mt-3">
                <li>Cambiar textos principales del home.</li>
                <li>Crear categorías del catálogo.</li>
                <li>Agregar productos, imágenes y precios.</li>
                <li>Configurar el número de WhatsApp.</li>
            </ul>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="glass-panel p-4 h-100">
            <h2 class="h5 text-white">Atajos</h2>
            <div class="d-grid gap-2 mt-3">
                <a href="products.php" class="btn btn-light rounded-pill">Gestionar productos</a>
                <a href="categories.php" class="btn btn-outline-light rounded-pill">Gestionar categorías</a>
                <a href="settings.php" class="btn btn-outline-light rounded-pill">Editar textos</a>
            </div>
        </div>
    </div>
</div>
<?php admin_footer(); ?>
