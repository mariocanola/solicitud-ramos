<!-- Listado de Solicitudes -->

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- Filtros -->
<div class="card">
    <div class="card-header">
        <span>Filtros de Busqueda</span>
        <?php
            $hayFiltros = !empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta'])
                       || !empty($filtros['id_sede']) || !empty($filtros['id_estado'])
                       || !empty($filtros['busqueda']);
        ?>
        <?php if ($hayFiltros): ?>
            <a href="<?= BASE_URL ?>/solicitudes" class="btn btn-outline btn-sm">Limpiar filtros</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form id="form_filtros" method="GET" action="<?= BASE_URL ?>/solicitudes">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Buscar por documento o nombre</label>
                        <input type="text" name="busqueda" class="form-control"
                               placeholder="Ej: 1234567890 o Juan Perez"
                               value="<?= htmlspecialchars($filtros['busqueda'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Fecha desde</label>
                        <input type="date" name="fecha_desde" class="form-control"
                               value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Fecha hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control"
                               value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <label>Sede</label>
                        <select name="id_sede" class="form-control">
                            <option value="">-- Todas --</option>
                            <?php foreach ($sedes as $sede): ?>
                            <option value="<?= $sede['id'] ?>" <?= ($filtros['id_sede'] ?? '') == $sede['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sede['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="id_estado" class="form-control">
                            <option value="">-- Todos --</option>
                            <?php foreach ($estados as $estado): ?>
                            <option value="<?= $estado['id'] ?>" <?= ($filtros['id_estado'] ?? '') == $estado['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($estado['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-6" style="display:flex;align-items:flex-end;gap:8px;padding-bottom:16px">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if ($hayFiltros): ?>
                        <a href="<?= BASE_URL ?>/solicitudes" class="btn btn-outline">Limpiar</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card mt-2">
    <div class="card-header">
        <span>Solicitudes (<?= $totalRegistros ?> resultado<?= $totalRegistros != 1 ? 's' : '' ?>)</span>
        <a href="<?= BASE_URL ?>/solicitudes/crear" class="btn btn-success btn-sm">+ Nueva Solicitud</a>
    </div>
    <div class="card-body">
        <?php if (empty($solicitudes)): ?>
            <div class="text-center" style="padding:40px 20px">
                <div style="font-size:48px;margin-bottom:10px;opacity:0.3">&#128269;</div>
                <?php if ($hayFiltros): ?>
                    <p style="font-size:16px;color:#555;margin-bottom:8px">No se encontraron solicitudes con los filtros aplicados.</p>
                    <a href="<?= BASE_URL ?>/solicitudes" class="btn btn-outline">Limpiar filtros</a>
                <?php else: ?>
                    <p style="font-size:16px;color:#555;margin-bottom:8px">No hay solicitudes registradas aun.</p>
                    <a href="<?= BASE_URL ?>/solicitudes/crear" class="btn btn-success">Crear primera solicitud</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Solicitante</th>
                        <th>Documento</th>
                        <th>Destinatario</th>
                        <th>Sede</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $contador = ($paginaActual - 1) * ITEMS_PER_PAGE + 1;
                foreach ($solicitudes as $s):
                ?>
                    <tr>
                        <td><?= $contador ?></td>
                        <td style="white-space:nowrap"><?= $s['fecha_solicitud'] ?></td>
                        <td><?= htmlspecialchars(Persona::getNombreCompleto($s)) ?></td>
                        <td><?= htmlspecialchars($s['documento']) ?></td>
                        <td><?= htmlspecialchars($s['nombre_destinatario'] ?? '') ?></td>
                        <td><?= htmlspecialchars($s['sede_nombre']) ?></td>
                        <td><?= htmlspecialchars($s['motivo_nombre']) ?></td>
                        <td>
                            <span class="badge" style="background:<?= htmlspecialchars($s['estado_color']) ?>">
                                <?= htmlspecialchars($s['estado_nombre']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/solicitudes/ver?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm">Ver detalle</a>
                        </td>
                    </tr>
                <?php
                $contador++;
                endforeach;
                ?>
                </tbody>
            </table>
        </div>

        <!-- Paginacion -->
        <?php if ($totalPaginas > 1): ?>
        <div class="pagination">
            <?php if ($paginaActual > 1): ?>
                <?php $params = $_GET; $params['pagina'] = $paginaActual - 1; ?>
                <a href="<?= BASE_URL ?>/solicitudes?<?= http_build_query($params) ?>">&laquo; Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <?php
                $params = $_GET;
                $params['pagina'] = $i;
                $qs = http_build_query($params);
                ?>
                <?php if ($i == $paginaActual): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/solicitudes?<?= $qs ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($paginaActual < $totalPaginas): ?>
                <?php $params = $_GET; $params['pagina'] = $paginaActual + 1; ?>
                <a href="<?= BASE_URL ?>/solicitudes?<?= http_build_query($params) ?>">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>
