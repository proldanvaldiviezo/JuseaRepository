<h2 class="mb-4"><i class="bi bi-gear"></i> Configurar Encabezado Institucional</h2>

<div class="card" style="max-width: 700px;">
    <div class="card-header bg-light">
        <strong>Datos del encabezado</strong>
        <small class="text-muted d-block">Estos datos se utilizan en todos los documentos generados por el sistema.</small>
    </div>
    <div class="card-body">
        <form action="<?= site_url('encabezado/actualizar') ?>" method="post" class="needs-validation" novalidate>
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="anio" class="form-label fw-semibold">Año <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="anio" name="anio"
                       value="<?= esc($encabezado->anio ?? date('Y')) ?>"
                       required maxlength="4" minlength="4" data-solo-numeros
                       placeholder="Ej: 2026">
                <div class="invalid-feedback">Ingrese un año válido (4 dígitos).</div>
            </div>

            <div class="mb-3">
                <label for="membrete" class="form-label fw-semibold">Membrete <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="membrete" name="membrete"
                       value="<?= esc($encabezado->membrete ?? '') ?>"
                       required minlength="3" maxlength="255"
                       placeholder="Ej: Año del Bicentenario de la Independencia">
                <div class="invalid-feedback">Ingrese el membrete (min. 3 caracteres).</div>
            </div>

            <div class="mb-4">
                <label for="unidad" class="form-label fw-semibold">Unidad <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="unidad" name="unidad"
                       value="<?= esc($encabezado->unidad ?? '') ?>"
                       required minlength="3" maxlength="255"
                       placeholder="Ej: Colegio Militar de la Nacion">
                <div class="invalid-feedback">Ingrese la unidad (min. 3 caracteres).</div>
            </div>

            <button type="submit" class="btn btn-ea">
                <i class="bi bi-check-circle"></i> Guardar Cambios
            </button>
        </form>
    </div>
</div>
