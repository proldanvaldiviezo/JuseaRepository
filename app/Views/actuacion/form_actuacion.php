<?php
/**
 * Vista genérica de formulario para Actuaciones Administrativas.
 * Variables recibidas:
 *   $tipo          'bienes' | 'accidente'
 *   $meta          array  (titulo_corto, titulo_largo, icono, color)
 *   $secciones     array  (definición de secciones y campos)
 *   $datos         array  (valores a repoblar, vacío en nueva actuación)
 *   $solo_lectura  bool   (true = modo vista, false = modo edición)
 *   $actuacion_id  int    (solo en modo lectura)
 */

use App\Config\ActuacionesCampos;

$solo_lectura  = $solo_lectura  ?? false;
$actuacion_id  = $actuacion_id  ?? null;
$datos         = $datos         ?? [];
$iconoColor    = $meta['color'] ?? 'secondary';
$accion        = $solo_lectura
    ? '#'
    : site_url("actuacion/{$tipo}/guardar");
?>

<!-- Encabezado del módulo -->
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:52px;height:52px;background:rgba(26,58,107,.12);">
        <i class="bi <?= esc($meta['icono']) ?> fs-4" style="color:var(--ea-blue)"></i>
    </div>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem;"><?= esc($meta['titulo_largo']) ?></h2>
        <p class="text-muted mb-0 small">
            <?= $solo_lectura ? 'Vista de actuación registrada' : 'Complete todos los campos y guarde la actuación' ?>
        </p>
    </div>
</div>

<?php if (!$solo_lectura): ?>
<form action="<?= $accion ?>" method="post" id="formActuacion" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="_tipo" value="<?= esc($tipo) ?>">
<?php endif; ?>

<!-- Acordeón de secciones -->
<div class="accordion" id="acordeonActuacion">
<?php foreach ($secciones as $secId => $seccion):
    $idx       = substr($secId, 1);   // 1, 2, 3…
    $collapseId = "collapse_{$tipo}_{$secId}";
    $isFirst   = ($secId === 's1');
?>
    <div class="accordion-item mb-3 border shadow-sm rounded">
        <h2 class="accordion-header">
            <button class="accordion-button <?= $isFirst ? '' : 'collapsed' ?> fw-semibold"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#<?= $collapseId ?>"
                    aria-expanded="<?= $isFirst ? 'true' : 'false' ?>">
                <span class="badge me-2"
                      style="background:var(--ea-blue);font-size:.7rem;min-width:26px;">
                    <?= $idx ?>
                </span>
                <i class="bi <?= esc($seccion['icono'] ?? 'bi-list') ?> me-2 text-muted"></i>
                <?= esc($seccion['titulo']) ?>
            </button>
        </h2>

        <div id="<?= $collapseId ?>"
             class="accordion-collapse collapse <?= $isFirst ? 'show' : '' ?>"
             data-bs-parent="#acordeonActuacion">
            <div class="accordion-body py-4 px-4">

                <?php if (!empty($seccion['subtitulos'])): ?>
                <!-- Indicador de sub-secciones (solo visual) -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ($seccion['subtitulos'] as $i => $sub): ?>
                    <span class="badge rounded-pill bg-light text-dark border"
                          style="font-size:.75rem;">
                        <?= esc($sub) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="row g-4">
                <?php foreach ($seccion['campos'] as $nombre => $campo):
                    $valorGuardado = $datos[$nombre] ?? '';
                    $label         = esc($campo['label']);
                    $type          = $campo['type'];
                    $placeholder   = esc($campo['placeholder'] ?? '');
                    $disabled      = $solo_lectura ? 'disabled' : '';
                    $colClass      = in_array($type, ['textarea']) ? 'col-12' : 'col-md-4 col-sm-6';
                ?>
                    <div class="<?= $colClass ?>">
                        <label class="form-label fw-semibold mb-2" style="font-size:.95rem;"><?= $label ?></label>

                        <?php if ($type === 'select'):
                            $opciones = ActuacionesCampos::resolverOpciones($campo['options'] ?? '');
                        ?>
                        <select name="<?= esc($nombre) ?>"
                                class="form-select"
                                <?= $disabled ?>>
                            <option value="">— Seleccione —</option>
                            <?php foreach ($opciones as $k => $v):
                                $key = is_int($k) ? $v : $k;
                                $val = is_int($k) ? $v : $v;
                                $sel = ((string)$valorGuardado === (string)$key) ? 'selected' : '';
                            ?>
                            <option value="<?= esc($key) ?>" <?= $sel ?>><?= esc($val) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <?php elseif ($type === 'textarea'): ?>
                        <textarea name="<?= esc($nombre) ?>"
                                  class="form-control"
                                  rows="4"
                                  placeholder="<?= $placeholder ?>"
                                  <?= $disabled ?>><?= esc($valorGuardado) ?></textarea>

                        <?php elseif ($type === 'number'): ?>
                        <input type="number"
                               name="<?= esc($nombre) ?>"
                               class="form-control"
                               value="<?= esc($valorGuardado) ?>"
                               placeholder="<?= $placeholder ?>"
                               <?= $disabled ?>>

                        <?php else: /* text */ ?>
                        <input type="text"
                               name="<?= esc($nombre) ?>"
                               class="form-control"
                               value="<?= esc($valorGuardado) ?>"
                               placeholder="<?= $placeholder ?>"
                               <?= $disabled ?>>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
                </div><!-- row -->

            </div><!-- accordion-body -->
        </div><!-- collapse -->
    </div><!-- accordion-item -->
