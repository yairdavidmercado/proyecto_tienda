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

    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');

    // Si la petición viene desde /payments/create_checkout.php,
    // subimos un nivel porque payment_result.php está en la raíz.
    if (substr($base, -9) === '/payments') {
        $base = dirname($base);
    }

    if ($base === '.' || $base === '/' || $base === '\\') {
        $base = '';
    }

    return $scheme . '://' . $host . $base . '/' . ltrim($path, '/');
}

function product_price_number($priceLabel): float
{
    $value = trim((string) $priceLabel);
    $value = preg_replace('/[^\d,\.]/', '', $value);

    if ($value === '') {
        return 0;
    }

    if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');

        if ($lastComma > $lastDot) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }
    } elseif (strpos($value, ',') !== false) {
        $value = str_replace(',', '.', $value);
    }

    return (float) $value;
}

function product_price_to_cop($priceLabel, string $priceBaseCurrency = 'COP', string $countryCode = 'CO', array $settings = []): int
{
    if ($priceBaseCurrency === 'LOCAL') {
        $amount = product_price_number($priceLabel);

        $rates = [
            'ES' => isset($settings['rate_cop_to_eur']) ? (float) $settings['rate_cop_to_eur'] : 0.00023,
            'US' => isset($settings['rate_cop_to_usd']) ? (float) $settings['rate_cop_to_usd'] : 0.00026,
            'MX' => isset($settings['rate_cop_to_mxn']) ? (float) $settings['rate_cop_to_mxn'] : 0.0044,
            'CO' => 1,
        ];

        $rate = $rates[$countryCode] ?? 1;

        if ($rate <= 0) {
            return 0;
        }

        return (int) round($amount / $rate);
    }

    return (int) preg_replace('/\D+/', '', (string) $priceLabel);
}

function setting_is_enabled(array $settings, string $key): bool
{
    return isset($settings[$key]) && (string) $settings[$key] === '1';
}

function wompi_get_transaction(array $settings, string $transactionId): ?array
{
    $transactionId = trim($transactionId);

    if ($transactionId === '') {
        return null;
    }

    $publicKey = trim((string) ($settings['wompi_public_key'] ?? ''));

    if ($publicKey === '') {
        return null;
    }

    $environment = (string) ($settings['wompi_environment'] ?? 'sandbox');

    $baseUrl = ($environment === 'production')
        ? 'https://production.wompi.co'
        : 'https://sandbox.wompi.co';

    $url = $baseUrl . '/v1/transactions/' . rawurlencode($transactionId);

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $publicKey,
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return null;
    }

    $json = json_decode($response, true);

    if (!is_array($json)) {
        return null;
    }

    return $json['data'] ?? null;
}

function notify_order_paid(PDO $pdo, array $order, array $settings): void
{
    $notificationEmail = trim((string) ($settings['notification_email'] ?? ''));

    if ($notificationEmail === '' || !filter_var($notificationEmail, FILTER_VALIDATE_EMAIL)) {
        file_put_contents(
            __DIR__ . '/../payments/mail_debug.log',
            date('Y-m-d H:i:s') . " - ERROR: notification_email vacío o inválido\n",
            FILE_APPEND
        );
        return;
    }

    $itemsStmt = $pdo->prepare("
        SELECT *
        FROM order_items
        WHERE order_id = ?
        ORDER BY id ASC
    ");
    $itemsStmt->execute([(int) $order['id']]);
    $items = $itemsStmt->fetchAll();

    $productsText = '';

    foreach ($items as $item) {
        $productsText .= '- ' . (int) $item['quantity'] . ' x ' . $item['product_name'];
        $productsText .= ' | Categoría: ' . ($item['category_name'] ?: 'Sin categoría');
        $productsText .= ' | Subtotal: $' . number_format((int) $item['subtotal_cop'], 0, ',', '.') . " COP\n";
    }

    if ($productsText === '') {
        $productsText = "Sin productos registrados.\n";
    }

    $subject = 'Pago aprobado - Pedido ' . $order['reference'];

    $message = "Se ha aprobado un pago en Wompi.\n\n";
    $message .= "Referencia: " . $order['reference'] . "\n";
    $message .= "Estado: " . $order['status'] . "\n";
    $message .= "Transacción Wompi: " . ($order['wompi_transaction_id'] ?? '-') . "\n";
    $message .= "Método de pago: " . ($order['wompi_payment_method'] ?? '-') . "\n";
    $message .= "País: " . $order['country_code'] . "\n";
    $message .= "Total: $" . number_format((int) $order['amount_cop'], 0, ',', '.') . " COP\n\n";

    $message .= "Cliente:\n";
    $message .= "Nombre: " . $order['customer_name'] . "\n";
    $message .= "Correo: " . $order['customer_email'] . "\n";
    $message .= "Teléfono: " . $order['customer_phone'] . "\n\n";

    $message .= "Productos comprados:\n";
    $message .= $productsText;

    $headers = [];
    $headers[] = 'From: Pixel Play <soporte@pixelplays.shop>';
    $headers[] = 'Reply-To: ' . $order['customer_email'];
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';

    $sent = mail($notificationEmail, $subject, $message, implode("\r\n", $headers));

    file_put_contents(
        __DIR__ . '/../payments/mail_debug.log',
        date('Y-m-d H:i:s') .
        " - Enviando a: " . $notificationEmail .
        " - Resultado: " . ($sent ? 'OK' : 'ERROR') .
        " - Referencia: " . $order['reference'] .
        "\n",
        FILE_APPEND
    );

    if ($sent) {
        $updateStmt = $pdo->prepare("
            UPDATE orders
            SET payment_notified_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([(int) $order['id']]);
    }
}