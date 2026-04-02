<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Sistema') ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/styles.css">
    <script src="<?= BASE_URL ?>/js/app.js"></script>
</head>
<body>
<div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?= BASE_URL ?>/img/logo-tandil.png" alt="Logo" class="sidebar-logo">
            <h2><?= APP_NAME ?></h2>
        </div>
        <nav class="sidebar-nav">
            <?php
            $currentRoute = $GLOBALS['current_route'] ?? 'dashboard';
            $menuItems = [
                ['route' => 'dashboard',      'icon' => '&#9632;', 'label' => 'Dashboard'],
                ['route' => 'solicitudes',    'icon' => '&#9776;', 'label' => 'Solicitudes'],
                ['route' => 'personas',       'icon' => '&#9787;', 'label' => 'Personas'],
                ['route' => 'configuracion',  'icon' => '&#9881;', 'label' => 'Configuracion'],
            ];
            foreach ($menuItems as $item):
                $isActive = ($currentRoute === $item['route']
                    || strpos($currentRoute, rtrim($item['route'], '/') . '/') === 0) ? ' active' : '';
            ?>
            <a href="<?= BASE_URL ?>/<?= $item['route'] ?>" class="<?= $isActive ?>">
                <span class="icon"><?= $item['icon'] ?></span>
                <?= $item['label'] ?>
            </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h1><?= htmlspecialchars($pageTitle ?? 'Sistema') ?></h1>
            <span class="text-muted"><?= date('d/m/Y H:i') ?></span>
        </div>
        <div class="content-area">
            <?= $content ?? '' ?>
        </div>
    </div>
</div>

<?php if (isset($extraJs)): ?>
<script src="<?= BASE_URL ?>/js/<?= $extraJs ?>"></script>
<?php endif; ?>
</body>
</html>
