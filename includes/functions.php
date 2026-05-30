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

function get_supported_countries(): array
{
    return [
        'CO' => 'Colombia',
        'ES' => 'España',
        'MX' => 'Mexico',
        'US' => 'Estados Unidos',
    ];
}

function normalize_country_code(?string $countryCode): string
{
    $countryCode = strtoupper(trim((string) $countryCode));
    $supportedCountries = get_supported_countries();
    return array_key_exists($countryCode, $supportedCountries) ? $countryCode : 'CO';
}

function normalize_country_codes(array $countryCodes): array
{
    $normalized = [];
    foreach ($countryCodes as $countryCode) {
        $code = normalize_country_code((string) $countryCode);
        $normalized[$code] = $code;
    }
    return array_values($normalized);
}

function get_categories(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC, name ASC');
    return $stmt->fetchAll();
}

function get_products(PDO $pdo, ?int $categoryId = null): array
{
    if ($categoryId) {
        $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name, c.country_code AS category_country_code FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE p.status = 1 AND c.status = 1 AND p.category_id = ? ORDER BY p.sort_order ASC, p.featured DESC, p.id DESC');
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    $stmt = $pdo->query('SELECT p.*, c.name AS category_name, c.country_code AS category_country_code FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE p.status = 1 AND c.status = 1 ORDER BY p.country_code ASC, p.sort_order ASC, p.featured DESC, p.id DESC');
    return $stmt->fetchAll();
}

function whatsapp_link(string $phone, string $message): string
{
    $cleanPhone = preg_replace('/\D+/', '', $phone);
    return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($message);
}

function absolute_url(string $path = ''): string
{
    $https = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );

    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));

    /*
     * Si la petición viene desde /payments/create_checkout.php,
     * quitamos /payments para que el retorno quede en la raíz.
     */
    $scriptDir = preg_replace('#/payments$#', '', $scriptDir);

    $base = trim($scriptDir, '/');

    $url = $scheme . '://' . $host;

    if ($base !== '') {
        $url .= '/' . $base;
    }

    return rtrim($url, '/') . '/' . ltrim($path, '/');
}

function product_price_to_cop($priceLabel): int
{
    return (int) preg_replace('/\D+/', '', (string) $priceLabel);
}

function setting_is_enabled(array $settings, string $key): bool
{
    return isset($settings[$key]) && (string) $settings[$key] === '1';
}