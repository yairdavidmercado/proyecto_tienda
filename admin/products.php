<?php
require __DIR__ . '/partials.php';

$supportedCountries = get_supported_countries();

function get_product_gallery_images(): array
{
    $directories = [
        ['fs' => __DIR__ . '/../assets/img/products', 'web' => 'assets/img/products'],
        ['fs' => __DIR__ . '/../uploads/products', 'web' => 'uploads/products'],
    ];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    $images = [];

    foreach ($directories as $directory) {
        if (!is_dir($directory['fs'])) {
            continue;
        }

        $items = scandir($directory['fs']);
        if ($items === false) {
            continue;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $directory['fs'] . DIRECTORY_SEPARATOR . $item;
            if (!is_file($fullPath)) {
                continue;
            }

            $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions, true)) {
                continue;
            }

            $images[] = $directory['web'] . '/' . $item;
        }
    }

    sort($images);
    return array_values(array_unique($images));
}

function resolve_product_image_url(array $galleryImages, string $currentImageUrl = ''): array
{
    $manualImageUrl = trim($_POST['image_url'] ?? '');
    $galleryImage = trim($_POST['gallery_image'] ?? '');

    $upload = $_FILES['image_file'] ?? null;
    if (is_array($upload) && isset($upload['error']) && (int) $upload['error'] !== UPLOAD_ERR_NO_FILE) {
        if ((int) $upload['error'] !== UPLOAD_ERR_OK) {
            return [$currentImageUrl, 'No se pudo subir la imagen. Intenta nuevamente.'];
        }

        $tmpPath = (string) ($upload['tmp_name'] ?? '');
        $originalName = (string) ($upload['name'] ?? '');
        $fileSize = (int) ($upload['size'] ?? 0);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

        if (!in_array($extension, $allowedExtensions, true)) {
            return [$currentImageUrl, 'Formato no permitido. Usa JPG, PNG, WEBP, GIF o SVG.'];
        }

        if ($fileSize > 5 * 1024 * 1024) {
            return [$currentImageUrl, 'La imagen supera el tamano maximo de 5MB.'];
        }

        if (!is_uploaded_file($tmpPath)) {
            return [$currentImageUrl, 'No se pudo validar el archivo subido.'];
        }

        $uploadDir = __DIR__ . '/../uploads/products';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            return [$currentImageUrl, 'No se pudo crear la carpeta de subidas.'];
        }

        try {
            $fileName = 'product-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        } catch (Throwable $e) {
            $fileName = 'product-' . date('Ymd-His') . '-' . mt_rand(1000, 9999) . '.' . $extension;
        }

        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        if (!move_uploaded_file($tmpPath, $targetPath)) {
            return [$currentImageUrl, 'No se pudo guardar la imagen subida.'];
        }

        return ['uploads/products/' . $fileName, null];
    }

    if ($galleryImage !== '' && in_array($galleryImage, $galleryImages, true)) {
        return [$galleryImage, null];
    }

    if ($manualImageUrl !== '') {
        return [$manualImageUrl, null];
    }

    return [$currentImageUrl, null];
}

function product_image_src(string $imageUrl): string
{
    $imageUrl = trim($imageUrl);
    if ($imageUrl === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $imageUrl) === 1) {
        return $imageUrl;
    }

    return '../' . ltrim($imageUrl, '/');
}

$galleryImages = get_product_gallery_images();
$editingProductId = (int) ($_GET['edit'] ?? 0);
$editingProduct = null;

