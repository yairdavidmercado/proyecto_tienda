<?php
require __DIR__ . '/partials.php';

$supportedCountries = get_supported_countries();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    if ($action === 'create') {
        $stmt = $pdo->prepare('INSERT INTO categories (name, short_label, description, country_code, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            trim($_POST['name'] ?? ''),
            trim($_POST['short_label'] ?? ''),
            trim($_POST['description'] ?? ''),
            normalize_country_code($_POST['country_code'] ?? 'CO'),
            (int) ($_POST['sort_order'] ?? 0),
            isset($_POST['status']) ? 1 : 0,
        ]);
        flash_message('success', 'Categoría creada correctamente.');
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
        flash_message('warning', 'Categoría eliminada.');
    }

    header('Location: categories.php');
    exit;
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY sort_order ASC, name ASC')->fetchAll();
admin_header('Categorías');
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="glass-panel p-4">
            <h2 class="h5 text-white mb-3">Nueva categoría</h2>
            <form method="post" class="d-grid gap-3">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="form-label text-white">Nombre</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div>
                    <label class="form-label text-white">Etiqueta corta</label>
                    <input type="text" name="short_label" class="form-control" maxlength="8" placeholder="CP">
                </div>
                <div>
                    <label class="form-label text-white">Descripción</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>
                <div>
                    <label class="form-label text-white">País</label>
                    <select name="country_code" class="form-select" required>
                        <?php foreach ($supportedCountries as $countryCode => $countryName): ?>
                            <option value="<?= e($countryCode); ?>"><?= e($countryCode . ' - ' . $countryName); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label text-white">Orden</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="status" id="status" checked>
                    <label class="form-check-label text-white" for="status">Activa</label>
                </div>
                <button type="submit" class="btn btn-light rounded-pill">Guardar categoría</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="table-dark-glass p-3">
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0 text-white">
                    <thead>
                        <tr class="text-secondary">
                            <th>Nombre</th>
                            <th>Etiqueta</th>
                            <th>País</th>
                            <th>Orden</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= e($category['name']); ?></td>
                            <td><?= e($category['short_label']); ?></td>
                            <td><?= e($category['country_code']); ?></td>
                            <td><?= (int) $category['sort_order']; ?></td>
                            <td><?= (int) $category['status'] === 1 ? 'Activa' : 'Inactiva'; ?></td>
                            <td class="text-end">
                                <form method="post" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $category['id']; ?>">
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
