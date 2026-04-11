<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND status = 1 LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['name'];
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Credenciales incorrectas.';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-site d-flex align-items-center justify-content-center min-vh-100">
    <div class="glass-panel login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <span class="brand-dot mb-3"></span>
            <h1 class="h3 text-white">Panel administrativo</h1>
            <p class="text-secondary mb-0">Inicia sesión para gestionar categorías, productos y textos.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger rounded-4"><?= e($error); ?></div>
        <?php endif; ?>

        <form method="post" class="d-grid gap-3">
            <div>
                <label class="form-label text-white">Correo</label>
                <input type="email" name="email" class="form-control" placeholder="admin@demo.com" required>
            </div>
            <div>
                <label class="form-label text-white">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="********" required>
            </div>
            <button class="btn btn-light rounded-pill py-2" type="submit">Entrar</button>
            <a href="../index.php" class="btn btn-outline-light rounded-pill py-2">Volver al sitio</a>
        </form>
        <div class="small text-secondary mt-4">
            Usuario demo: <strong class="text-white">admin@demo.com</strong><br>
            Clave demo: <strong class="text-white">admin123</strong>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
