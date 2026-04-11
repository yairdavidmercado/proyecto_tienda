<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
require_login();
$settings = get_settings($pdo);
$flash = flash_message();
function admin_header(string $title): void {
    global $flash;
    ?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body class="admin-site">
    <div class="admin-shell d-lg-flex">
        <aside class="sidebar p-4">
            <a href="dashboard.php" class="navbar-brand fw-bold text-white text-decoration-none d-inline-flex align-items-center gap-2 mb-4">
                <img src="../assets/img/logo.png" alt="Admin" class="brand-logo">
            </a>
            <nav class="nav flex-column gap-2">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">Resumen</a>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>" href="products.php">Productos</a>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>" href="categories.php">Categorías</a>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>" href="settings.php">Ajustes</a>
                <a class="nav-link" href="logout.php">Cerrar sesión</a>
            </nav>
        </aside>
        <main class="content-wrap p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-white mb-1"><?= e($title); ?></h1>
                    <p class="text-secondary mb-0">Hola, <?= e($_SESSION['admin_name'] ?? 'Administrador'); ?>.</p>
                </div>
                <a href="../index.php" target="_blank" class="btn btn-outline-light rounded-pill">Ver sitio</a>
            </div>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']); ?> show rounded-4" data-auto-close="true"><?= e($flash['message']); ?></div>
            <?php endif; ?>
    <?php
}

function admin_footer(): void {
    ?>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    </body>
    </html>
    <?php
}
