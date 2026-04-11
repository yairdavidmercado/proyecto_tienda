<?php
require __DIR__ . '/partials.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = [
        'site_name',
        'site_description',
        'hero_title',
        'hero_subtitle',
        'whatsapp_number',
        'whatsapp_number_co',
        'whatsapp_number_es',
        'whatsapp_number_mx',
        'whatsapp_number_us',
        'whatsapp_message_general',
        'rate_cop_to_eur',
        'rate_cop_to_mxn',
        'rate_cop_to_usd',
    ];

    $numericRates = ['rate_cop_to_eur', 'rate_cop_to_mxn', 'rate_cop_to_usd'];
    $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($allowed as $key) {
        $value = trim($_POST[$key] ?? '');

        if (in_array($key, $numericRates, true)) {
            $normalized = str_replace(',', '.', $value);
            if (!is_numeric($normalized) || (float) $normalized <= 0) {
                continue;
            }
            $value = (string) $normalized;
        }

        $stmt->execute([$key, $value]);
    }
    flash_message('success', 'Ajustes actualizados correctamente.');
    header('Location: settings.php');
    exit;
}

$settings = get_settings($pdo);
admin_header('Ajustes del sitio');
?>
<div class="glass-panel p-4 p-lg-5">
    <form method="post" class="row g-4">
        <div class="col-md-6">
            <label class="form-label text-white">Nombre del sitio</label>
            <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Descripción SEO</label>
            <input type="text" name="site_description" class="form-control" value="<?= e($settings['site_description'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Título principal</label>
            <input type="text" name="hero_title" class="form-control" value="<?= e($settings['hero_title'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Subtítulo principal</label>
            <input type="text" name="hero_subtitle" class="form-control" value="<?= e($settings['hero_subtitle'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Número de WhatsApp</label>
            <input type="text" name="whatsapp_number" class="form-control" value="<?= e($settings['whatsapp_number'] ?? ''); ?>" placeholder="573001234567">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">WhatsApp Colombia (CO)</label>
            <input type="text" name="whatsapp_number_co" class="form-control" value="<?= e($settings['whatsapp_number_co'] ?? ($settings['whatsapp_number'] ?? '')); ?>" placeholder="573001234567">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">WhatsApp España (ES)</label>
            <input type="text" name="whatsapp_number_es" class="form-control" value="<?= e($settings['whatsapp_number_es'] ?? ($settings['whatsapp_number'] ?? '')); ?>" placeholder="34600111222">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">WhatsApp México (MX)</label>
            <input type="text" name="whatsapp_number_mx" class="form-control" value="<?= e($settings['whatsapp_number_mx'] ?? ($settings['whatsapp_number'] ?? '')); ?>" placeholder="5215512345678">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">WhatsApp Estados Unidos (US)</label>
            <input type="text" name="whatsapp_number_us" class="form-control" value="<?= e($settings['whatsapp_number_us'] ?? ($settings['whatsapp_number'] ?? '')); ?>" placeholder="12025550123">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Mensaje general</label>
            <input type="text" name="whatsapp_message_general" class="form-control" value="<?= e($settings['whatsapp_message_general'] ?? ''); ?>">
        </div>
        <div class="col-12">
            <hr class="border-secondary-subtle">
            <h2 class="h5 text-white mb-1">Tasas de cambio (base COP)</h2>
            <p class="text-secondary mb-0 small">Puedes usar cualquiera de estos formatos: 1 COP = 0.00026 USD o 1 USD = 3850 COP. El sistema detecta ambos.</p>
        </div>
        <div class="col-md-4">
            <label class="form-label text-white">COP a EUR</label>
            <input type="text" name="rate_cop_to_eur" class="form-control" value="<?= e($settings['rate_cop_to_eur'] ?? '0.00023'); ?>" placeholder="0.00023 o 4300">
        </div>
        <div class="col-md-4">
            <label class="form-label text-white">COP a MXN</label>
            <input type="text" name="rate_cop_to_mxn" class="form-control" value="<?= e($settings['rate_cop_to_mxn'] ?? '0.0044'); ?>" placeholder="0.0044 o 227">
        </div>
        <div class="col-md-4">
            <label class="form-label text-white">COP a USD</label>
            <input type="text" name="rate_cop_to_usd" class="form-control" value="<?= e($settings['rate_cop_to_usd'] ?? '0.00026'); ?>" placeholder="0.00026 o 3850">
        </div>
        <div class="col-12">
            <button class="btn btn-light rounded-pill px-4">Guardar cambios</button>
        </div>
    </form>
</div>
<?php admin_footer(); ?>
