<?php
/**
 * Vista: Gestión de Usuarios — lista
 * Variables: $usuarios, $roles, $stats, $rolActual, $idActual
 */
?>
<!-- Encabezado -->
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:52px;height:52px;background:rgba(26,58,107,.12)">
        <i class="bi bi-people-fill fs-4" style="color:var(--ea-blue)"></i>
    </div>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem">Gestión de Usuarios</h2>
        <p class="text-muted mb-0 small">Administración de cuentas y niveles de acceso al sistema JUSEA CMN</p>
    </div>
</div>

<!-- Stats por rol -->
<div class="row g-3 mb-4">
<?php
$statDef = [
    'admin'    => ['color' => '#dc3545', 'icon' => 'bi-shield-fill-check'],
    'jefe'     => ['color' => '#0d6efd', 'icon' => 'bi-award-fill'],
    'operador' => ['color' => '#198754', 'icon' => 'bi-pencil-fill'],
    'consulta' => ['color' => '#6c757d', 'icon' => 'bi-eye-fill'],
    'inactivos'=> ['color' => '#adb5bd', 'icon' => 'bi-person-slash'],
];
foreach ($statDef as $key => $sd):
    $label = $key === 'inactivos' ? 'Inactivos' : ($roles[$key]['label'] ?? ucfirst($key));
    $val   = $stats[$key] ?? 0;
?>
<div class="col-6 col-md" style="min-width:110px">
    <div class="card border-0 shadow-sm text-center h-100"
         style="border-top:3px solid <?= $sd['color'] ?>!important">
        <div class="card-body py-2 px-1">
            <div class="fs-3 fw-bold" style="color:<?= $sd['color'] ?>"><?= $val ?></div>
            <div class="small text-muted"><?= $label ?></div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Alertas flash -->
<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show py-2">
    <i class="bi bi-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show py-2">
    <i class="bi bi-exclamation-triangle me-1"></i> <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Barra de acciones -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small"><i class="bi bi-people"></i> <?= count($usuarios) ?> usuario(s) en el sistema</span>
    <a href="<?= site_url('usuarios/nuevo') ?>" class="btn btn-sm btn-ea-blue">
        <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
    </a>
</div>

<!-- Tabla de usuarios -->
<div class="card border-0 shadow-sm">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-blue table-hover align-middle mb-0">
    <thead>
        <tr>
            <th style="width:40px">#</th>
            <th>Usuario</th>
            <th>Nombre Completo</th>
            <th style="width:140px">Rol</th>
            <th class="d-none d-md-table-cell">Email</th>
            <th class="d-none d-lg-table-cell">Último Acceso</th>
            <th style="width:60px">Estado</th>
            <th style="width:150px">Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($usuarios)): ?>
    <tr><td colspan="8" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
    <?php else: ?>
    <?php foreach ($usuarios as $u):
        $ri    = $roles[$u->rol] ?? ['label' => $u->rol, 'badge' => 'bg-secondary'];
        $esMio = ((int)$u->id === (int)$idActual);
    ?>
    <tr class="<?= !$u->activo ? 'table-secondary opacity-75' : '' ?>">
        <td class="text-muted small"><?= $u->id ?></td>
        <td>
            <span class="fw-semibold font-monospace"><?= esc($u->username) ?></span>
            <?php if ($esMio): ?>
            <span class="badge bg-info text-dark ms-1 small">Yo</span>
            <?php endif; ?>
        </td>
        <td><?= esc($u->nombre_completo) ?></td>
        <td>
            <span class="badge <?= $ri['badge'] ?>"><?= $ri['label'] ?></span>
        </td>
        <td class="small d-none d-md-table-cell text-muted"><?= esc($u->email ?? '—') ?></td>
        <td class="small d-none d-lg-table-cell text-muted">
            <?= $u->ultimo_acceso ? date('d/m/Y H:i', strtotime($u->ultimo_acceso)) : 'Nunca' ?>
        </td>
        <td>
            <?php if ($u->activo): ?>
            <span class="badge bg-success small">Activo</span>
            <?php else: ?>
            <span class="badge bg-danger small">Inactivo</span>
            <?php endif; ?>
        </td>
        <td>
            <!-- Editar -->
            <a href="<?= site_url('usuarios/editar/' . $u->id) ?>"
               class="btn btn-xs btn-outline-secondary me-1" title="Editar datos">
                <i class="bi bi-pencil-fill"></i>
            </a>
            <!-- Cambiar password -->
            <a href="<?= site_url('usuarios/cambiar-password/' . $u->id) ?>"
               class="btn btn-xs btn-outline-primary me-1" title="Cambiar contraseña">
                <i class="bi bi-key-fill"></i>
            </a>
            <?php if (!$esMio): ?>
            <!-- Desactivar / Reactivar -->
            <?php if ($u->activo): ?>
            <button type="button" class="btn btn-xs btn-outline-danger"
                    title="Desactivar usuario"
                    onclick="confirmarBaja(<?= $u->id ?>, '<?= addslashes($u->nombre_completo) ?>', '<?= esc($u->username) ?>')">
                <i class="bi bi-person-dash-fill"></i>
            </button>
            <?php else: ?>
            <form method="POST" action="<?= site_url('usuarios/reactivar/' . $u->id) ?>" style="display:inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-xs btn-outline-success" title="Reactivar usuario">
                    <i class="bi bi-person-check-fill"></i>
                </button>
            </form>
            <?php endif; ?>
            <?php else: ?>
            <span class="text-muted small" title="No puede modificar su propia cuenta desde aquí"><i class="bi bi-lock-fill"></i></span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
</div>
</div>
</div>

<!-- Leyenda de roles -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header py-2" style="background:rgba(26,58,107,.06)">
        <small class="fw-semibold" style="color:var(--ea-blue)">
            <i class="bi bi-info-circle me-1"></i>Niveles de acceso del sistema
        </small>
    </div>
    <div class="card-body py-2">
        <div class="row g-2">
        <?php foreach ($roles as $key => $r): ?>
        <div class="col-md-6 col-lg-3">
            <div class="d-flex align-items-start gap-2 p-2 rounded" style="background:#f8f9fa">
                <span class="badge <?= $r['badge'] ?> mt-1"><?= $r['label'] ?></span>
                <small class="text-muted"><?= $r['desc'] ?></small>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal confirmar baja -->
<div class="modal fade" id="modalBaja" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                    Confirmar desactivación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Desactivar la cuenta de <strong id="modalNombreBaja"></strong>?</p>
                <p class="text-muted small mt-1 mb-0">El usuario <code id="modalUsernameBaja"></code> no podrá ingresar al sistema. Puede reactivarse en cualquier momento.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <form id="formBaja" method="POST" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-person-dash-fill"></i> Desactivar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarBaja(id, nombre, username) {
    document.getElementById('modalNombreBaja').textContent   = nombre;
    document.getElementById('modalUsernameBaja').textContent = username;
    document.getElementById('formBaja').action = '<?= site_url('usuarios/desactivar/') ?>' + id;
    new bootstrap.Modal(document.getElementById('modalBaja')).show();
}
</script>
