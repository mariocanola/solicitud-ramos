<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/styles.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #F0F2F5; }
        .login-card { background: white; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; padding: 40px 30px; text-align: center; }
        .login-card img { width: 70px; height: 70px; object-fit: contain; border-radius: 8px; margin-bottom: 12px; }
        .login-card h1 { font-size: 18px; color: var(--primary); margin-bottom: 6px; }
        .login-card p { font-size: 13px; color: #7F8C8D; margin-bottom: 24px; }
        .login-card .form-group { text-align: left; }
        .login-card .btn { width: 100%; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="<?= BASE_URL ?>/img/logo-tandil.png" alt="Logo">
        <h1><?= APP_NAME ?></h1>
        <p>Ingrese su contrasena para continuar</p>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label>Contrasena</label>
                <input type="password" name="password" class="form-control" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-lg">Ingresar</button>
        </form>
    </div>
</body>
</html>
