<?php
/**
 * Vista de historial de actuaciones administrativas.
 * Variables: $tipo, $meta, $actuaciones
 */
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center"
             style="width:48px;height:48px;background:rgba(107,15,26,.12);">
            <i class="bi <?= esc($meta['icono']) ?> fs-4" style="color:var(--ea-crimson)"></i>
        </div>
        <div>
            <h2 class="mb-0 fw-bold" style="font-size:1.2rem;">
                Historial — <?= esc($meta['titulo_corto']) ?>
            </h2>
            <p class="text-muted mb-0 small"><?= count($actuaciones) ?> actuacion(es) registrada(s)</p>
        </div>
    </div>
    <a href="<?= site_url("actuacion/{$tipo}") ?>" class="btn btn-ea-blue">
        <i class="bi bi-plus-circle"></i> Nueva Actuación
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-1"></i>
    <?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (empty($actuaciones)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi <?= esc($meta['icono']) ?> fs-1 text-muted mb-3 d-block"></i>
        <p class="text-muted mb-3">No hay actuaciones registradas aún.</p>
        <a href="<?= site_url("actuacion/{$tipo}") ?>" class="btn btn-ea-blue">
            <i class="bi bi-plus-circle"></i> Registrar la primera
        </a>
    </div>
</div>

<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead style="background:var(--ea-blue);color:#fff;">
                    <tr>
                        <th class="ps-3" style="width:60px;">#</th>
                        <th>Causante</th>
                        <th>Grado</th>
                        <th>Nro. Expediente</th>
                        <th>Fecha</th>
                        <th class="text-center" style="width:90px;">Estado</th>
                        <th class="text-center" style="width:160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($actuaciones as $act): ?>
                <tr>
                    <td class="ps-3 text-muted"><?= (int)$act->id ?></td>
                    <td class="fw-semibold"><?= esc($act->causante_apellido ?: '—') ?></td>
                    <td><?= esc($act->causante_grado ?: '—') ?></td>
                    <td><?= esc($act->nro_expediente ?: '—') ?></td>
                    <td class="text-muted small">
                        <?= date('d/m/Y H:i', strtotime($act->created_at)) ?>
                    </td>
                    <td class="text-center">
                        <?php
                        $estadoBadge = match($act->estado ?? 'activa') {
                            'activa'   => 'success',
                            'cerrada'  => 'secondary',
                            'anulada'  => 'danger',
                            default    => 'warning',
                        };
                        ?>
                        <span class="badge bg-<?= $estadoBadge ?>">
                            <?= esc(ucfirst($act->estado ?? 'activa')) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="<?= site_url("actuacion/{$tipo}/ver/{$act->id}") ?>"
                           class="btn btn-sm btn-outline-blue me-1"
                           title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= site_url("actuacion/{$tipo}/generar/{$act->id}") ?>"
                           class="btn btn-sm btn-ea-blue"
                           title="Descargar expediente completo (ZIP)"
                           onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm\'></span>';setTimeout(()=>{this.innerHTML='<i class=\'bi bi-download\'></i>'},3000);">
                            <i class="bi bi-download"></i>
                        </a>
                        <?php if (session()->get('usuario_rol') === 'admin'): ?>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger ms-1"
                                title="Eliminar registro"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEliminar"
                                data-id="<?= (int)$act->id ?>"
                                data-tipo="<?= esc($tipo) ?>"
                                data-label="<?= esc(($act->causante_grado ?? '') . ' ' . ($act->causante_apellido ?? '') . ' — Exp. ' . ($act->nro_expediente ?? '#' . $act->id)) ?>">
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

<?php if (session()->get('usuario_rol') === 'admin'): ?>
<!-- Modal confirmar eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Está por eliminar el siguiente registro de forma <strong>permanente</strong>:</p>
                <div class="alert alert-warning py-2 mb-0">
                    <i class="bi bi-person-fill me-1"></i>
                    <strong id="modalEliminarLabel2"></strong>
                </div>
                <p class="text-muted small mt-2 mb-0">
                    Esta acción no puede deshacerse. Solo disponible para administradores.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <form id="formEliminar" method="post" action="" class="d-inline">
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
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function (e) {
    var btn    = e.relatedTarget;
    var id     = btn.getAttribute('data-id');
    var tipo   = btn.getAttribute('data-tipo');
    var label  = btn.getAttribute('data-label');
    document.getElementById('modalEliminarLabel2').textContent = label;
    document.getElementById('formEliminar').action =
        '<?= site_url("actuacion") ?>/' + tipo + '/eliminar/' + id;
});
</script>
<?php endif; ?>
