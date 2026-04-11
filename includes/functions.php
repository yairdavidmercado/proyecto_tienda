<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function site_url(string $path = ''): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    if ($base === '.' || $base === '/') {
        $base = '';
    }
    return $base . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function flash_message(?string $type = null, ?string $message = null): ?array
{
    if ($type !== null && $message !== null) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        return null;
    }

    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    return null;
}

function get_settings(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
    $settings = [];
    foreach ($stmt->fetchAll() as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function get_categories(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC, name ASC');
    return $stmt->fetchAll();
}

function get_products(PDO $pdo, ?int $categoryId = null): array
{
    if ($categoryId) {
        $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE p.status = 1 AND c.status = 1 AND p.category_id = ? ORDER BY p.featured DESC, p.id DESC');
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    $stmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE p.status = 1 AND c.status = 1 ORDER BY p.featured DESC, p.id DESC');
    return $stmt->fetchAll();
}

function whatsapp_link(string $phone, string $message): string
{
    $cleanPhone = preg_replace('/\D+/', '', $phone);
    return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($message);
}
