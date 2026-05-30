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