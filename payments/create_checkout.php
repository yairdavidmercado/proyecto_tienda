<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo no permitido.');
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        throw new Exception('Solicitud invalida.');
    }

    $settings = get_settings($pdo);

    if (!setting_is_enabled($settings, 'wompi_enabled')) {
        throw new Exception('Wompi no esta habilitado.');
    }

    $publicKey = trim((string) ($settings['wompi_public_key'] ?? ''));
    $integrityKey = trim((string) ($settings['wompi_integrity_key'] ?? ''));

    if ($publicKey === '' || $integrityKey === '') {
        throw new Exception('Faltan llaves de Wompi en ajustes.');
    }

    $countryCode = normalize_country_code($payload['country'] ?? 'CO');

    $customer = $payload['customer'] ?? [];
    $customerName = trim((string) ($customer['name'] ?? ''));
    $customerEmail = trim((string) ($customer['email'] ?? ''));
    $customerPhone = trim((string) ($customer['phone'] ?? ''));

    if ($customerName === '') {
        throw new Exception('Ingresa el nombre del cliente.');
    }

    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Ingresa un correo valido.');
    }

    if ($customerPhone === '') {
        throw new Exception('Ingresa el WhatsApp o telefono del cliente.');
    }

    $items = $payload['items'] ?? [];
    if (!is_array($items) || count($items) === 0) {
        throw new Exception('El carrito esta vacio.');
    }

    $pdo->beginTransaction();

    $reference = 'PX' . date('YmdHis') . strtoupper(bin2hex(random_bytes(4)));

    $orderStmt = $pdo->prepare("
        INSERT INTO orders (
            reference,
            country_code,
            customer_name,
            customer_email,
            customer_phone,
            amount_cop,
            amount_in_cents,
            currency,
            status
        ) VALUES (?, ?, ?, ?, ?, 0, 0, 'COP', 'PENDING')
    ");

    $orderStmt->execute([
        $reference,
        $countryCode,
        $customerName,
        $customerEmail,
        $customerPhone,
    ]);

    $orderId = (int) $pdo->lastInsertId();

    $productStmt = $pdo->prepare("
        SELECT
            p.id,
            p.name,
            p.price_label,
            p.country_code,
            p.country_codes,
            p.status,
            c.name AS category_name
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
        WHERE p.id = ?
          AND p.status = 1
          AND c.status = 1
        LIMIT 1
    ");

    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id,
            product_id,
            product_name,
            category_name,
            unit_price_cop,
            quantity,
            subtotal_cop
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $amountCop = 0;

    foreach ($items as $item) {
        $productId = (int) ($item['productId'] ?? 0);
        $qty = max(1, (int) ($item['qty'] ?? 1));

        if ($productId <= 0) {
            continue;
        }

        $productStmt->execute([$productId]);
        $product = $productStmt->fetch();

        if (!$product) {
            throw new Exception('Uno de los productos ya no esta disponible.');
        }

        $productCountryCodes = normalize_country_codes(
            explode(',', (string) ($product['country_codes'] ?: $product['country_code']))
        );

        if (!in_array($countryCode, $productCountryCodes, true)) {
            throw new Exception('Uno de los productos no esta disponible para el pais seleccionado.');
        }

        $unitPriceCop = product_price_to_cop($product['price_label']);
        if ($unitPriceCop <= 0) {
            throw new Exception('Uno de los productos no tiene precio valido.');
        }

        $subtotalCop = $unitPriceCop * $qty;
        $amountCop += $subtotalCop;

        $itemStmt->execute([
            $orderId,
            (int) $product['id'],
            (string) $product['name'],
            (string) $product['category_name'],
            $unitPriceCop,
            $qty,
            $subtotalCop,
        ]);
    }

    if ($amountCop <= 0) {
        throw new Exception('No se pudo calcular el total del pedido.');
    }

    $amountInCents = $amountCop * 100;
    $currency = 'COP';

    $updateOrderStmt = $pdo->prepare("
        UPDATE orders
        SET amount_cop = ?, amount_in_cents = ?
        WHERE id = ?
    ");
    $updateOrderStmt->execute([$amountCop, $amountInCents, $orderId]);

    $pdo->commit();

    /*
     * Wompi exige firmar:
     * reference + amount_in_cents + currency + integrity_key
     */
    $signature = hash('sha256', $reference . $amountInCents . $currency . $integrityKey);

    $redirectUrl = absolute_url('../payment_result.php?reference=' . urlencode($reference));

    $query = http_build_query([
        'public-key' => $publicKey,
        'currency' => $currency,
        'amount-in-cents' => $amountInCents,
        'reference' => $reference,
        'signature:integrity' => $signature,
        'redirect-url' => $redirectUrl,
        'customer-data:email' => $customerEmail,
        'customer-data:full-name' => $customerName,
        'customer-data:phone-number' => preg_replace('/\D+/', '', $customerPhone),
    ]);

    echo json_encode([
        'ok' => true,
        'reference' => $reference,
        'checkout_url' => 'https://checkout.wompi.co/p/?' . $query,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);

    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage(),
    ]);
}