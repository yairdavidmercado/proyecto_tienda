<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';

http_response_code(200);

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

try {
    $rawBody = file_get_contents('php://input');
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
} catch (Throwable $e) {
    // Respondemos 200 para evitar reintentos infinitos si el error es interno.
    exit;
}