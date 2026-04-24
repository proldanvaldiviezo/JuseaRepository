<?php
/**
 * Vista: Panel de Trazabilidad de Expedientes
 * Variables: $porArea, $totales, $tipo, $area, $filas, $todasLasAreas
 */

$tiposLabel = [
    'sancion_cuadros'    => ['label' => 'Sanción Cuadros',    'badge' => 'bg-danger',          'icon' => 'bi-file-earmark-ruled-fill'],
    'sancion_cadetes'    => ['label' => 'Sanción Cadetes',    'badge' => 'bg-warning text-dark','icon' => 'bi-file-earmark-ruled-fill'],
    'actuacion_bienes'   => ['label' => 'Bienes',             'badge' => 'bg-warning text-dark','icon' => 'bi-box-seam-fill'],
    'actuacion_accidente'=> ['label' => 'Acc./Enf.',          'badge' => 'bg-info text-dark',  'icon' => 'bi-heart-pulse-fill'],
];

$estadoBadge = [
    'en_tramite' => 'bg-primary',
    'recibido'   => 'bg-secondary',
    'elevado'    => 'bg-danger',
    'devuelto'   => 'bg-warning text-dark',
    'suspendido' => 'bg-secondary',
    'resuelto'   => 'bg-success',
    'archivado'  => 'bg-dark',
];

$totalGeneral = count($filas);

function fmtFechaP(?string $f): string {
    if (!$f) return '—';
    try { return (new DateTime($f))->format('d/m/Y'); } catch (\Exception $e) { return esc($f); }
}
?>

<!-- ── Encabezado ─────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:52px;height:52px;background:rgba(26,58,107,.12);">
        <i class="bi bi-diagram-3-fill fs-4" style="color:var(--ea-blue)"></i>
    </div>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem;">Trazabilidad de Expedientes</h2>
        <p class="text-muted mb-0 small">Seguimiento de expedientes activos por área — Sanciones y Actuaciones CMN</p>
    </div>
</div>

