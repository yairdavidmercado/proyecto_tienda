<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';

http_response_code(200);

file_put_contents(
    __DIR__ . '/wompi_webhook_debug.log',
    date('Y-m-d H:i:s') . " - WEBHOOK RECIBIDO\n" . file_get_contents('php://input') . "\n\n",
    FILE_APPEND
);

function wompi_get_nested_value(array $array, string $path)
{
    $parts = explode('.', $path);
    $value = $array;

    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return '';
        }

        $value = $value[$part];
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_array($value)) {
        return json_encode($value);
    }

    return (string) $value;
}

function notify_order_paid(PDO $pdo, array $order, array $settings): void
{
    $notificationEmail = trim((string) ($settings['notification_email'] ?? ''));

    if ($notificationEmail === '' || !filter_var($notificationEmail, FILTER_VALIDATE_EMAIL)) {
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
        __DIR__ . '/mail_debug.log',
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

try {
    $rawBody = file_get_contents('php://input');
    file_put_contents(
        __DIR__ . '/wompi_webhook_debug.log',
        date('Y-m-d H:i:s') . " - BODY\n" . $rawBody . "\n\n",
        FILE_APPEND
    );
    $event = json_decode($rawBody, true);

    if (!is_array($event)) {
        exit;
    }

    $settings = get_settings($pdo);
    $eventsKey = trim((string) ($settings['wompi_events_key'] ?? ''));

    if ($eventsKey === '') {
        exit;
    }

    $signature = $event['signature'] ?? [];
    $properties = $signature['properties'] ?? [];
    $timestamp = $signature['timestamp'] ?? '';
    $receivedChecksum = $signature['checksum'] ?? '';

    if (!$receivedChecksum && !empty($_SERVER['HTTP_X_EVENT_CHECKSUM'])) {
        $receivedChecksum = $_SERVER['HTTP_X_EVENT_CHECKSUM'];
    }

    if (!is_array($properties) || $timestamp === '' || $receivedChecksum === '') {
        exit;
    }

    $concatenated = '';

    foreach ($properties as $property) {
        $concatenated .= wompi_get_nested_value($event['data'] ?? [], (string) $property);
    }

    $concatenated .= (string) $timestamp;
    $concatenated .= $eventsKey;

    $calculatedChecksum = hash('sha256', $concatenated);

    if (!hash_equals(strtolower($receivedChecksum), strtolower($calculatedChecksum))) {
        exit;
    }

    if (($event['event'] ?? '') !== 'transaction.updated') {
        exit;
    }

    $transaction = $event['data']['transaction'] ?? [];
    if (!is_array($transaction)) {
        exit;
    }

    $reference = (string) ($transaction['reference'] ?? '');
    $status = (string) ($transaction['status'] ?? '');
    $transactionId = (string) ($transaction['id'] ?? '');
    $paymentMethod = (string) ($transaction['payment_method_type'] ?? '');
    $amountInCents = (int) ($transaction['amount_in_cents'] ?? 0);

    if ($reference === '' || $status === '') {
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE orders
        SET
            status = ?,
            wompi_transaction_id = ?,
            wompi_payment_method = ?,
            raw_event = ?,
            updated_at = NOW()
        WHERE reference = ?
          AND amount_in_cents = ?
    ");

    $stmt->execute([
        $status,
        $transactionId,
        $paymentMethod,
        $rawBody,
        $reference,
        $amountInCents,
    ]);

    $orderStmt = $pdo->prepare("
        SELECT *
        FROM orders
        WHERE reference = ?
        LIMIT 1
    ");
    $orderStmt->execute([$reference]);
    $order = $orderStmt->fetch();

    if (
        $order
        && strtoupper((string) $order['status']) === 'APPROVED'
        && empty($order['payment_notified_at'])
    ) {
        notify_order_paid($pdo, $order, $settings);
    }
} catch (Throwable $e) {
    // Respondemos 200 para evitar reintentos infinitos si el error es interno.
    exit;
}