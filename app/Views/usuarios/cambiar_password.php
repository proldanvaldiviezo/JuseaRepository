<?php /** Vista: cambiar contraseña — Variable: $usuario */ ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= site_url('usuarios') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem">Cambiar Contraseña</h2>
        <p class="text-muted mb-0 small"><?= esc($usuario->nombre_completo) ?> — <code><?= esc($usuario->username) ?></code></p>
    </div>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0 small">
        <?php foreach ((array)session()->getFlashdata('errors') as $e): ?>
        <li><?= esc($e) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row justify-content-center">
<div class="col-md-6 col-lg-4">
<div class="card border-0 shadow-sm">
<div class="card-body p-4">
<form method="POST" action="<?= site_url('usuarios/cambiar-password/' . $usuario->id) ?>">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label fw-semibold">Nueva contraseña <span class="text-danger">*</span></label>
        <input type="password" name="password" class="form-control"
               placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
    </div>
    <div class="mb-4">
        <label class="form-label fw-semibold">Confirmar <span class="text-danger">*</span></label>
        <input type="password" name="password_confirm" class="form-control"
               placeholder="Repita la contraseña" autocomplete="new-password" required>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-ea-blue">
            <i class="bi bi-key-fill me-1"></i> Actualizar Contraseña
        </button>
        <a href="<?= site_url('usuarios') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
</div>
</div>
</div>
</div>