<!-- ── Estadísticas rápidas ──────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100" style="border-top:3px solid var(--ea-blue)!important;">
            <div class="card-body py-2">
                <div class="fs-3 fw-bold" style="color:var(--ea-blue)"><?= $totalGeneral ?></div>
                <div class="small text-muted">Expedientes activos</div>
            </div>
        </div>
    </div>
    <?php foreach ($totales as $t => $cnt): ?>
    <?php $tl = $tiposLabel[$t] ?? ['label'=>$t,'badge'=>'bg-secondary','icon'=>'bi-file']; ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100"
             style="border-top:3px solid <?= strpos($tl['badge'],'danger') !== false ? '#dc3545' : (strpos($tl['badge'],'warning') !== false ? '#ffc107' : '#0dcaf0') ?>!important;cursor:pointer;"
             onclick="filtrarTipo('<?= $t ?>')">
            <div class="card-body py-2">
                <i class="bi <?= $tl['icon'] ?> text-muted mb-1 d-block"></i>
                <div class="fs-3 fw-bold"><?= $cnt ?></div>
                <div class="small text-muted"><?= $tl['label'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Filtros ────────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="<?= site_url('trazabilidad') ?>" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label form-label-sm mb-1">Tipo de expediente</label>
                <select name="tipo" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($tiposLabel as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $tipo === $k ? 'selected' : '' ?>><?= $v['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label form-label-sm mb-1">Área actual</label>
                <select name="area" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todas las áreas</option>
                    <?php foreach ($todasLasAreas as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $area === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($tipo || $area): ?>
            <div class="col-auto">
                <a href="<?= site_url('trazabilidad') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar filtros
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ── Tabla principal por área ──────────────────────────────────────────── -->
<?php if (empty($filas)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-diagram-3 fs-1 d-block mb-2 opacity-25"></i>
        No hay expedientes activos<?= ($tipo || $area) ? ' con los filtros seleccionados' : '' ?>.
    </div>
</div>

<?php else: ?>

<?php foreach ($porArea as $areaKey => $expedientesArea):
    $areaInfo  = \App\Models\TrazabilidadModel::todasLasAreas();
    $areaLabel = $areaInfo[$areaKey] ?? ucfirst($areaKey);
    $estilo    = \App\Models\TrazabilidadModel::estiloArea($areaKey);
    $cantArea  = count($expedientesArea);
?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header d-flex align-items-center justify-content-between py-2"
         style="background:rgba(26,58,107,.06);border-bottom:2px solid var(--ea-blue);"
         data-bs-toggle="collapse" data-bs-target="#area-<?= $areaKey ?>" style="cursor:pointer">
        <div class="d-flex align-items-center gap-2">
            <i class="bi <?= $estilo['icon'] ?>" style="color:var(--ea-blue)"></i>
            <strong><?= esc($areaLabel) ?></strong>
            <span class="badge <?= $estilo['badge'] ?> ms-1"><?= $cantArea ?> expediente<?= $cantArea !== 1 ? 's' : '' ?></span>
        </div>
        <i class="bi bi-chevron-down text-muted small"></i>
    </div>

    <div class="collapse show" id="area-<?= $areaKey ?>">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:110px">Tipo</th>
                        <th style="width:130px">N° Expediente</th>
                        <th>Causante</th>
                        <th class="d-none d-md-table-cell">Responsable en área</th>
                        <th class="d-none d-md-table-cell">Estado</th>
                        <th style="width:90px">Desde</th>
                        <th style="width:70px">Días</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($expedientesArea as $exp):
                    $dias = $exp['dias'];
                    $colorDias = \App\Models\TrazabilidadModel::colorDias($dias);
                    $tl   = $tiposLabel[$exp['tipo_expediente']] ?? ['label'=>$exp['tipo_label'],'badge'=>'bg-secondary'];
                    $eBadge = $estadoBadge[$exp['estado_paso']] ?? 'bg-secondary';
                    $estadosPaso = \App\Models\TrazabilidadModel::estadosPaso();
                    $estadoLabel = $estadosPaso[$exp['estado_paso']] ?? $exp['estado_paso'];
                ?>
                <tr>
                    <td><span class="badge <?= $tl['badge'] ?> small"><?= $tl['label'] ?></span></td>
                    <td class="font-monospace small fw-semibold"><?= esc($exp['nro_display']) ?></td>
                    <td class="fw-semibold small"><?= esc($exp['causante']) ?></td>
                    <td class="small d-none d-md-table-cell text-muted">
                        <?= esc($exp['responsable_nombre'] ?: '—') ?>
                        <?php if ($exp['responsable_cargo']): ?>
                        <br><span class="text-muted opacity-75"><?= esc($exp['responsable_cargo']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <span class="badge <?= $eBadge ?> small"><?= esc($estadoLabel) ?></span>
                    </td>
                    <td class="small font-monospace"><?= fmtFechaP($exp['fecha_entrada']) ?></td>
                    <td class="small fw-bold <?= $colorDias ?>">
                        <?= \App\Models\TrazabilidadModel::labelDias($dias) ?>
                    </td>
                    <td>
                        <a href="<?= site_url('trazabilidad/ver/' . $exp['tipo_expediente'] . '/' . $exp['id_expediente']) ?>"
                           class="btn btn-xs btn-outline-primary" title="Ver trazabilidad completa">
                            <i class="bi bi-diagram-3"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endforeach; ?>

<?php endif; ?>

<!-- Alerta semáforo -->
<div class="d-flex gap-2 align-items-center mt-3 small text-muted">
    <span><span class="fw-bold text-success">■</span> ≤5 días</span>
    <span><span class="fw-bold text-warning">■</span> 6–15 días</span>
    <span><span class="fw-bold text-danger">■</span> >15 días (demorado)</span>
</div>

<script>
function filtrarTipo(tipo) {
    const url = new URL(window.location.href);
    url.searchParams.set('tipo', tipo);
    url.searchParams.delete('area');
    window.location.href = url.toString();
}
</script>
