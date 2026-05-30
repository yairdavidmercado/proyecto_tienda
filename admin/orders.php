<?php
require __DIR__ . '/partials.php';

$ordersStmt = $pdo->query("
    SELECT
        o.*,
        COUNT(oi.id) AS total_items
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id
    ORDER BY o.id DESC
");

$orders = $ordersStmt->fetchAll();

$itemsStmt = $pdo->query("
    SELECT *
    FROM order_items
    ORDER BY order_id DESC, id ASC
");

$itemsByOrder = [];

foreach ($itemsStmt->fetchAll() as $item) {
    $orderId = (int) $item['order_id'];

    if (!isset($itemsByOrder[$orderId])) {
        $itemsByOrder[$orderId] = [];
    }

    $itemsByOrder[$orderId][] = $item;
}

admin_header('Pedidos Wompi');
?>

<div class="glass-panel p-4 overflow-hidden">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h5 text-white mb-1">Pedidos recibidos</h2>
            <p class="text-secondary mb-0">
                Aquí puedes ver qué productos compró cada cliente y el estado de pago en Wompi.
            </p>
        </div>
    </div>

    <?php if (!$orders): ?>
        <div class="text-secondary">Aún no hay pedidos registrados.</div>
    <?php else: ?>
        <div class="table-responsive admin-table-scroll">
            <table class="table table-borderless align-middle mb-0 text-white admin-data-table">
                <thead>
                    <tr class="text-secondary">
                        <th>Referencia</th>
                        <th>Cliente</th>
                        <th>País</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Productos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                            $orderId = (int) $order['id'];
                            $items = $itemsByOrder[$orderId] ?? [];
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($order['reference']); ?></div>
                                <?php if (!empty($order['wompi_transaction_id'])): ?>
                                    <div class="small text-secondary">
                                        Tx: <?= e($order['wompi_transaction_id']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div><?= e($order['customer_name']); ?></div>
                                <div class="small text-secondary"><?= e($order['customer_email']); ?></div>
                                <div class="small text-secondary"><?= e($order['customer_phone']); ?></div>
                            </td>

                            <td><?= e($order['country_code']); ?></td>

                            <td>
                                $<?= number_format((int) $order['amount_cop'], 0, ',', '.'); ?> COP
                            </td>

                            <td>
                                <?php
                                    $status = strtoupper((string) $order['status']);
                                    $badgeClass = 'text-bg-secondary';

                                    if ($status === 'APPROVED') {
                                        $badgeClass = 'text-bg-success';
                                    } elseif ($status === 'PENDING') {
                                        $badgeClass = 'text-bg-warning';
                                    } elseif (in_array($status, ['DECLINED', 'ERROR', 'VOIDED'], true)) {
                                        $badgeClass = 'text-bg-danger';
                                    }
                                ?>
                                <span class="badge <?= $badgeClass; ?>">
                                    <?= e($status); ?>
                                </span>

                                <?php if (!empty($order['wompi_payment_method'])): ?>
                                    <div class="small text-secondary mt-1">
                                        <?= e($order['wompi_payment_method']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= e($order['created_at']); ?>
                            </td>

                            <td style="min-width: 280px;">
                                <?php if (!$items): ?>
                                    <span class="text-secondary">Sin productos registrados</span>
                                <?php else: ?>
                                    <div class="d-grid gap-2">
                                        <?php foreach ($items as $item): ?>
                                            <div class="p-2 rounded-3 border border-secondary-subtle">
                                                <div class="fw-semibold">
                                                    <?= (int) $item['quantity']; ?> x <?= e($item['product_name']); ?>
                                                </div>

                                                <div class="small text-secondary">
                                                    <?= e($item['category_name'] ?? 'Sin categoría'); ?>
                                                </div>

                                                <div class="small text-secondary">
                                                    Unitario:
                                                    $<?= number_format((int) $item['unit_price_cop'], 0, ',', '.'); ?> COP
                                                    · Subtotal:
                                                    $<?= number_format((int) $item['subtotal_cop'], 0, ',', '.'); ?> COP
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php admin_footer(); ?>