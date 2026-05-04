<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/styles.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #F0F2F5; margin: 0; }
        .login-card { background: white; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); width: 100%; max-width: 420px; padding: 48px 36px; text-align: center; }
        .login-card img { width: 80px; height: 80px; object-fit: contain; border-radius: 8px; margin-bottom: 16px; }
        .login-card h1 { font-size: 20px; color: var(--primary, #2C3E50); margin-bottom: 6px; }
        .login-card p.subtitle { font-size: 14px; color: #7F8C8D; margin-bottom: 28px; }
        .login-card .form-group { text-align: left; margin-bottom: 16px; }
        .login-card .form-group label { display: block; font-size: 13px; color: #2C3E50; margin-bottom: 6px; font-weight: 500; }
        .login-card .form-control { width: 100%; box-sizing: border-box; padding: 12px 14px; font-size: 15px; border: 1px solid #DDD; border-radius: 8px; }
        .login-card .form-control:focus { outline: none; border-color: var(--primary, #2C3E50); }
        .login-card .btn { width: 100%; margin-top: 8px; padding: 13px; font-size: 15px; font-weight: 600; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-danger { background: #FDECEA; color: #B71C1C; border: 1px solid #F5C6CB; }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="<?= BASE_URL ?>/img/logo-tandil.png" alt="Logo" onerror="this.style.display='none'">
        <h1><?= APP_NAME ?></h1>
        <p class="subtitle">Ingrese sus credenciales para continuar</p>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login" autocomplete="off">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" class="form-control"
                       value="<?= htmlspecialchars($oldUser ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label>Contrasena</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg">Ingresar</button>
        </form>
    </div>
</body>
</html>
