<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/functions.php';

$settings = get_settings($pdo);

$reference = trim((string) ($_GET['reference'] ?? ''));
$transactionId = trim((string) ($_GET['id'] ?? ''));

$order = null;

if ($reference !== '') {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE reference = ? LIMIT 1');
    $stmt->execute([$reference]);
    $order = $stmt->fetch();
}

/*
 * Si Wompi retorna el id de transacción en la URL,
 * consultamos el estado real en Wompi y actualizamos la orden.
 */
if ($order && $transactionId !== '') {
    $transaction = wompi_get_transaction($settings, $transactionId);

    if (is_array($transaction)) {
        $wompiReference = (string) ($transaction['reference'] ?? '');
        $wompiStatus = (string) ($transaction['status'] ?? '');
        $wompiTransactionId = (string) ($transaction['id'] ?? '');
        $wompiPaymentMethod = (string) ($transaction['payment_method_type'] ?? '');
        $wompiAmountInCents = (int) ($transaction['amount_in_cents'] ?? 0);

        if (
            $wompiReference === $order['reference']
            && $wompiAmountInCents === (int) $order['amount_in_cents']
            && $wompiStatus !== ''
        ) {
            $updateStmt = $pdo->prepare("
                UPDATE orders
                SET
                    status = ?,
                    wompi_transaction_id = ?,
                    wompi_payment_method = ?,
                    raw_event = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");

            $updateStmt->execute([
                $wompiStatus,
                $wompiTransactionId,
                $wompiPaymentMethod,
                json_encode(['source' => 'payment_result', 'transaction' => $transaction]),
                (int) $order['id'],
            ]);

            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
            $stmt->execute([(int) $order['id']]);
            $order = $stmt->fetch();
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="glass-panel p-4 p-lg-5 text-center">
        <h1 class="text-white mb-3">Resultado del pago</h1>

        <?php if (!$order): ?>
            <p class="text-secondary mb-0">
                No encontramos el pedido. Si ya pagaste, comunícate por WhatsApp con el comprobante.
            </p>
        <?php else: ?>
            <?php if ($order['status'] === 'APPROVED'): ?>
                <h2 class="text-success">Pago aprobado</h2>
                <p class="text-secondary">
                    Tu pago fue recibido correctamente. Nos comunicaremos contigo para coordinar la entrega del producto.
                </p>
            <?php elseif ($order['status'] === 'PENDING'): ?>
                <h2 class="text-warning">Pago en validación</h2>
                <p class="text-secondary">
                    Recibimos tu intento de pago. Estamos esperando la confirmación de Wompi.
                </p>
            <?php else: ?>
                <h2 class="text-danger">Pago no aprobado</h2>
                <p class="text-secondary">
                    El estado actual del pago es: <?= e($order['status']); ?>.
                </p>
            <?php endif; ?>

            <div class="mt-4 text-secondary">
                <div>Referencia: <strong class="text-white"><?= e($order['reference']); ?></strong></div>
                <?php if ($transactionId): ?>
                    <div>Transacción Wompi: <strong class="text-white"><?= e($transactionId); ?></strong></div>
                <?php endif; ?>
            </div>

            <a href="index.php" class="btn btn-light rounded-pill px-4 mt-4">Volver a la tienda</a>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>