if ($editingProductId > 0) {
    $editStmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $editStmt->execute([$editingProductId]);
    $editingProduct = $editStmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    if ($action === 'create') {
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $rawCountryCodes = $_POST['country_codes'] ?? [];
        $countryCodes = normalize_country_codes(is_array($rawCountryCodes) ? $rawCountryCodes : []);
        $countryCode = $countryCodes[0] ?? 'CO';
        $countryCodesCsv = implode(',', $countryCodes);

        $categoryStmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $categoryStmt->execute([$categoryId]);
        $category = $categoryStmt->fetch();
        [$imageUrl, $imageError] = resolve_product_image_url($galleryImages);

        if (!$category) {
            flash_message('danger', 'La categoría seleccionada no existe.');
        } elseif (empty($countryCodes)) {
            flash_message('danger', 'Debes seleccionar al menos un país para el producto.');
        } elseif ($imageError) {
            flash_message('danger', $imageError);
        } else {
            $priceBaseCurrency = ($_POST['price_base_currency'] ?? 'COP') === 'LOCAL' ? 'LOCAL' : 'COP';

            $stmt = $pdo->prepare('INSERT INTO products (category_id, country_code, country_codes, name, short_description, price_label, price_base_currency, image_url, sort_order, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $categoryId,
                $countryCode,
                $countryCodesCsv,
                trim($_POST['name'] ?? ''),
                trim($_POST['short_description'] ?? ''),
                trim($_POST['price_label'] ?? ''),
                $priceBaseCurrency,
                $imageUrl,
                (int) ($_POST['sort_order'] ?? 0),
                isset($_POST['featured']) ? 1 : 0,
                isset($_POST['status']) ? 1 : 0,
            ]);
            flash_message('success', 'Producto creado correctamente.');
        }
    }

    if ($action === 'update') {
        $productId = (int) ($_POST['id'] ?? 0);
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $rawCountryCodes = $_POST['country_codes'] ?? [];
        $countryCodes = normalize_country_codes(is_array($rawCountryCodes) ? $rawCountryCodes : []);
        $countryCode = $countryCodes[0] ?? 'CO';
        $countryCodesCsv = implode(',', $countryCodes);

        $productStmt = $pdo->prepare('SELECT id, image_url FROM products WHERE id = ?');
        $productStmt->execute([$productId]);
        $product = $productStmt->fetch();

        $categoryStmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $categoryStmt->execute([$categoryId]);
        $category = $categoryStmt->fetch();

        [$imageUrl, $imageError] = resolve_product_image_url($galleryImages, (string) ($product['image_url'] ?? ''));

        if (!$product) {
            flash_message('danger', 'El producto que intentas editar no existe.');
        } elseif (!$category) {
            flash_message('danger', 'La categoría seleccionada no existe.');
        } elseif (empty($countryCodes)) {
            flash_message('danger', 'Debes seleccionar al menos un país para el producto.');
        } elseif ($imageError) {
            flash_message('danger', $imageError);
        } else {
            $priceBaseCurrency = ($_POST['price_base_currency'] ?? 'COP') === 'LOCAL' ? 'LOCAL' : 'COP';

            $stmt = $pdo->prepare('UPDATE products SET category_id = ?, country_code = ?, country_codes = ?, name = ?, short_description = ?, price_label = ?, price_base_currency = ?, image_url = ?, sort_order = ?, featured = ?, status = ? WHERE id = ?');
            $stmt->execute([
                $categoryId,
                $countryCode,
                $countryCodesCsv,
                trim($_POST['name'] ?? ''),
                trim($_POST['short_description'] ?? ''),
                trim($_POST['price_label'] ?? ''),
                $priceBaseCurrency,
                $imageUrl,
                (int) ($_POST['sort_order'] ?? 0),
                isset($_POST['featured']) ? 1 : 0,
                isset($_POST['status']) ? 1 : 0,
                $productId,
            ]);
            flash_message('success', 'Producto actualizado correctamente.');
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
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.sort_order ASC, p.id DESC')->fetchAll();
$isEditing = is_array($editingProduct);
$selectedCountries = $isEditing
    ? normalize_country_codes(explode(',', (string) ($editingProduct['country_codes'] ?: $editingProduct['country_code'])))
    : ['CO'];
admin_header('Productos');
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="glass-panel p-4">
            <h2 class="h5 text-white mb-3"><?= $isEditing ? 'Editar producto' : 'Nuevo producto'; ?></h2>
            <form method="post" class="d-grid gap-3" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= $isEditing ? 'update' : 'create'; ?>">
                <?php if ($isEditing): ?>
                    <input type="hidden" name="id" value="<?= (int) $editingProduct['id']; ?>">
                <?php endif; ?>
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
                    <label class="form-label text-white">Categoría</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id']; ?>" <?= $isEditing && (int) $editingProduct['category_id'] === (int) $category['id'] ? 'selected' : ''; ?>><?= e('[' . $category['country_code'] . '] ' . $category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label text-white">Nombre</label>
                    <input type="text" name="name" class="form-control" value="<?= e($editingProduct['name'] ?? ''); ?>" required>
                </div>
                <div>
                    <label class="form-label text-white">Descripción corta</label>
                    <textarea name="short_description" class="form-control" rows="4"><?= e($editingProduct['short_description'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label class="form-label text-white">Cómo tomar el precio</label>
                    <select name="price_base_currency" class="form-select">
                        <option value="COP" <?= !$isEditing || ($editingProduct['price_base_currency'] ?? 'COP') === 'COP' ? 'selected' : ''; ?>>
                            Tomar como COP y convertir según el país
                        </option>
                        <option value="LOCAL" <?= $isEditing && ($editingProduct['price_base_currency'] ?? 'COP') === 'LOCAL' ? 'selected' : ''; ?>>
                            Tomar directo en la moneda del país
                        </option>
                    </select>
                    <div class="small text-secondary">
                        Usa COP para productos creados en pesos. Usa directo para España, Estados Unidos o México cuando escribas el valor final en EUR, USD o MXN.
                    </div>
                </div>
                <div>
                    <label class="form-label text-white">Orden (menor primero)</label>
                    <input type="number" name="sort_order" class="form-control" min="0" step="1" value="<?= (int) ($editingProduct['sort_order'] ?? 0); ?>">
                </div>
                <div>
                    <label class="form-label text-white">URL de imagen</label>
                    <input type="text" name="image_url" class="form-control" placeholder="https://... o ruta local" value="<?= e($editingProduct['image_url'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label text-white">Elegir de galería</label>
                    <select name="gallery_image" class="form-select">
                        <option value="">No seleccionar</option>
                        <?php foreach ($galleryImages as $galleryImage): ?>
                            <option value="<?= e($galleryImage); ?>" <?= $isEditing && ($editingProduct['image_url'] ?? '') === $galleryImage ? 'selected' : ''; ?>><?= e($galleryImage); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="small text-secondary">Si subes una imagen, tiene prioridad sobre la galería y la URL.</div>
                </div>
                <div>
                    <label class="form-label text-white">Subir imagen</label>
                    <input type="file" name="image_file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/*">
                    <div class="small text-secondary">Tamano maximo: 5MB.</div>
                </div>
                <?php if ($isEditing && !empty($editingProduct['image_url'])): ?>
                    <div>
                        <label class="form-label text-white">Vista previa actual</label>
                        <div class="rounded overflow-hidden border border-secondary-subtle" style="max-width: 220px;">
                            <img src="<?= e(product_image_src((string) $editingProduct['image_url'])); ?>" alt="Imagen del producto" class="img-fluid">
                        </div>
                    </div>
                <?php endif; ?>
                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured" <?= $isEditing && (int) ($editingProduct['featured'] ?? 0) === 1 ? 'checked' : ''; ?>>
                        <label class="form-check-label text-white" for="featured">Destacado</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" id="statusProduct" <?= !$isEditing || (int) ($editingProduct['status'] ?? 0) === 1 ? 'checked' : ''; ?>>
                        <label class="form-check-label text-white" for="statusProduct">Activo</label>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-light rounded-pill"><?= $isEditing ? 'Actualizar producto' : 'Guardar producto'; ?></button>
                    <?php if ($isEditing): ?>
                        <a href="products.php" class="btn btn-outline-light rounded-pill">Cancelar</a>
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
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>País</th>
                            <th>Precio</th>
                            <th>Orden</th>
                            <th>Imagen</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <?php
                            $productCountries = normalize_country_codes(explode(',', (string) ($product['country_codes'] ?: $product['country_code'])));
                            $countryLabels = [];
                            foreach ($productCountries as $productCountryCode) {
                                $countryLabels[] = $productCountryCode . ' - ' . ($supportedCountries[$productCountryCode] ?? $productCountryCode);
                            }
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($product['name']); ?></div>
                                <div class="small text-secondary"><?= e($product['short_description']); ?></div>
                            </td>
                            <td><?= e($product['category_name']); ?></td>
                            <td><?= e(implode(', ', $countryLabels)); ?></td>
                            <td><?= e($product['price_label']); ?></td>
                            <td><?= (int) ($product['sort_order'] ?? 0); ?></td>
                            <td>
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?= e(product_image_src((string) $product['image_url'])); ?>" alt="<?= e($product['name']); ?>" class="rounded" style="width:56px;height:56px;object-fit:cover;">
                                <?php else: ?>
                                    <span class="text-secondary small">Sin imagen</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="products.php?edit=<?= (int) $product['id']; ?>" class="btn btn-sm btn-outline-light rounded-pill">Editar</a>
                                    <form method="post" onsubmit="return confirm('¿Eliminar este producto?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $product['id']; ?>">
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