<?php endforeach; ?>
</div><!-- accordion -->

<?php if ($solo_lectura && $actuacion_id): ?>
<!-- ─── Panel de Adjuntos ─────────────────────────────────────────────────── -->
<div class="card border shadow-sm rounded mt-3" id="panelAdjuntos">
    <div class="card-header d-flex align-items-center gap-2 py-3"
         style="background:rgba(26,58,107,.07);">
        <i class="bi bi-paperclip fs-5" style="color:var(--ea-blue)"></i>
        <span class="fw-semibold" style="font-size:.97rem;">Documentación Adjunta</span>
        <span class="badge ms-auto bg-secondary" id="badgeCantAdj">0</span>
    </div>
    <div class="card-body px-4 py-3">
        <p class="text-muted small mb-3">
            Cargue aquí los documentos de respaldo (PDF, JPG, PNG, DOCX — máx. 15 MB por archivo).
            Serán incluidos en la carpeta <code>00_ADJUNTOS</code> del expediente ZIP.
        </p>
        <div class="mb-3">
            <label class="form-label fw-semibold mb-2" style="font-size:.9rem;">Agregar archivo</label>
            <div class="input-group">
                <input type="file" id="inputAdjunto" class="form-control"
                       accept=".pdf,.jpg,.jpeg,.png,.tif,.tiff,.doc,.docx">
                <button class="btn btn-ea-blue" type="button" id="btnSubirAdj">
                    <span id="spinnerAdj" class="spinner-border spinner-border-sm me-1 d-none"></span>
                    <i class="bi bi-upload" id="icoSubirAdj"></i> Subir
                </button>
            </div>
            <div id="msgAdj" class="form-text mt-1"></div>
        </div>
        <ul class="list-group list-group-flush" id="listaAdjuntos">
            <li class="list-group-item text-muted small text-center py-3">
                <i class="bi bi-inbox"></i> No hay archivos adjuntos aún.
            </li>
        </ul>
    </div>
</div>
<!-- ──────────────────────────────────────────────────────────────────────── -->
<?php endif; ?>

<!-- Botones de acción -->
<?php if (!$solo_lectura): ?>
<div class="d-flex justify-content-between mt-4">
    <a href="<?= site_url("actuacion/{$tipo}/historial") ?>" class="btn btn-outline-blue">
        <i class="bi bi-arrow-left"></i> Cancelar
    </a>
    <button type="submit" class="btn btn-lg btn-ea-blue">
        <i class="bi bi-check-circle"></i> Guardar Actuación
    </button>
