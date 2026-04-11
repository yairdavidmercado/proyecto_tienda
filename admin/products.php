<?php
require __DIR__ . '/partials.php';

$supportedCountries = get_supported_countries();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    if ($action === 'create') {
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $countryCode = normalize_country_code($_POST['country_code'] ?? 'CO');
        $categoryStmt = $pdo->prepare('SELECT id, country_code FROM categories WHERE id = ?');
        $categoryStmt->execute([$categoryId]);
        $category = $categoryStmt->fetch();

        if (!$category) {
            flash_message('danger', 'La categoría seleccionada no existe.');
        } elseif (($category['country_code'] ?? '') !== $countryCode) {
            flash_message('danger', 'La categoría no pertenece al país seleccionado.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (category_id, country_code, name, short_description, price_label, image_url, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $categoryId,
                $countryCode,
                trim($_POST['name'] ?? ''),
                trim($_POST['short_description'] ?? ''),
                trim($_POST['price_label'] ?? ''),
                trim($_POST['image_url'] ?? ''),
                isset($_POST['featured']) ? 1 : 0,
                isset($_POST['status']) ? 1 : 0,
            ]);
            flash_message('success', 'Producto creado correctamente.');
        }
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
        flash_message('warning', 'Producto eliminado.');
    }

    header('Location: products.php');
    exit;
}

$categories = $pdo->query('SELECT id, name, country_code FROM categories WHERE status = 1 ORDER BY country_code ASC, sort_order ASC, name ASC')->fetchAll();
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.country_code ASC, p.id DESC')->fetchAll();
admin_header('Productos');
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="glass-panel p-4">
            <h2 class="h5 text-white mb-3">Nuevo producto</h2>
            <form method="post" class="d-grid gap-3">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="form-label text-white">País</label>
                    <select name="country_code" class="form-select" required>
                        <?php foreach ($supportedCountries as $countryCode => $countryName): ?>
                            <option value="<?= e($countryCode); ?>"><?= e($countryCode . ' - ' . $countryName); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label text-white">Categoría</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id']; ?>"><?= e('[' . $category['country_code'] . '] ' . $category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label text-white">Nombre</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div>
                    <label class="form-label text-white">Descripción corta</label>
                    <textarea name="short_description" class="form-control" rows="4"></textarea>
                </div>
                <div>
                    <label class="form-label text-white">Precio visible</label>
                    <input type="text" name="price_label" class="form-control" placeholder="$25.000 / mes" required>
                </div>
                <div>
                    <label class="form-label text-white">URL de imagen</label>
                    <input type="url" name="image_url" class="form-control" placeholder="https://...">
                </div>
                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured">
                        <label class="form-check-label text-white" for="featured">Destacado</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" id="statusProduct" checked>
                        <label class="form-check-label text-white" for="statusProduct">Activo</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-light rounded-pill">Guardar producto</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="table-dark-glass p-3">
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0 text-white">
                    <thead>
                        <tr class="text-secondary">
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>País</th>
                            <th>Precio</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($product['name']); ?></div>
                                <div class="small text-secondary"><?= e($product['short_description']); ?></div>
                            </td>
                            <td><?= e($product['category_name']); ?></td>
                            <td><?= e($product['country_code']); ?></td>
                            <td><?= e($product['price_label']); ?></td>
                            <td class="text-end">
                                <form method="post" onsubmit="return confirm('¿Eliminar este producto?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $product['id']; ?>">
                                    <button class="btn btn-sm btn-outline-light rounded-pill">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php admin_footer(); ?>
