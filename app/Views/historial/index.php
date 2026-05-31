<h2 class="mb-4"><i class="bi bi-clock-history"></i> Historial de Sanciones</h2>

<!-- Formulario de búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form action="<?= site_url('historial/buscar') ?>" method="post" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Buscar por:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="criterio" id="criterio_dni" value="dni"
                               <?= (($criterio ?? 'dni') === 'dni') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="criterio_dni">DNI</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="criterio" id="criterio_apellido" value="apellido"
                               <?= (($criterio ?? '') === 'apellido') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="criterio_apellido">Apellido</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="valor" class="form-label">Valor de búsqueda</label>
                    <input type="text" class="form-control" id="valor" name="valor"
                           value="<?= esc($valor ?? '') ?>" required minlength="2"
                           placeholder="Ingrese DNI o apellido...">
                    <div class="invalid-feedback">Ingrese al menos 2 caracteres.</div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-ea w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resultados -->
<?php if (isset($resultados)): ?>
    <?php if (empty($resultados)): ?>
        <div class="alert alert-warning">
            <i class="bi bi-info-circle"></i> No se encontraron sanciones para el criterio ingresado.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <strong><?= count($resultados) ?> resultado(s) encontrado(s)</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th colspan="4" class="text-center">DATOS DEL INFRACTOR</th>
                                <th colspan="3" class="text-center">SANCIÓN</th>
                                <th colspan="2" class="text-center">IMPUESTA POR</th>
                                <th>ESTADO</th>
                                <th class="text-center">ACCIONES</th>
                            </tr>
                            <tr class="table-secondary">
                                <th>DNI</th>
                                <th>Grado</th>
                                <th>Apellido</th>
                                <th>Nombre</th>
                                <th>Motivo</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Grado</th>
                                <th>Apellido</th>
                                <th>Estado</th>
                                <th class="text-center">Descargar / Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $r): ?>
                            <?php
                                $sid      = (int) $r['sancion_id'];
                                $cat      = $r['categoria'] ?? 'cuadros';
                                $badgeClass = match($r['estado'] ?? '') {
                                    'activa'   => 'bg-danger',
                                    'cumplida' => 'bg-success',
                                    'anulada'  => 'bg-secondary',
                                    default    => 'bg-primary',
                                };
                                $rutaDesc = ($cat === 'cadetes')
                                    ? 'sancion/cadetes/descargar/' . $sid
                                    : 'sancion/cuadros/descargar/' . $sid;
                            ?>
                            <tr>
                                <td><?= esc($r['dni_infractor']) ?></td>
                                <td><?= esc($r['grado_infractor']) ?></td>
                                <td><?= esc($r['apellido_infractor']) ?></td>
                                <td><?= esc($r['nombre_infractor']) ?></td>
                                <td title="<?= esc($r['motivo']) ?>"><?= esc(mb_strimwidth($r['motivo'], 0, 40, '...')) ?></td>
                                <td><?= esc(date('d/m/Y', strtotime($r['fecha_comision']))) ?></td>
                                <td><?= esc($r['tipo'] ?? '') ?></td>
                                <td><?= esc($r['grado_instructor']) ?></td>
                                <td><?= esc($r['apellido_instructor']) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?> badge-estado">
                                        <?= esc(ucfirst($r['estado'] ?? '')) ?>
                                    </span>
                                </td>
                                <td class="text-nowrap text-center">
                                    <!-- ANEXO 1: Word y PDF -->
                                    <a href="<?= site_url($rutaDesc . '/docx') ?>"
                                       class="btn btn-sm btn-primary" title="ANEXO 1 - Word">
                                        <i class="bi bi-file-earmark-word"></i>
                                    </a>
                                    <a href="<?= site_url($rutaDesc . '/pdf') ?>"
                                       class="btn btn-sm btn-danger" title="ANEXO 1 - PDF">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    <?php if ($cat === 'cuadros'): ?>
                                    <!-- ANEXO 2: Revisión de Oficio -->
                                    <a href="<?= site_url('sancion/cuadros/revision/' . $sid) ?>"
                                       class="btn btn-sm btn-outline-secondary" title="ANEXO 2 - Revisión de Oficio">
                                        <i class="bi bi-file-earmark-ruled"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (session()->get('usuario_rol') === 'admin'): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger ms-1"
                                            title="Eliminar registro"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEliminarSancion"
                                            data-id="<?= $sid ?>"
                                            data-cat="<?= esc($cat) ?>"
                                            data-label="<?= esc($r['grado_infractor'] . ' ' . $r['apellido_infractor'] . ', ' . $r['nombre_infractor'] . ' — DNI ' . $r['dni_infractor']) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (session()->get('usuario_rol') === 'admin'): ?>
<!-- Modal confirmar eliminación sanción -->
<div class="modal fade" id="modalEliminarSancion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Está por eliminar la siguiente sanción de forma <strong>permanente</strong>:</p>
                <div class="alert alert-warning py-2 mb-0">
                    <i class="bi bi-person-fill me-1"></i>
                    <strong id="modalSancionLabel"></strong>
                </div>
                <p class="text-muted small mt-2 mb-0">
                    Esta acción no puede deshacerse. Solo disponible para administradores.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <form id="formEliminarSancion" method="post" action="" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEliminarSancion').addEventListener('show.bs.modal', function (e) {
    var btn   = e.relatedTarget;
    var id    = btn.getAttribute('data-id');
    var cat   = btn.getAttribute('data-cat');
    var label = btn.getAttribute('data-label');
    document.getElementById('modalSancionLabel').textContent = label;
    document.getElementById('formEliminarSancion').action =
        '<?= site_url("sancion") ?>/' + cat + '/eliminar/' + id;
});
</script>
<?php endif; ?>