</div>
</form>
<?php else: ?>
<div class="d-flex justify-content-between align-items-center mt-4">
    <a href="<?= site_url("actuacion/{$tipo}/historial") ?>" class="btn btn-outline-blue">
        <i class="bi bi-arrow-left"></i> Volver al Historial
    </a>
    <?php if ($actuacion_id): ?>
    <a href="<?= site_url("actuacion/{$tipo}/generar/{$actuacion_id}") ?>"
       class="btn btn-ea-blue btn-lg"
       id="btnDescargarExp"
       onclick="document.getElementById('btnDescargarExp').innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Generando documentos…';setTimeout(()=>{document.getElementById('btnDescargarExp').innerHTML='<i class=\'bi bi-file-earmark-zip me-1\'></i>Descargar Expediente (ZIP)'},5000);">
        <i class="bi bi-file-earmark-zip me-1"></i>
        Descargar Expediente (ZIP)
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php
// Variables para JS
$_actTipo = esc($tipo ?? '');
$_actId   = (int)($actuacion_id ?? 0);
$_esAdm   = in_array(session()->get('usuario_rol') ?? '', ['admin','jefe']) ? 'true' : 'false';
$_noEdit  = ($solo_lectura ?? false) ? 'true' : 'false';
// CSRF para fetch
$_csrfName  = csrf_token();
$_csrfValue = csrf_hash();
?>
<script>
/* ============================================================
   BÚSQUEDA DE PERSONAL EN PADRÓN — Actuaciones Administrativas
   Patrón idéntico al módulo de Sanciones.
   Configuración separada por tipo de formulario (bienes / accidente)
   porque los nombres de campo difieren completamente entre ambos.
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {

    if (<?= $_noEdit ?>) return; // Solo aplica en modo edición (formulario nuevo)

    var actTipo     = '<?= $_actTipo ?>';
    var urlDni      = '<?= site_url('api/persona/buscar-dni') ?>';
    var urlApellido = '<?= site_url('api/persona/buscar-apellido') ?>';

    /*
     * Estructura de cada grupo:
     *   anchor : nombre del campo APELLIDO del grupo (para localizar el .row padre)
     *   label  : etiqueta visible en el widget
     *   campos : { nombreCampoEnForm → campoEnBD }
     *
     * BIENES usa nombres semánticos (CAUSANTE_APELLIDO, TESTIGO_APELLIDO…)
     * ACCIDENTE usa variables genéricas (ApeCausanteH1, var2b4, var4b4, var5b4…)
     */
    var GRUPOS_BIENES = [
        {
            anchor: 'CAUSANTE_APELLIDO',
            label : 'Causante',
            campos: {
                'CAUSANTE_APELLIDO'  : 'apellido',
                'CAUSANTE_NOMBRE'    : 'nombre',
                'CAUSANTE_GRADO'     : 'grado',
                'CAUSANTE_DNI'       : 'dni',
                'CAUSANTE_DEPENDENCIA': 'destino_interno'
            }
        },
        {
            anchor: 'INFORMANTE_APELLIDO',
            label : 'Oficial Informante',
            campos: {
                'INFORMANTE_APELLIDO': 'apellido',
                'INFORMANTE_NOMBRE'  : 'nombre',
                'INFORMANTE_GRADO'   : 'grado'
            }
        },
        {
            anchor: 'TESTIGO_APELLIDO',
            label : 'Testigo',
            campos: {
                'TESTIGO_APELLIDO'          : 'apellido',
                'TESTIGO_DNI'               : 'dni',
                'TESTIGO_GRADO'             : 'grado',
                'TESTIGO_CARGO'             : 'cargo',
                'TESTIGO_PROFESION'         : 'cargo',
                'TESTIGO_SITUACION_REVISTA' : 'destino_interno'
            }
        }
    ];

    var GRUPOS_ACCIDENTE = [
        {
            anchor: 'ApeCausanteH1',
            label : 'Causante',
            campos: {
                'ApeCausanteH1'  : 'apellido',
                'NomCausanteH1'  : 'nombre',
                'GradCausanteH1' : 'grado',
                'ArmaCausanteH1' : 'arma_especialidad',
                'var1b7'         : 'dni'
            }
        },
        {
            anchor: 'var2b4',
            label : 'Oficial Informante',
            campos: {
                'var2b4': 'apellido',
                'var2b3': 'nombre',
                'var2b1': 'grado',
                'var2b2': 'arma_especialidad'
            }
        },
        {
            anchor: 'var4b4',
            label : 'Testigo 1',
            campos: {
                'var4b4': 'apellido',
                'var4b3': 'nombre',
                'var4b1': 'grado',
                'var4b2': 'arma_especialidad',
                'var4b6': 'dni'
            }
        },
        {
            anchor: 'var5b4',
            label : 'Testigo 2',
            campos: {
                'var5b4': 'apellido',
                'var5b3': 'nombre',
                'var5b1': 'grado',
                'var5b2': 'arma_especialidad',
                'var5b6': 'dni'
            }
        }
    ];

    var GRUPOS = (actTipo === 'accidente') ? GRUPOS_ACCIDENTE : GRUPOS_BIENES;

    function getField(name) {
        return document.querySelector('[name="' + name + '"]');
    }

    // Rellena los campos reales del formulario con los datos del padrón
    function cargarPersona(camposMap, data) {
        Object.keys(camposMap).forEach(function(fieldName) {
            var bdField = camposMap[fieldName];
            var el = getField(fieldName);
            if (!el) return;
            el.value = data[bdField] || '';
            el.dispatchEvent(new Event('change'));
        });
    }

    // ── Para cada grupo, inyectar widget de búsqueda ──────────────────────
    GRUPOS.forEach(function(g) {
        var anchorField = getField(g.anchor);
        if (!anchorField) return; // Este grupo no existe en este formulario

        // Buscar el contenedor .row.g-4 que rodea los campos del grupo
        var colDiv = anchorField.closest('[class*="col-"]');
        if (!colDiv) colDiv = anchorField.parentElement;
        var rowDiv = colDiv ? colDiv.parentElement : null;
        if (!rowDiv) return;

        // Evitar duplicados si el DOM se re-procesa
        if (rowDiv.querySelector('[data-srch-widget]')) return;

        // ID únicos para los elementos del widget
        var uid = g.anchor.replace(/[^a-zA-Z0-9]/g, '');
        var idBusqApell  = '_srch_apell_' + uid;
        var idBtnApell   = '_btn_apell_'  + uid;
        var idListaApell = '_lista_'      + uid;
        var idDniInput   = '_srch_dni_'   + uid;
        var idBtnDni     = '_btn_dni_'    + uid;
        var idOk         = '_ok_'         + uid;

        // Crear el col-12 con el widget
        var widget = document.createElement('div');
        widget.className = 'col-12 mb-1';
        widget.setAttribute('data-srch-widget', '1');
        widget.innerHTML =
            '<div class="border rounded p-2 d-flex flex-wrap align-items-center gap-2"'
            + ' style="background:rgba(26,58,107,.05);">'
            + '<span class="fw-semibold small text-muted">'
            + '<i class="bi bi-search me-1"></i>Buscar ' + g.label + ':</span>'
            // Buscar por apellido
            + '<div class="input-group input-group-sm" style="max-width:260px;">'
            + '<input type="text" class="form-control" id="' + idBusqApell + '"'
            + ' placeholder="Apellido..." autocomplete="off">'
            + '<button type="button" class="btn btn-outline-secondary" id="' + idBtnApell + '"'
            + ' title="Buscar por apellido"><i class="bi bi-search"></i></button>'
            + '</div>'
            // Buscar por DNI
            + '<div class="input-group input-group-sm" style="max-width:200px;">'
            + '<input type="text" class="form-control" id="' + idDniInput + '"'
            + ' placeholder="DNI..." autocomplete="off" inputmode="numeric">'
            + '<button type="button" class="btn btn-outline-secondary" id="' + idBtnDni + '"'
            + ' title="Buscar por DNI">DNI <i class="bi bi-arrow-right"></i></button>'
            + '</div>'
            // Indicador OK
            + '<span class="text-success small" id="' + idOk + '" style="display:none;">'
            + '<i class="bi bi-check-circle"></i> Datos cargados</span>'
            + '</div>'
            // Lista de resultados (apellido)
            + '<div id="' + idListaApell + '" class="list-group mt-1"'
            + ' style="display:none;max-height:200px;overflow-y:auto;z-index:1050;position:relative;"></div>';

        // Insertar al comienzo del row (antes del primer col-)
        rowDiv.insertBefore(widget, rowDiv.firstChild);

        var inpApell   = document.getElementById(idBusqApell);
        var btnApell   = document.getElementById(idBtnApell);
        var listaApell = document.getElementById(idListaApell);
        var inpDni     = document.getElementById(idDniInput);
        var btnDni     = document.getElementById(idBtnDni);
        var okSpan     = document.getElementById(idOk);

        function mostrarOk() {
            okSpan.style.display = '';
            setTimeout(function() { okSpan.style.display = 'none'; }, 3000);
        }

        // ── Buscar por apellido ────────────────────────────────────────────
        function buscarApellido() {
            var q = inpApell.value.trim();
            if (q.length < 2) { listaApell.style.display = 'none'; return; }
            fetch(urlApellido + '?q=' + encodeURIComponent(q))
                .then(function(r) { return r.json(); })
                .then(function(resultados) {
                    listaApell.innerHTML = '';
                    if (!resultados.length) {
                        listaApell.innerHTML = '<span class="list-group-item text-muted small">Sin resultados en el padrón</span>';
                        listaApell.style.display = 'block';
                        return;
                    }
                    resultados.forEach(function(p) {
                        var item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action py-1 px-3';
                        var grado = p.grado ? p.grado + ' ' : '';
                        item.innerHTML = '<strong>' + grado + p.apellido + '</strong>'
                            + (p.nombre ? ', ' + p.nombre : '')
                            + ' <small class="text-muted">(DNI: ' + p.dni + ')</small>';
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            cargarPersona(g.campos, p);
                            listaApell.style.display = 'none';
                            inpApell.value = '';
                            mostrarOk();
                        });
                        listaApell.appendChild(item);
                    });
                    listaApell.style.display = 'block';
                })
                .catch(function() { listaApell.style.display = 'none'; });
        }

        btnApell.addEventListener('click', buscarApellido);
        inpApell.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); buscarApellido(); }
            else if (this.value.trim().length >= 3) { buscarApellido(); }
        });

        // Cerrar lista al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!listaApell.contains(e.target) && e.target !== inpApell && e.target !== btnApell) {
                listaApell.style.display = 'none';
            }
        });

        // ── Buscar por DNI ─────────────────────────────────────────────────
        function buscarDni() {
            var dni = inpDni.value.trim();
            if (dni.length < 7) { alert('Ingrese un DNI válido (mínimo 7 dígitos)'); return; }
            fetch(urlDni + '?dni=' + encodeURIComponent(dni))
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.found && res.data) {
                        cargarPersona(g.campos, res.data);
                        inpDni.value = '';
                        mostrarOk();
                    } else {
                        alert('DNI no encontrado en el padrón. Complete los campos manualmente.');
                    }
                })
                .catch(function() { alert('Error de conexión'); });
        }

        btnDni.addEventListener('click', buscarDni);
        inpDni.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); buscarDni(); }
        });
    });

});

