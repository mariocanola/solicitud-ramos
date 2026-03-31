<!-- Configuración General -->

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Configuración del Sistema</div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
            <?= $csrfField ?>

            <?php
            // Group configs by category
            $groups = [
                'Organización' => ['nombre_organizacion', 'logo_path'],
                'Correo' => ['correo_destino', 'correo_cc'],
                'SMTP' => ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure'],
                'Cupos' => ['cupo_default'],
            ];

            // Build lookup
            $configMap = [];
            foreach ($configuraciones as $c) {
                $configMap[$c['clave']] = $c;
            }
            ?>

            <?php foreach ($groups as $groupName => $claves): ?>
            <h4 style="color:var(--primary);margin:20px 0 10px;padding-bottom:5px;border-bottom:1px solid var(--border)">
                <?= $groupName ?>
            </h4>
            <div class="row">
                <?php foreach ($claves as $clave):
                    $config = $configMap[$clave] ?? null;
                    $tipo = ($clave === 'smtp_pass') ? 'password' : 'text';
                    if ($clave === 'smtp_port' || $clave === 'cupo_default') $tipo = 'number';
                ?>
                <div class="col-6">
                    <div class="form-group">
                        <label><?= htmlspecialchars($config['descripcion'] ?? $clave) ?></label>
                        <input type="<?= $tipo ?>" name="<?= htmlspecialchars($clave) ?>"
                               class="form-control"
                               value="<?= htmlspecialchars($config['valor'] ?? '') ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>

            <div class="text-right mt-3">
                <button type="submit" class="btn btn-success btn-lg">Guardar Configuración</button>
            </div>
        </form>
    </div>
</div>
