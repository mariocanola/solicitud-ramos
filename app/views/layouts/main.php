<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Sistema') ?> - <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/logo-tandil.png">
    <?php $cssVer = @filemtime(BASE_PATH . '/public/css/styles.css') ?: time(); ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/styles.css?v=<?= $cssVer ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" defer></script>
    <?php $appJsVer = @filemtime(BASE_PATH . '/public/js/app.js') ?: time(); ?>
    <?php $fvJsVer  = @filemtime(BASE_PATH . '/public/js/form-validator.js') ?: time(); ?>
    <script src="<?= BASE_URL ?>/js/app.js?v=<?= $appJsVer ?>" defer></script>
    <script src="<?= BASE_URL ?>/js/form-validator.js?v=<?= $fvJsVer ?>"></script>
</head>
<body>
<div class="app-wrapper">
    <!-- Sidebar overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?= BASE_URL ?>/img/logo-tandil.png" alt="Logo" class="sidebar-logo">
            <h2><?= APP_NAME ?></h2>
        </div>
        <nav class="sidebar-nav">
            <?php
            $currentRoute = $GLOBALS['current_route'] ?? 'dashboard';
            $rol = Auth::rol();

            if ($rol === 'admin') {
                $menuItems = [
                    ['route' => 'dashboard',      'icon' => '&#9632;', 'label' => 'Dashboard'],
                    ['route' => 'solicitudes',    'icon' => '&#9776;', 'label' => 'Solicitudes'],
                    ['route' => 'personas',       'icon' => '&#9787;', 'label' => 'Personas'],
                    ['route' => 'configuracion',  'icon' => '&#9881;', 'label' => 'Configuracion'],
                ];
            } else {
                $menuItems = [
                    ['route' => 'solicitudes/nueva', 'icon' => '&#10010;', 'label' => 'Nueva solicitud'],
                ];
            }

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
            <button class="menu-toggle" id="menu-toggle" aria-label="Abrir menú">
                <span></span><span></span><span></span>
            </button>
            <h1><?= htmlspecialchars($pageTitle ?? 'Sistema') ?></h1>
            <div class="top-bar-right">
                <span class="text-muted"><?= date('d/m/Y H:i') ?></span>
                <?php $u = Auth::user(); if ($u):
                    // Iniciales para el avatar (max 2 letras)
                    $partes = preg_split('/\s+/', trim($u['nombre']));
                    $iniciales = strtoupper(mb_substr($partes[0] ?? '', 0, 1) . (isset($partes[1]) ? mb_substr($partes[1], 0, 1) : ''));
                    if ($iniciales === '') {
                        $iniciales = strtoupper(mb_substr($u['username'], 0, 2));
                    }
                    $rolLabel = $u['rol'] === 'admin' ? 'Administrador' : 'Operador';
                ?>
                <div class="user-dropdown">
                    <button class="user-dropdown-btn" id="user-dropdown-btn">
                        <div class="user-avatar" aria-hidden="true"><?= htmlspecialchars($iniciales) ?></div>
                        <span class="user-name"><?= htmlspecialchars($u['nombre']) ?></span>
                        <svg class="dropdown-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div class="dropdown-menu" id="user-dropdown-menu">
                        <div class="dropdown-header">
                            <div class="dropdown-user-info">
                                <div class="dropdown-user-name"><?= htmlspecialchars($u['nombre']) ?></div>
                                <span class="user-role role-<?= htmlspecialchars($u['rol']) ?>"><?= $rolLabel ?></span>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?= BASE_URL ?>/logout" class="dropdown-item dropdown-item-logout" id="btn-logout">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="content-area">
            <?= $content ?? '' ?>
        </div>
    </div>
</div>

<?php if (isset($extraJs)): ?>
<script src="<?= BASE_URL ?>/js/<?= $extraJs ?>"></script>
<?php endif; ?>

<script>
// Sidebar toggle (mobile)
document.addEventListener('DOMContentLoaded', function() {
    var menuToggle = document.getElementById('menu-toggle');
    var sidebar    = document.getElementById('sidebar');
    var overlay    = document.getElementById('sidebar-overlay');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar on nav link click (mobile)
    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) { closeSidebar(); }
            });
        });
    }
});

// User Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtn = document.getElementById('user-dropdown-btn');
    const dropdown = document.querySelector('.user-dropdown');
    const logoutBtn = document.getElementById('btn-logout');
    
    if (dropdownBtn && dropdown) {
        // Toggle dropdown
        dropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
        
        // Close dropdown when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdown.classList.remove('active');
            }
        });
    }
    
    // Confirmación amigable antes de cerrar sesión
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            
            // Close dropdown first
            if (dropdown) {
                dropdown.classList.remove('active');
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Cerrar sesión?',
                    text: 'Vas a salir del sistema.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, salir',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#E74C3C',
                    cancelButtonColor: '#95A5A6',
                    reverseButtons: true
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            } else {
                if (confirm('Cerrar sesión?')) {
                    window.location.href = url;
                }
            }
        });
    }
});
</script>
</body>
</html>
