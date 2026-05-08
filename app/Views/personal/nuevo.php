<?php
/**
 * Vista: Alta de Personal — búsqueda por DNI en APICPS y confirmación
 */
?>

<!-- Encabezado -->
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:52px;height:52px;background:rgba(26,58,107,.12);">
        <i class="bi bi-person-plus-fill fs-4" style="color:var(--ea-blue)"></i>
    </div>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem;">Alta de Personal</h2>
        <p class="text-muted mb-0 small">Busque al integrante por DNI en la APICPS y confirme el alta en el padrón</p>
    </div>
</div>

<!-- Alertas flash -->
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show py-2">
    <i class="bi bi-exclamation-triangle me-1"></i> <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Búsqueda por DNI -->
<div class="card border-0 shadow-sm mb-4" style="max-width:480px;">
    <div class="card-body">
        <label class="form-label fw-semibold" for="inputDni">DNI del integrante</label>
        <div class="input-group">
            <input type="text" id="inputDni" class="form-control"
                   placeholder="Ej: 32630429" maxlength="10"
                   inputmode="numeric" pattern="[0-9]+" autocomplete="off">
            <button class="btn btn-ea-blue" id="btnBuscar" type="button">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>
        <div id="buscarFeedback" class="form-text mt-1"></div>
    </div>
</div>

<!-- Resultado de la búsqueda (oculto hasta que haya datos) -->
<div id="resultadoCard" class="card border-0 shadow-sm mb-4 d-none" style="max-width:560px;">
    <div class="card-header bg-white py-2 border-bottom">
        <i class="bi bi-person-check me-1" style="color:var(--ea-blue)"></i>
        <strong>Datos encontrados en APICPS</strong>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="small text-muted">DNI</div>
                <div class="fw-semibold font-monospace" id="rDni">—</div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Grado</div>
                <div class="fw-semibold" id="rGrado">—</div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Apellido</div>
                <div class="fw-semibold" id="rApellido">—</div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Nombre</div>
                <div class="fw-semibold" id="rNombre">—</div>
            </div>
            <div class="col-12">
                <div class="small text-muted">Arma / Especialidad</div>
                <div id="rArma">—</div>
            </div>
        </div>

        <!-- Alerta si ya existe -->
        <div id="alertaExiste" class="alert alert-warning py-2 d-none">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Este integrante ya se encuentra en el padrón.
        </div>

        <!-- Formulario de confirmación -->
        <form id="formGuardar" method="POST" action="<?= site_url('personal/guardar') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="dni"      id="hDni">
            <input type="hidden" name="nombre"   id="hNombre">
            <input type="hidden" name="apellido" id="hApellido">
            <input type="hidden" name="grado"    id="hGrado">
            <input type="hidden" name="arma"     id="hArma">
            <input type="hidden" name="display"  id="hDisplay">

            <div class="d-flex gap-2">
                <button type="submit" id="btnGuardar" class="btn btn-success">
                    <i class="bi bi-person-check-fill me-1"></i> Dar de alta en padrón
                </button>
                <a href="<?= site_url('personal') ?>" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputDni    = document.getElementById('inputDni');
    const btnBuscar   = document.getElementById('btnBuscar');
    const feedback    = document.getElementById('buscarFeedback');
    const resultado   = document.getElementById('resultadoCard');
    const alertaExiste = document.getElementById('alertaExiste');
    const btnGuardar  = document.getElementById('btnGuardar');

    function setFeedback(msg, tipo) {
        feedback.textContent = msg;
        feedback.className   = 'form-text mt-1 text-' + (tipo || 'muted');
    }

    function buscar() {
        const dni = inputDni.value.trim();
        if (!/^\d{6,10}$/.test(dni)) {
            setFeedback('Ingrese un DNI válido (solo números, 6 a 10 dígitos).', 'danger');
            resultado.classList.add('d-none');
            return;
        }

        btnBuscar.disabled = true;
        btnBuscar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Buscando…';
        setFeedback('', 'muted');
        resultado.classList.add('d-none');

        fetch('<?= site_url('personal/buscar-apicps') ?>?dni=' + encodeURIComponent(dni), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            btnBuscar.disabled = false;
            btnBuscar.innerHTML = '<i class="bi bi-search"></i> Buscar';

            if (!res.ok) {
                setFeedback(res.msg, 'danger');
                return;
            }

            const d = res.datos;
            document.getElementById('rDni').textContent      = d.dni      || '—';
            document.getElementById('rGrado').textContent    = d.grado    || '—';
            document.getElementById('rApellido').textContent = d.apellido || '—';
            document.getElementById('rNombre').textContent   = d.nombre   || '—';
            document.getElementById('rArma').textContent     = d.arma     || '—';

            document.getElementById('hDni').value      = d.dni      || '';
            document.getElementById('hNombre').value   = d.nombre   || '';
            document.getElementById('hApellido').value = d.apellido || '';
            document.getElementById('hGrado').value    = d.grado    || '';
            document.getElementById('hArma').value     = d.arma     || '';
            document.getElementById('hDisplay').value  = d.display  || '';

            if (res.yaExiste) {
                alertaExiste.classList.remove('d-none');
                btnGuardar.disabled = true;
            } else {
                alertaExiste.classList.add('d-none');
                btnGuardar.disabled = false;
            }

            resultado.classList.remove('d-none');
            setFeedback('Integrante encontrado en APICPS.', 'success');
        })
        .catch(() => {
            btnBuscar.disabled = false;
            btnBuscar.innerHTML = '<i class="bi bi-search"></i> Buscar';
            setFeedback('Error de comunicación. Intente nuevamente.', 'danger');
        });
    }

    btnBuscar.addEventListener('click', buscar);
    inputDni.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); buscar(); }
    });
});
</script>
