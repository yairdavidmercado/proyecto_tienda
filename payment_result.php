<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/functions.php';

$settings = get_settings($pdo);

$reference = trim((string) ($_GET['reference'] ?? ''));
$transactionId = trim((string) ($_GET['id'] ?? ''));

$order = null;

$orderItems = [];

if ($reference !== '') {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE reference = ? LIMIT 1');
    $stmt->execute([$reference]);
    $order = $stmt->fetch();
}

if ($order) {
    $itemsStmt = $pdo->prepare('
        SELECT *
        FROM order_items
        WHERE order_id = ?
        ORDER BY id ASC
    ');
    $itemsStmt->execute([(int) $order['id']]);
    $orderItems = $itemsStmt->fetchAll();
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

            if (
                $order
                && strtoupper((string) $order['status']) === 'APPROVED'
                && empty($order['payment_notified_at'])
            ) {
                notify_order_paid($pdo, $order, $settings);
            }

            $itemsStmt = $pdo->prepare('
                SELECT *
                FROM order_items
                WHERE order_id = ?
                ORDER BY id ASC
            ');
            $itemsStmt->execute([(int) $order['id']]);
            $orderItems = $itemsStmt->fetchAll();
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

            <?php if (!empty($orderItems)): ?>
                <div class="mt-4 text-start mx-auto" style="max-width: 620px;">
                    <h3 class="h5 text-white mb-3">Productos comprados</h3>

                    <div class="d-grid gap-2">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="p-3 rounded-4 border border-secondary-subtle">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold text-white">
                                            <?= (int) $item['quantity']; ?> x <?= e($item['product_name']); ?>
                                        </div>

                                        <div class="small text-secondary">
                                            <?= e($item['category_name'] ?? 'Sin categoría'); ?>
                                        </div>
                                    </div>

                                    <div class="text-white text-end">
                                        $<?= number_format((int) $item['subtotal_cop'], 0, ',', '.'); ?> COP
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <a href="index.php" class="btn btn-light rounded-pill px-4 mt-4">Volver a la tienda</a>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>