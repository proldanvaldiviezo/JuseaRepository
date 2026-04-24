<?php
/**
 * Vista: Padrón de Personal CMN — Listado v2
 * Variables: $personas, $pager, $stats, $q, $tipo, $arma, $armasList,
 *            $activo, $total, $perPage, $vista, $agrupado
 */

$tiposLabel = [
    'cuadro'    => ['label' => 'Cuadro',     'badge' => 'bg-primary',   'icon' => 'bi-shield-fill'],
    'cadete'    => ['label' => 'Cadete',     'badge' => 'bg-warning text-dark', 'icon' => 'bi-person-fill'],
    'instructor'=> ['label' => 'Instructor', 'badge' => 'bg-success',   'icon' => 'bi-mortarboard-fill'],
    'autoridad' => ['label' => 'Autoridad',  'badge' => 'bg-danger',    'icon' => 'bi-star-fill'],
    'civil'     => ['label' => 'Civil',      'badge' => 'bg-secondary', 'icon' => 'bi-person-badge-fill'],
];

$isAdmin   = session()->get('usuario_rol') === 'admin';
$vistaActual = $vista ?? 'tabla';

// Parámetros comunes para preservar filtros en links
$qp = http_build_query(array_filter([
    'q'      => $q,
    'tipo'   => $tipo,
    'arma'   => $arma,
    'activo' => $activo,
], fn($v) => $v !== ''));
?>

<!-- Encabezado -->
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:52px;height:52px;background:rgba(26,58,107,.12);">
        <i class="bi bi-people-fill fs-4" style="color:var(--ea-blue)"></i>
    </div>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem;">Padrón de Personal CMN</h2>
        <p class="text-muted mb-0 small">Base de datos del personal que alimenta los formularios de Sanciones y Actuaciones</p>
    </div>
</div>

