<?php
/**
 * Vista: Usuarios — formulario nuevo/editar rol JUSEA
 * Variables: $usuario (null=nuevo), $roles, $esNuevo
 */
$titulo = $esNuevo ? 'Agregar Acceso JUSEA' : 'Editar Acceso JUSEA';
$rolesLabels = [
    'admin'    => 'Administrador',
    'jefe'     => 'Jefe División',
    'operador' => 'Operador',
    'consulta' => 'Consulta',
];
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= site_url('usuarios') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem"><?= $titulo ?></h2>
        <p class="text-muted mb-0 small">
            <?= $esNuevo
                ? 'Buscá al integrante por apellido o DNI y asignale un nivel de acceso en JUSEA.'
                : 'Modificar el nivel de acceso al sistema JUSEA.' ?>
        </p>
    </div>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <strong><i class="bi bi-exclamation-triangle me-1"></i>Errores:</strong>
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
      action="<?= $esNuevo ? site_url('usuarios/guardar') : site_url('usuarios/actualizar/' . $usuario->Id) ?>"
      id="formUsuario">
    <?= csrf_field() ?>

    <?php if ($esNuevo): ?>

    <!-- ── Búsqueda por apellido / DNI ──────────────────────────── -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            <i class="bi bi-search me-1" style="color:var(--ea-blue)"></i>
            Buscar integrante <span class="text-danger">*</span>
        </label>
        <div class="position-relative">
            <input type="text" id="inputBuscar" class="form-control"
                   placeholder="Apellido o DNI..." autocomplete="off"
                   minlength="2">
            <!-- Dropdown de resultados -->
            <div id="dropdownResultados"
                 class="position-absolute w-100 bg-white border rounded shadow-sm d-none"
                 style="z-index:1050;max-height:280px;overflow-y:auto;top:calc(100% + 2px)">
            </div>
        </div>
        <div id="feedbackBuscar" class="form-text mt-1"></div>
    </div>

    <!-- Tarjeta del usuario seleccionado (oculta hasta selección) -->
    <div id="cardSeleccionado" class="alert alert-light border mb-3 d-none" style="border-color:#c8d9f0!important">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="fw-bold" id="selNombreCompleto"></div>
                <div class="small text-muted">
                    DNI: <span id="selDni" class="font-monospace"></span>
                    &nbsp;·&nbsp;
                    <span id="selGrado"></span>
                    &nbsp;·&nbsp;
                    <span id="selArma"></span>
                </div>
                <div id="selAdvertencia" class="small mt-1 text-warning d-none">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Este integrante ya tiene acceso JUSEA con rol <strong id="selRolActual"></strong>.
                    Al guardar se actualizará su rol.
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="btnLimpiar" title="Cambiar selección">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- Campo oculto con el Id del usuario seleccionado -->
    <input type="hidden" name="user_id" id="hiddenUserId">

    <?php else: ?>

    <!-- Info del usuario (solo lectura en edición) -->
    <div class="alert alert-light border mb-3" style="border-color:#c8d9f0!important">
        <div class="fw-bold">
            <?= esc(trim(($usuario->Apellido ?? '') . ', ' . ($usuario->Nombre ?? '')) ?: $usuario->UserName) ?>
        </div>
        <div class="small text-muted">
            DNI: <span class="font-monospace"><?= esc($usuario->DNI ?? '—') ?></span>
            &nbsp;·&nbsp; <?= esc($usuario->GRADO ?? '') ?>
        </div>
    </div>

    <?php endif; ?>

    <!-- ── Nivel de acceso ───────────────────────────────────────── -->
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
        <button type="submit" class="btn btn-ea-blue" id="btnGuardar"
                <?= $esNuevo ? 'disabled' : '' ?>>
            <i class="bi bi-floppy-fill me-1"></i>
            <?= $esNuevo ? 'Otorgar Acceso' : 'Guardar Cambios' ?>
        </button>
        <a href="<?= site_url('usuarios') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>

    <p class="text-muted small mt-3 mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Las contraseñas se gestionan desde la Intranet del Ejército.
    </p>

</form>
</div>
</div>
</div>
</div>

