<?php
/**
 * Vista: Usuarios — formulario nuevo/editar rol JUSEA
 * Variables: $usuario (null=nuevo), $roles, $esNuevo
 */
$titulo = $esNuevo ? 'Agregar Acceso JUSEA' : 'Editar Rol JUSEA';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= site_url('usuarios') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem"><?= $titulo ?></h2>
        <p class="text-muted mb-0 small">
            <?= $esNuevo
                ? 'Busca un usuario existente del sistema Partes y asígnale un rol en JUSEA.'
                : 'Modificar el nivel de acceso al sistema JUSEA.' ?>
        </p>
    </div>
</div>

<?php if (session()->getFlashdata('errors') || !empty(session()->getFlashdata('errors'))): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <strong><i class="bi bi-exclamation-triangle me-1"></i>Errores de validación:</strong>
    <ul class="mb-0 mt-1 small">
        <?php foreach ((array)session()->getFlashdata('errors') as $e): ?>
        <li><?= esc($e) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show py-2">
    <i class="bi bi-exclamation-triangle me-1"></i> <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row justify-content-center">
<div class="col-md-8 col-lg-6">
<div class="card border-0 shadow-sm">
<div class="card-body p-4">

<form method="POST"
      action="<?= $esNuevo ? site_url('usuarios/guardar') : site_url('usuarios/actualizar/' . $usuario->Id) ?>">
    <?= csrf_field() ?>

    <?php if ($esNuevo): ?>
    <!-- Username (búsqueda en AspNetUsers) -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            <i class="bi bi-person-badge me-1" style="color:var(--ea-blue)"></i>
            Nombre de usuario (sistema Partes) <span class="text-danger">*</span>
        </label>
        <input type="text" name="username" class="form-control"
               value="<?= esc(old('username')) ?>"
               placeholder="ej: gonzalez.m"
               autocomplete="username" required>
        <div class="form-text">El usuario debe existir en el sistema Partes. Las contraseñas se gestionan allí.</div>
    </div>
    <?php else: ?>
    <!-- Info del usuario (solo lectura) -->
    <div class="mb-3">
        <label class="form-label fw-semibold text-muted">Usuario</label>
        <div class="form-control bg-light font-monospace"><?= esc($usuario->UserName ?? '') ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold text-muted">Nombre y Apellido</label>
        <div class="form-control bg-light">
            <?= esc(trim(($usuario->Apellido ?? '') . ', ' . ($usuario->Nombre ?? '')) ?: '—') ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Rol -->
    <div class="mb-4">
        <label class="form-label fw-semibold">
            <i class="bi bi-shield-fill me-1" style="color:var(--ea-blue)"></i>
            Nivel de acceso JUSEA <span class="text-danger">*</span>
        </label>
        <select name="rol" class="form-select" id="selectRol" onchange="actualizarDescRol()" required>
            <?php foreach ($roles as $key => $r): ?>
            <option value="<?= $key ?>"
                    data-desc="<?= esc($r['desc']) ?>"
                    <?= (old('rol', $usuario->rol ?? 'operador') === $key) ? 'selected' : '' ?>>
                <?= $r['label'] ?>
            </option>
            <?php endforeach; ?>
        </select>
        <div id="descRol" class="form-text mt-1"></div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-ea-blue">
            <i class="bi bi-floppy-fill me-1"></i>
            <?= $esNuevo ? 'Otorgar Acceso' : 'Guardar Cambios' ?>
        </button>
        <a href="<?= site_url('usuarios') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>

</form>
</div>
</div>
</div>
</div>

<script>
function actualizarDescRol() {
    const sel  = document.getElementById('selectRol');
    const desc = sel.options[sel.selectedIndex].dataset.desc || '';
    document.getElementById('descRol').textContent = desc;
}
actualizarDescRol();
</script>
