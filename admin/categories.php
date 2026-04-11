<?php
require __DIR__ . '/partials.php';

$supportedCountries = get_supported_countries();
$editingCategoryId = (int) ($_GET['edit'] ?? 0);
$editingCategory = null;

if ($editingCategoryId > 0) {
    $editStmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $editStmt->execute([$editingCategoryId]);
    $editingCategory = $editStmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    if ($action === 'create') {
        $rawCountryCodes = $_POST['country_codes'] ?? [];
        $countryCodes = normalize_country_codes(is_array($rawCountryCodes) ? $rawCountryCodes : []);
        $countryCode = $countryCodes[0] ?? 'CO';
        $countryCodesCsv = implode(',', $countryCodes);

        if (empty($countryCodes)) {
            flash_message('danger', 'Debes seleccionar al menos un país para la categoría.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name, short_label, description, country_code, country_codes, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                trim($_POST['name'] ?? ''),
                trim($_POST['short_label'] ?? ''),
                trim($_POST['description'] ?? ''),
                $countryCode,
                $countryCodesCsv,
                (int) ($_POST['sort_order'] ?? 0),
                isset($_POST['status']) ? 1 : 0,
            ]);
            flash_message('success', 'Categoría creada correctamente.');
        }
    }

    if ($action === 'update') {
        $categoryId = (int) ($_POST['id'] ?? 0);
        $rawCountryCodes = $_POST['country_codes'] ?? [];
        $countryCodes = normalize_country_codes(is_array($rawCountryCodes) ? $rawCountryCodes : []);
        $countryCode = $countryCodes[0] ?? 'CO';
        $countryCodesCsv = implode(',', $countryCodes);

        $categoryStmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $categoryStmt->execute([$categoryId]);
        $category = $categoryStmt->fetch();

        if (!$category) {
            flash_message('danger', 'La categoría que intentas editar no existe.');
        } elseif (empty($countryCodes)) {
            flash_message('danger', 'Debes seleccionar al menos un país para la categoría.');
        } else {
            $stmt = $pdo->prepare('UPDATE categories SET name = ?, short_label = ?, description = ?, country_code = ?, country_codes = ?, sort_order = ?, status = ? WHERE id = ?');
            $stmt->execute([
                trim($_POST['name'] ?? ''),
                trim($_POST['short_label'] ?? ''),
                trim($_POST['description'] ?? ''),
                $countryCode,
                $countryCodesCsv,
                (int) ($_POST['sort_order'] ?? 0),
                isset($_POST['status']) ? 1 : 0,
                $categoryId,
            ]);
            flash_message('success', 'Categoría actualizada correctamente.');
        }
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
$isEditing = is_array($editingCategory);
$selectedCountries = $isEditing
    ? normalize_country_codes(explode(',', (string) ($editingCategory['country_codes'] ?: $editingCategory['country_code'])))
    : ['CO'];
admin_header('Categorías');
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="glass-panel p-4">
            <h2 class="h5 text-white mb-3"><?= $isEditing ? 'Editar categoría' : 'Nueva categoría'; ?></h2>
            <form method="post" class="d-grid gap-3">
                <input type="hidden" name="action" value="<?= $isEditing ? 'update' : 'create'; ?>">
                <?php if ($isEditing): ?>
                    <input type="hidden" name="id" value="<?= (int) $editingCategory['id']; ?>">
                <?php endif; ?>
                <div>
                    <label class="form-label text-white">Nombre</label>
                    <input type="text" name="name" class="form-control" value="<?= e($editingCategory['name'] ?? ''); ?>" required>
                </div>
                <div>
                    <label class="form-label text-white">Etiqueta corta</label>
                    <input type="text" name="short_label" class="form-control" maxlength="8" placeholder="CP" value="<?= e($editingCategory['short_label'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label text-white">Descripción</label>
                    <textarea name="description" class="form-control" rows="4"><?= e($editingCategory['description'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label class="form-label text-white">Países</label>
                    <div class="d-grid gap-2">
                        <?php foreach ($supportedCountries as $countryCode => $countryName): ?>
                            <label class="form-check text-white">
                                <input class="form-check-input" type="checkbox" name="country_codes[]" value="<?= e($countryCode); ?>" <?= in_array($countryCode, $selectedCountries, true) ? 'checked' : ''; ?>>
                                <span class="form-check-label"><?= e($countryCode . ' - ' . $countryName); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="small text-secondary">Selecciona uno o varios países.</div>
                </div>
                <div>
                    <label class="form-label text-white">Orden</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= e((string) ($editingCategory['sort_order'] ?? 0)); ?>">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="status" id="status" <?= !$isEditing || (int) ($editingCategory['status'] ?? 0) === 1 ? 'checked' : ''; ?>>
                    <label class="form-check-label text-white" for="status">Activa</label>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-light rounded-pill"><?= $isEditing ? 'Actualizar categoría' : 'Guardar categoría'; ?></button>
                    <?php if ($isEditing): ?>
                        <a href="categories.php" class="btn btn-outline-light rounded-pill">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="table-dark-glass p-3">
            <div class="table-responsive admin-table-scroll">
                <table class="table table-borderless align-middle mb-0 text-white admin-data-table">
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
                        <?php
                            $categoryCountries = normalize_country_codes(explode(',', (string) ($category['country_codes'] ?: $category['country_code'])));
                            $categoryCountryLabels = [];
                            foreach ($categoryCountries as $categoryCountryCode) {
                                $categoryCountryLabels[] = $categoryCountryCode . ' - ' . ($supportedCountries[$categoryCountryCode] ?? $categoryCountryCode);
                            }
                        ?>
                        <tr>
                            <td><?= e($category['name']); ?></td>
                            <td><?= e($category['short_label']); ?></td>
                            <td><?= e(implode(', ', $categoryCountryLabels)); ?></td>
                            <td><?= (int) $category['sort_order']; ?></td>
                            <td><?= (int) $category['status'] === 1 ? 'Activa' : 'Inactiva'; ?></td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="categories.php?edit=<?= (int) $category['id']; ?>" class="btn btn-sm btn-outline-light rounded-pill">Editar</a>
                                    <form method="post" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $category['id']; ?>">
                                        <button class="btn btn-sm btn-outline-light rounded-pill">Eliminar</button>
                                    </form>
                                </div>
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
