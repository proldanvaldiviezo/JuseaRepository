<?php
/**
 * Vista: Usuarios — formulario nuevo/editar
 * Variables: $usuario (null=nuevo), $roles, $esNuevo
 */
$titulo = $esNuevo ? 'Nuevo Usuario' : 'Editar Usuario';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= site_url('usuarios') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem"><?= $titulo ?></h2>
        <p class="text-muted mb-0 small"><?= $esNuevo ? 'Registrar nuevo acceso al sistema' : 'Modificar datos del usuario' ?></p>
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

<div class="row justify-content-center">
<div class="col-md-8 col-lg-6">
<div class="card border-0 shadow-sm">
<div class="card-body p-4">

<form method="POST"
      action="<?= $esNuevo ? site_url('usuarios/guardar') : site_url('usuarios/actualizar/' . $usuario->id) ?>">
    <?= csrf_field() ?>

    <!-- Username -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            <i class="bi bi-person-badge me-1" style="color:var(--ea-blue)"></i>
            Nombre de usuario <span class="text-danger">*</span>
        </label>
        <input type="text" name="username" class="form-control"
               value="<?= esc(old('username', $usuario->username ?? '')) ?>"
               placeholder="ej: gonzalez.m"
               autocomplete="username" required>
        <div class="form-text">Mínimo 3 caracteres. No se puede repetir.</div>
    </div>

    <!-- Nombre completo -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            <i class="bi bi-person-fill me-1" style="color:var(--ea-blue)"></i>
            Nombre completo <span class="text-danger">*</span>
        </label>
        <input type="text" name="nombre_completo" class="form-control"
               value="<?= esc(old('nombre_completo', $usuario->nombre_completo ?? '')) ?>"
               placeholder="ej: MY González Fidani, Martín" required>
    </div>

    <!-- Email -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            <i class="bi bi-envelope-fill me-1" style="color:var(--ea-blue)"></i>
            Correo electrónico
        </label>
        <input type="email" name="email" class="form-control"
               value="<?= esc(old('email', $usuario->email ?? '')) ?>"
               placeholder="ej: gonzalez@ejercito.mil.ar">
    </div>

    <!-- Rol -->
    <div class="mb-4">
        <label class="form-label fw-semibold">
            <i class="bi bi-shield-fill me-1" style="color:var(--ea-blue)"></i>
            Nivel de acceso <span class="text-danger">*</span>
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

    <?php if ($esNuevo): ?>
    <hr class="my-3">
    <p class="text-muted small fw-semibold mb-2"><i class="bi bi-key-fill me-1"></i>Contraseña inicial</p>

    <div class="mb-3">
        <label class="form-label">Contraseña <span class="text-danger">*</span></label>
        <input type="password" name="password" class="form-control"
               placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
    </div>
    <div class="mb-4">
        <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
        <input type="password" name="password_confirm" class="form-control"
               placeholder="Repita la contraseña" autocomplete="new-password" required>
    </div>
    <?php endif; ?>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-ea-blue">
            <i class="bi bi-floppy-fill me-1"></i>
            <?= $esNuevo ? 'Crear Usuario' : 'Guardar Cambios' ?>
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