/* ============================================================
   PANEL DE ADJUNTOS — subida y gestión de archivos
   ============================================================ */
(function () {
    'use strict';

    const soloLect = <?= $_noEdit ?>;
    const actId    = <?= $_actId ?>;
    const actTipo  = '<?= $_actTipo ?>';
    const esAdmin  = <?= $_esAdm ?>;
    const CSRF_NAME  = '<?= $_csrfName ?>';
    const CSRF_VALUE = '<?= $_csrfValue ?>';

    if (!soloLect || !actId) return;

    const BASE    = window.location.origin;
    const urlSub  = BASE + '/actuacion/' + actTipo + '/adjunto/subir/'    + actId;
    const urlList = BASE + '/actuacion/' + actTipo + '/adjunto/listar/'   + actId;
    const urlElim = BASE + '/actuacion/' + actTipo + '/adjunto/eliminar/' + actId;

    // CI4 renueva el hash CSRF en cada respuesta JSON — usamos el inicial para el primer request
    let csrfVal = CSRF_VALUE;

    function cargarLista() {
        fetch(urlList)
            .then(r => r.json())
            .then(res => {
                const ul    = document.getElementById('listaAdjuntos');
                const badge = document.getElementById('badgeCantAdj');
                if (!ul) return;
                const files = res.files ?? [];
                badge.textContent = files.length;
                ul.innerHTML = '';

                if (!files.length) {
                    ul.innerHTML = '<li class="list-group-item text-muted small text-center py-3">'
                                 + '<i class="bi bi-inbox"></i> No hay archivos adjuntos.</li>';
                    return;
                }

                files.forEach(f => {
                    const ext = f.name.split('.').pop().toLowerCase();
                    const ico = ext === 'pdf' ? 'bi-file-earmark-pdf text-danger'
                              : ['jpg','jpeg','png','tif'].includes(ext) ? 'bi-file-earmark-image text-success'
                              : 'bi-file-earmark-text text-primary';
                    const li  = document.createElement('li');
                    li.className = 'list-group-item d-flex align-items-center gap-2 py-2';
                    const btnEl = esAdmin
                        ? '<button class="btn btn-sm btn-outline-danger ms-auto py-0 px-2 btn-elim"'
                          + ' data-n="' + f.name + '" title="Eliminar"><i class="bi bi-trash"></i></button>'
                        : '<span class="ms-auto"></span>';
                    li.innerHTML = '<i class="bi ' + ico + ' fs-5"></i>'
                        + '<span class="small fw-semibold text-truncate" style="max-width:260px;">' + f.name + '</span>'
                        + '<span class="badge bg-light text-dark border small ms-1">' + f.size + '</span>'
                        + btnEl;
                    ul.appendChild(li);
                });

                ul.querySelectorAll('.btn-elim').forEach(btn => {
                    btn.addEventListener('click', () => {
                        if (!confirm('¿Eliminar "' + btn.dataset.n + '"?')) return;
                        const body = {};
                        body[CSRF_NAME] = csrfVal;
                        body['nombre']  = btn.dataset.n;
                        fetch(urlElim, {
                            method  : 'POST',
                            headers : { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest' },
                            body    : JSON.stringify(body),
                        })
                        .then(r => r.json())
                        .then(r2 => {
                            if (r2.ok) cargarLista();
                            else alert('Error: ' + r2.error);
                        })
                        .catch(() => alert('Error de red.'));
                    });
                });
            }).catch(() => {});
    }

    const btnSub = document.getElementById('btnSubirAdj');
    const inpAdj = document.getElementById('inputAdjunto');
    const msgAdj = document.getElementById('msgAdj');
    const spin   = document.getElementById('spinnerAdj');
    const icoUp  = document.getElementById('icoSubirAdj');

    if (btnSub && inpAdj) {
        btnSub.addEventListener('click', () => {
            const file = inpAdj.files[0];
            if (!file) { msgAdj.textContent = 'Seleccione un archivo primero.'; return; }

            const fd = new FormData();
            fd.append('adjunto', file);
            fd.append(CSRF_NAME, csrfVal);

            btnSub.disabled = true;
            spin.classList.remove('d-none');
            icoUp.classList.add('d-none');
            msgAdj.textContent = 'Subiendo…';

            fetch(urlSub, {
                method  : 'POST',
                headers : { 'X-Requested-With':'XMLHttpRequest' },
                body    : fd,
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    msgAdj.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Subido correctamente.</span>';
                    inpAdj.value = '';
                    cargarLista();
                } else {
                    msgAdj.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle"></i> ' + res.error + '</span>';
                }
            })
            .catch(() => {
                msgAdj.innerHTML = '<span class="text-danger">Error de red.</span>';
            })
            .finally(() => {
                btnSub.disabled = false;
                spin.classList.add('d-none');
                icoUp.classList.remove('d-none');
            });
        });
    }

    cargarLista();
})();
</script>