<script>
// ── Descripción del rol ──────────────────────────────────────────────────
function actualizarDescRol() {
    const sel  = document.getElementById('selectRol');
    const desc = sel.options[sel.selectedIndex]?.dataset.desc || '';
    document.getElementById('descRol').textContent = desc;
}
actualizarDescRol();

<?php if ($esNuevo): ?>
// ── Autocompletado de integrante ────────────────────────────────────────
(function () {
    const input      = document.getElementById('inputBuscar');
    const dropdown   = document.getElementById('dropdownResultados');
    const feedback   = document.getElementById('feedbackBuscar');
    const card       = document.getElementById('cardSeleccionado');
    const hiddenId   = document.getElementById('hiddenUserId');
    const btnGuardar = document.getElementById('btnGuardar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    let timer = null;

    function setFeedback(msg, tipo) {
        feedback.textContent = msg;
        feedback.className   = 'form-text mt-1 text-' + (tipo || 'muted');
    }

    function cerrarDropdown() {
        dropdown.classList.add('d-none');
        dropdown.innerHTML = '';
    }

    function seleccionar(item) {
        hiddenId.value = item.id;

        document.getElementById('selNombreCompleto').textContent =
            ((item.apellido || '') + ', ' + (item.nombre || '')).trim().replace(/^,\s*/, '');
        document.getElementById('selDni').textContent   = item.dni   || '—';
        document.getElementById('selGrado').textContent = item.grado || '';
        document.getElementById('selArma').textContent  = item.arma  || '';

        const adv = document.getElementById('selAdvertencia');
        if (item.rol_jusea) {
            document.getElementById('selRolActual').textContent = item.rol_jusea;
            adv.classList.remove('d-none');
        } else {
            adv.classList.add('d-none');
        }

        card.classList.remove('d-none');
        input.value = '';
        input.style.display = 'none';
        btnGuardar.disabled = false;
        cerrarDropdown();
        setFeedback('', 'muted');
    }

    function limpiar() {
        hiddenId.value = '';
        card.classList.add('d-none');
        input.style.display = '';
        input.value = '';
        input.focus();
        btnGuardar.disabled = true;
    }

    btnLimpiar.addEventListener('click', limpiar);

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = input.value.trim();

        if (q.length < 2) {
            cerrarDropdown();
            setFeedback('', 'muted');
            return;
        }

        setFeedback('Buscando…', 'muted');

        timer = setTimeout(function () {
            fetch('<?= site_url('api/usuarios/buscar') ?>?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(function (lista) {
                dropdown.innerHTML = '';

                if (!lista.length) {
                    setFeedback('No se encontraron integrantes.', 'warning');
                    cerrarDropdown();
                    return;
                }

                setFeedback(lista.length + ' resultado(s). Seleccioná uno.', 'muted');

                lista.forEach(function (item) {
                    const li = document.createElement('div');
                    li.className = 'px-3 py-2 border-bottom cursor-pointer';
                    li.style.cursor = 'pointer';

                    const nombre = ((item.apellido || '') + ', ' + (item.nombre || '')).trim().replace(/^,\s*/, '') || '(sin nombre)';
                    const badge  = item.rol_jusea
                        ? `<span class="badge bg-warning text-dark ms-1 small">Ya en JUSEA: ${item.rol_jusea}</span>`
                        : '';

                    li.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">${escHtml(nombre)}</span>${badge}
                                <div class="small text-muted">
                                    DNI: <span class="font-monospace">${escHtml(item.dni)}</span>
                                    &nbsp;·&nbsp; ${escHtml(item.grado || '')}
                                    ${item.arma ? '&nbsp;·&nbsp; ' + escHtml(item.arma) : ''}
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted small"></i>
                        </div>`;

                    li.addEventListener('mouseenter', () => li.classList.add('bg-light'));
                    li.addEventListener('mouseleave', () => li.classList.remove('bg-light'));
                    li.addEventListener('click',      () => seleccionar(item));
                    dropdown.appendChild(li);
                });

                dropdown.classList.remove('d-none');
            })
            .catch(function () {
                setFeedback('Error de comunicación. Intente nuevamente.', 'danger');
                cerrarDropdown();
            });
        }, 300);
    });

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            cerrarDropdown();
        }
    });

    function escHtml(str) {
        return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
<?php endif; ?>
</script>