<!-- ESTADÍSTICAS -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['val' => $stats['total'],       'label' => 'Total activos', 'color' => 'var(--ea-blue)', 'filtro' => ''],
        ['val' => $stats['cuadros'],     'label' => 'Cuadros',       'color' => '#0d6efd',        'filtro' => 'tipo=cuadro'],
        ['val' => $stats['cadetes'],     'label' => 'Cadetes',       'color' => '#ffc107',        'filtro' => 'tipo=cadete'],
        ['val' => $stats['autoridades'], 'label' => 'Autoridades',   'color' => '#198754',        'filtro' => 'tipo=instructor'],
        ['val' => $stats['civiles'],     'label' => 'Civiles',       'color' => '#6c757d',        'filtro' => 'tipo=civil'],
        ['val' => $stats['inactivos'],   'label' => 'Inactivos',     'color' => '#dc3545',        'filtro' => 'activo=0'],
    ];
    foreach ($statCards as $sc):
        $href = site_url('personal') . ($sc['filtro'] ? '?' . $sc['filtro'] : '');
    ?>
    <div class="col-6 col-md-2">
        <a href="<?= $href ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center h-100" style="border-top:3px solid <?= $sc['color'] ?>!important;cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div class="fs-3 fw-bold" style="color:<?= $sc['color'] ?>"><?= $sc['val'] ?></div>
                    <div class="small text-muted"><?= $sc['label'] ?></div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- FILTROS -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= site_url('personal') ?>" id="formFiltros" class="row g-2 align-items-end">
            <input type="hidden" name="vista" value="<?= esc($vistaActual) ?>">

            <!-- Búsqueda general -->
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1">Buscar</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Apellido, nombre, grado, DNI, cargo..."
                       value="<?= esc($q) ?>" autocomplete="off">
            </div>

            <!-- Tipo -->
            <div class="col-6 col-md-2">
                <label class="form-label form-label-sm mb-1">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos los tipos</option>
                    <?php foreach($tiposLabel as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $tipo === $k ? 'selected' : '' ?>><?= $v['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Arma / Especialidad -->
            <div class="col-6 col-md-2">
                <label class="form-label form-label-sm mb-1">Arma / Especialidad</label>
                <select name="arma" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach($armasList as $a): ?>
                    <option value="<?= esc($a) ?>" <?= $arma === $a ? 'selected' : '' ?>><?= esc($a) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Estado -->
            <div class="col-6 col-md-2">
                <label class="form-label form-label-sm mb-1">Estado</label>
                <select name="activo" class="form-select form-select-sm">
                    <option value="1" <?= $activo === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= $activo === '0' ? 'selected' : '' ?>>Inactivos</option>
                    <option value=""  <?= $activo === ''  ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>

            <!-- Botones filtro -->
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-ea-blue">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="<?= site_url('personal') ?>" class="btn btn-sm btn-outline-secondary ms-1" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- BARRA DE ACCIONES + TOGGLE VISTA -->
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
    <div class="text-muted small">
        <i class="bi bi-list-ul"></i>
        <strong><?= $total ?></strong> registro(s) encontrado(s)
        <?php if ($arma): ?> · Arma: <strong><?= esc($arma) ?></strong><?php endif; ?>
    </div>
    <div class="d-flex gap-2 flex-wrap">

        <!-- Toggle vista -->
        <div class="btn-group btn-group-sm" role="group" title="Forma de listar">
            <a href="<?= site_url('personal?' . ($qp ? $qp . '&' : '') . 'vista=tabla') ?>"
               class="btn <?= $vistaActual === 'tabla' ? 'btn-ea-blue' : 'btn-outline-secondary' ?>"
               title="Vista tabla">
                <i class="bi bi-table"></i>
            </a>
            <a href="<?= site_url('personal?' . ($qp ? $qp . '&' : '') . 'vista=agrupado') ?>"
               class="btn <?= $vistaActual === 'agrupado' ? 'btn-ea-blue' : 'btn-outline-secondary' ?>"
               title="Vista agrupada por tipo">
                <i class="bi bi-collection-fill"></i>
            </a>
        </div>

        <!-- Exportar -->
        <a href="<?= site_url('personal/exportar?' . ($qp ?: 'activo=1')) ?>"
           class="btn btn-sm btn-outline-secondary" title="Exportar lista actual a CSV (Excel)">
            <i class="bi bi-download"></i> Exportar CSV
        </a>

        <?php if ($isAdmin): ?>
        <a href="<?= site_url('personal/importar') ?>"
           class="btn btn-sm btn-outline-warning" title="Importar/actualizar masivo desde CSV">
            <i class="bi bi-upload"></i> Importar CSV
        </a>
        <a href="<?= site_url('personal/nuevo') ?>" class="btn btn-sm btn-ea-blue">
            <i class="bi bi-person-plus-fill"></i> Nuevo
        </a>
        <?php endif; ?>
    </div>
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
<?php if (session()->getFlashdata('import_errors')): ?>
<div class="alert alert-warning alert-dismissible fade show">
    <strong><i class="bi bi-exclamation-triangle"></i> Filas con errores en la importación:</strong>
    <ul class="mb-0 mt-1 small">
        <?php foreach (session()->getFlashdata('import_errors') as $e): ?>
        <li><?= esc($e) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($isAdmin): ?>
<!-- BARRA ACCIÓN MASIVA (visible solo cuando hay checkboxes marcados) -->
<div id="barraAccionMasiva" class="alert mb-2 py-2 d-none"
     style="background:#1a3a6b;color:#fff;border:none;border-radius:6px;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span><i class="bi bi-check2-square me-1"></i>
            <strong id="contSeleccionados">0</strong> integrante(s) seleccionado(s)
        </span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-warning"
                    onclick="ejecutarAccionMasiva('desactivar')">
                <i class="bi bi-person-dash-fill"></i> Desactivar seleccionados
            </button>
            <button type="button" class="btn btn-sm btn-success"
                    onclick="ejecutarAccionMasiva('reactivar')">
                <i class="bi bi-person-check-fill"></i> Reactivar seleccionados
            </button>
            <button type="button" class="btn btn-sm btn-outline-light"
                    onclick="deseleccionarTodo()">
                <i class="bi bi-x"></i> Cancelar
            </button>
        </div>
    </div>
</div>

<!-- Form oculto para acción masiva -->
<form id="formAccionMasiva" method="POST" action="<?= site_url('personal/accion-masiva') ?>" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="accion" id="inputAccion" value="">
    <div id="inputsIds"></div>
</form>
<?php endif; ?>


<?php if ($vistaActual === 'agrupado'): ?>
<!-- ══════════════════════════════════════════════════════════ -->
<!-- VISTA AGRUPADA POR TIPO                                   -->
<!-- ══════════════════════════════════════════════════════════ -->
<?php if (empty($agrupado)): ?>
<div class="text-center text-muted py-5">
    <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
    No se encontraron registros con los filtros actuales.
</div>
<?php else: ?>

<form id="wrapperTablaAgrupada">
<?php foreach ($agrupado as $tipoKey => $grupo): ?>
<?php $tl = $tiposLabel[$tipoKey] ?? ['label' => ucfirst($tipoKey), 'badge' => 'bg-secondary', 'icon' => 'bi-people']; ?>

<div class="card border-0 shadow-sm mb-3">
    <!-- Cabecera colapsable del grupo -->
    <div class="card-header d-flex justify-content-between align-items-center py-2"
         style="cursor:pointer;background:rgba(26,58,107,.07);border-bottom:2px solid var(--ea-blue);"
         data-bs-toggle="collapse" data-bs-target="#grupo-<?= $tipoKey ?>">
        <div class="d-flex align-items-center gap-2">
            <?php if ($isAdmin): ?>
            <input type="checkbox" class="form-check-input check-grupo mt-0"
                   data-grupo="<?= $tipoKey ?>"
                   onclick="event.stopPropagation(); toggleGrupo('<?= $tipoKey ?>', this.checked)"
                   title="Seleccionar todos los <?= $tl['label'] ?>">
            <?php endif; ?>
            <i class="bi <?= $tl['icon'] ?>" style="color:var(--ea-blue)"></i>
            <strong><?= $tl['label'] ?>s</strong>
            <span class="badge <?= $tl['badge'] ?> ms-1"><?= count($grupo) ?></span>
        </div>
        <i class="bi bi-chevron-down text-muted small"></i>
    </div>

    <div class="collapse show" id="grupo-<?= $tipoKey ?>">
        <div class="table-responsive">
            <table class="table table-blue table-hover table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <?php if ($isAdmin): ?><th style="width:32px"></th><?php endif; ?>
                        <th style="width:95px">DNI</th>
                        <th style="width:80px">Grado</th>
                        <th>Apellido y Nombre</th>
                        <th class="d-none d-md-table-cell">Arma / Esp.</th>
                        <th class="d-none d-lg-table-cell">Destino / Cargo</th>
                        <th style="width:110px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($grupo as $p): ?>
                <tr class="<?= !$p->activo ? 'table-secondary opacity-75' : '' ?>">
                    <?php if ($isAdmin): ?>
                    <td>
                        <input type="checkbox" class="form-check-input check-persona mt-0"
                               data-grupo="<?= $tipoKey ?>"
                               value="<?= $p->id ?>"
                               onchange="actualizarBarraMasiva()">
                    </td>
                    <?php endif; ?>
                    <td class="font-monospace small"><?= esc($p->dni) ?></td>
                    <td class="fw-semibold small"><?= esc($p->grado ?? '') ?></td>
                    <td>
                        <span class="fw-semibold"><?= esc($p->apellido) ?></span>
                        <?php if (!empty($p->nombre)): ?><span class="text-muted">, <?= esc($p->nombre) ?></span><?php endif; ?>
                        <?php if (!$p->activo): ?><span class="badge bg-danger ms-1 small">Inactivo</span><?php endif; ?>
                    </td>
                    <td class="small d-none d-md-table-cell text-muted"><?= esc($p->arma_especialidad ?? '—') ?></td>
                    <td class="small d-none d-lg-table-cell">
                        <?= esc($p->destino_interno ?? '') ?>
                        <?php if (!empty($p->cargo)): ?><br><span class="text-muted"><?= esc($p->cargo) ?></span><?php endif; ?>
                    </td>
                    <td><?= renderAcciones($p, $isAdmin) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>
</form>

<?php endif; ?>

<?php else: ?>
<!-- ══════════════════════════════════════════════════════════ -->
<!-- VISTA TABLA (paginada)                                    -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <form id="wrapperTabla">
        <div class="table-responsive">
            <table class="table table-blue table-hover table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <?php if ($isAdmin): ?>
                        <th style="width:32px">
                            <input type="checkbox" class="form-check-input mt-0" id="checkTodos"
                                   onclick="toggleTodos(this.checked)" title="Seleccionar todos">
                        </th>
                        <?php endif; ?>
                        <th style="width:95px">DNI</th>
                        <th style="width:80px">Grado</th>
                        <th>Apellido y Nombre</th>
                        <th class="d-none d-lg-table-cell">Arma / Esp.</th>
                        <th class="d-none d-md-table-cell">Destino Interno</th>
                        <th class="d-none d-xl-table-cell">Cargo</th>
                        <th style="width:90px">Tipo</th>
                        <th style="width:110px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($personas)): ?>
                    <tr>
                        <td colspan="<?= $isAdmin ? '9' : '8' ?>" class="text-center text-muted py-4">
                            <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>
                            No se encontraron registros con los filtros actuales.
                            <?php if ($isAdmin): ?>
                            <br><a href="<?= site_url('personal/nuevo') ?>" class="btn btn-sm btn-ea-blue mt-2">
                                <i class="bi bi-person-plus"></i> Agregar primer integrante
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($personas as $p): ?>
                    <?php $tl = $tiposLabel[$p->tipo] ?? ['label' => $p->tipo, 'badge' => 'bg-secondary']; ?>
                    <tr class="<?= !$p->activo ? 'table-secondary opacity-75' : '' ?>">
                        <?php if ($isAdmin): ?>
                        <td>
                            <input type="checkbox" class="form-check-input check-persona mt-0"
                                   value="<?= $p->id ?>"
                                   onchange="actualizarBarraMasiva()">
                        </td>
                        <?php endif; ?>
                        <td class="font-monospace small"><?= esc($p->dni) ?></td>
                        <td class="fw-semibold small"><?= esc($p->grado ?? '') ?></td>
                        <td>
                            <span class="fw-semibold"><?= esc($p->apellido) ?></span>
                            <?php if (!empty($p->nombre)): ?>
                            <span class="text-muted">, <?= esc($p->nombre) ?></span>
                            <?php endif; ?>
                            <?php if (!$p->activo): ?>
                            <span class="badge bg-danger ms-1 small">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="small d-none d-lg-table-cell text-muted"><?= esc($p->arma_especialidad ?? '—') ?></td>
                        <td class="small d-none d-md-table-cell"><?= esc($p->destino_interno ?? '') ?></td>
                        <td class="small d-none d-xl-table-cell text-muted"><?= esc($p->cargo ?? '') ?></td>
                        <td><span class="badge <?= $tl['badge'] ?> small"><?= $tl['label'] ?></span></td>
                        <td><?= renderAcciones($p, $isAdmin) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </form>
    </div>
</div>

<!-- Paginación -->
<?php if ($pager): ?>
<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
    <div class="text-muted small">
        Mostrando hasta <?= $perPage ?> de <?= $total ?> registros
    </div>
    <div>
        <?= $pager->links() ?>
    </div>
</div>
<?php endif; ?>

<?php endif; /* fin toggle vista */ ?>


<!-- Modal confirmar desactivar individual -->
<div class="modal fade" id="modalDesactivar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Confirmar desactivación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Desactivar a <strong id="modalNombrePersona"></strong>?<br>
                <span class="text-muted small">El integrante no aparecerá más en los formularios. Puede reactivarlo después.</span></p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <form id="formDesactivar" method="POST" style="display:inline">
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
// ── Acciones individuales ──────────────────────────────────────────────────
function confirmarDesactivar(id, nombre) {
    document.getElementById('modalNombrePersona').textContent = nombre;
    document.getElementById('formDesactivar').action = '<?= site_url('personal/eliminar/') ?>' + id;
    new bootstrap.Modal(document.getElementById('modalDesactivar')).show();
}

// ── Checkboxes ────────────────────────────────────────────────────────────
function toggleTodos(checked) {
    document.querySelectorAll('.check-persona').forEach(cb => {
        cb.checked = checked;
    });
    actualizarBarraMasiva();
}

function toggleGrupo(grupo, checked) {
    document.querySelectorAll('.check-persona[data-grupo="' + grupo + '"]').forEach(cb => {
        cb.checked = checked;
    });
    actualizarBarraMasiva();
}

function deseleccionarTodo() {
    document.querySelectorAll('.check-persona').forEach(cb => cb.checked = false);
    const checkTodos = document.getElementById('checkTodos');
    if (checkTodos) checkTodos.checked = false;
    document.querySelectorAll('.check-grupo').forEach(cb => cb.checked = false);
    actualizarBarraMasiva();
}

function actualizarBarraMasiva() {
    const seleccionados = document.querySelectorAll('.check-persona:checked');
    const barra = document.getElementById('barraAccionMasiva');
    const cont  = document.getElementById('contSeleccionados');
    if (!barra) return;
    if (seleccionados.length > 0) {
        barra.classList.remove('d-none');
        cont.textContent = seleccionados.length;
    } else {
        barra.classList.add('d-none');
    }
}

function ejecutarAccionMasiva(accion) {
    const seleccionados = document.querySelectorAll('.check-persona:checked');
    if (seleccionados.length === 0) return;

    const verbo = accion === 'desactivar' ? 'desactivar' : 'reactivar';
    if (!confirm('¿' + verbo.charAt(0).toUpperCase() + verbo.slice(1) + ' ' + seleccionados.length + ' integrante(s) seleccionado(s)?')) return;

    const form     = document.getElementById('formAccionMasiva');
    const inputAcc = document.getElementById('inputAccion');
    const divIds   = document.getElementById('inputsIds');

    inputAcc.value = accion;
    divIds.innerHTML = '';
    seleccionados.forEach(cb => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'ids[]';
        inp.value = cb.value;
        divIds.appendChild(inp);
    });
    form.submit();
}
</script>

<?php
/**
 * Helper local: renderiza los botones de acción de una fila
 */
function renderAcciones($p, bool $isAdmin): string {
    $html = '';

    // Botón Historial: visible para todos los roles autenticados
    $html .= '<a href="' . site_url('personal/historial/' . $p->id) . '"
                 class="btn btn-xs btn-outline-primary me-1" title="Ver historial de sanciones y actuaciones">
                 <i class="bi bi-clock-history"></i>
              </a>';

    if ($isAdmin) {
        $nombre = addslashes($p->apellido . ($p->nombre ? ', ' . $p->nombre : ''));
        $html .= '<a href="' . site_url('personal/editar/' . $p->id) . '"
                     class="btn btn-xs btn-outline-secondary me-1" title="Editar">
                     <i class="bi bi-pencil-fill"></i>
                  </a>';
        if ($p->activo) {
            $html .= '<button type="button"
                         class="btn btn-xs btn-outline-danger"
                         title="Desactivar"
                         onclick="confirmarDesactivar(' . $p->id . ', \'' . $nombre . '\')">
                         <i class="bi bi-person-dash-fill"></i>
                      </button>';
        } else {
            $html .= '<form method="POST" action="' . site_url('personal/reactivar/' . $p->id) . '" style="display:inline">
                         ' . csrf_field() . '
                         <button type="submit" class="btn btn-xs btn-outline-success" title="Reactivar">
                             <i class="bi bi-person-check-fill"></i>
                         </button>
                      </form>';
        }
    }
    return $html;
}
?>
