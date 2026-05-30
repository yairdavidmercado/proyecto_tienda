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
        'whatsapp_message_co',
        'whatsapp_message_es',
        'whatsapp_message_mx',
        'whatsapp_message_us',
        'payment_methods_co',
        'payment_methods_es',
        'payment_methods_mx',
        'payment_methods_us',
        'rate_cop_to_eur',
        'rate_cop_to_mxn',
        'rate_cop_to_usd',
        'wompi_environment',
        'wompi_public_key',
        'wompi_private_key',
        'wompi_integrity_key',
        'wompi_events_key',
        'wompi_enabled',
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
<!--         <div class="col-md-6">
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
        </div> -->
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
            <label class="form-label text-white">Mensaje WhatsApp Colombia (CO)</label>
            <input type="text" name="whatsapp_message_co" class="form-control" value="<?= e($settings['whatsapp_message_co'] ?? ($settings['whatsapp_message_general'] ?? '')); ?>" placeholder="Hola, quiero informacion de pantallas streaming para Colombia.">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Mensaje WhatsApp España (ES)</label>
            <input type="text" name="whatsapp_message_es" class="form-control" value="<?= e($settings['whatsapp_message_es'] ?? ($settings['whatsapp_message_general'] ?? '')); ?>" placeholder="Hola, quiero informacion de pantallas streaming para Espana.">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Mensaje WhatsApp México (MX)</label>
            <input type="text" name="whatsapp_message_mx" class="form-control" value="<?= e($settings['whatsapp_message_mx'] ?? ($settings['whatsapp_message_general'] ?? '')); ?>" placeholder="Hola, quiero informacion de pantallas streaming para Mexico.">
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Mensaje WhatsApp Estados Unidos (US)</label>
            <input type="text" name="whatsapp_message_us" class="form-control" value="<?= e($settings['whatsapp_message_us'] ?? ($settings['whatsapp_message_general'] ?? '')); ?>" placeholder="Hello, I want info about streaming screens for the US.">
        </div>
        <div class="col-12">
            <hr class="border-secondary-subtle">
            <h2 class="h5 text-white mb-1">Medios de pago por pais</h2>
            <p class="text-secondary mb-0 small">Este contenido reemplaza la seccion publica de beneficios y cambia segun el selector de pais.</p>
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Medios de pago Colombia (CO)</label>
            <textarea name="payment_methods_co" class="form-control" rows="8" placeholder="NEQUI, DAVIPLATA, bancos, etc."><?= e($settings['payment_methods_co'] ?? ''); ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Medios de pago España (ES)</label>
            <textarea name="payment_methods_es" class="form-control" rows="8" placeholder="Bizum, transferencia, etc."><?= e($settings['payment_methods_es'] ?? ''); ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Medios de pago México (MX)</label>
            <textarea name="payment_methods_mx" class="form-control" rows="8" placeholder="SPEI, OXXO, transferencia, etc."><?= e($settings['payment_methods_mx'] ?? ''); ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label text-white">Medios de pago Estados Unidos (US)</label>
            <textarea name="payment_methods_us" class="form-control" rows="8" placeholder="Zelle, ACH, etc."><?= e($settings['payment_methods_us'] ?? ''); ?></textarea>
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

            <div class="col-12">
                <hr class="border-secondary-subtle">
                <h2 class="h5 text-white mb-1">Wompi</h2>
                <p class="text-secondary mb-0 small">
                    Usa llaves sandbox para pruebas y llaves producción cuando el cliente ya vaya a recibir dinero real.
                </p>
            </div>

            <div class="col-md-4">
                <label class="form-label text-white">Wompi habilitado</label>
                <select name="wompi_enabled" class="form-control">
                    <option value="1" <?= (($settings['wompi_enabled'] ?? '1') === '1') ? 'selected' : ''; ?>>Sí</option>
                    <option value="0" <?= (($settings['wompi_enabled'] ?? '1') === '0') ? 'selected' : ''; ?>>No</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label text-white">Ambiente</label>
                <select name="wompi_environment" class="form-control">
                    <option value="sandbox" <?= (($settings['wompi_environment'] ?? 'sandbox') === 'sandbox') ? 'selected' : ''; ?>>Sandbox</option>
                    <option value="production" <?= (($settings['wompi_environment'] ?? 'sandbox') === 'production') ? 'selected' : ''; ?>>Producción</option>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label text-white">Llave pública</label>
                <input type="text" name="wompi_public_key" class="form-control" value="<?= e($settings['wompi_public_key'] ?? ''); ?>" placeholder="pub_test_...">
            </div>

            <div class="col-md-12">
                <label class="form-label text-white">Llave privada</label>
                <input type="text" name="wompi_private_key" class="form-control" value="<?= e($settings['wompi_private_key'] ?? ''); ?>" placeholder="prv_test_...">
            </div>

            <div class="col-md-12">
                <label class="form-label text-white">Llave de integridad</label>
                <input type="text" name="wompi_integrity_key" class="form-control" value="<?= e($settings['wompi_integrity_key'] ?? ''); ?>" placeholder="test_integrity_...">
            </div>

            <div class="col-md-12">
                <label class="form-label text-white">Llave de eventos</label>
                <input type="text" name="wompi_events_key" class="form-control" value="<?= e($settings['wompi_events_key'] ?? ''); ?>" placeholder="test_events_...">
            </div>
            
            <button class="btn btn-light rounded-pill px-4">Guardar cambios</button>
        </div>
    </form>
</div>
<?php admin_footer(); ?>
