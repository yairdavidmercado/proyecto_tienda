<?php
require __DIR__ . '/partials.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = [
        'site_name',
        'site_description',
        'hero_title',
        'hero_subtitle',
        'whatsapp_number',
        'whatsapp_message_general',
    ];

    $stmt = $pdo->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
    foreach ($allowed as $key) {
        $stmt->execute([trim($_POST[$key] ?? ''), $key]);
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
            <label class="form-label text-white">Mensaje general</label>
            <input type="text" name="whatsapp_message_general" class="form-control" value="<?= e($settings['whatsapp_message_general'] ?? ''); ?>">
        </div>
        <div class="col-12">
            <button class="btn btn-light rounded-pill px-4">Guardar cambios</button>
        </div>
    </form>
</div>
<?php admin_footer(); ?>
