<?php
/**
 * Vista: Historial de sanciones y actuaciones por persona
 * Variables: $persona, $sanciones (array), $bienes (array), $accidentes (array)
 */

$personaActiva = !($persona->bajaUnidad ?? false);

$totalSanciones  = count($sanciones);
$totalBienes     = count($bienes);
$totalAccidentes = count($accidentes);
$totalGeneral    = $totalSanciones + $totalBienes + $totalAccidentes;

// Helper: formatear fecha (Y-m-d → d/m/Y)
function fmtFecha(?string $f): string {
    if (!$f || $f === '0000-00-00') return '—';
    try { return (new DateTime($f))->format('d/m/Y'); } catch (\Exception $e) { return esc($f); }
}

// Helper: badge de estado
function badgeEstado(string $estado): string {
    $map = [
        'activa'    => 'bg-success',
        'cumplida'  => 'bg-secondary',
        'anulada'   => 'bg-danger',
        'pendiente' => 'bg-warning text-dark',
    ];
    $cls = $map[strtolower($estado)] ?? 'bg-secondary';
    return '<span class="badge ' . $cls . ' small">' . esc(ucfirst($estado)) . '</span>';
}
?>

<!-- ── Encabezado ─────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="<?= site_url('personal') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Padrón
    </a>
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:52px;height:52px;background:rgba(26,58,107,.12);">
        <i class="bi bi-clock-history fs-4" style="color:var(--ea-blue)"></i>
    </div>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem;">
            Historial — <?= esc($persona->apellido) ?><?= $persona->nombre ? ', ' . esc($persona->nombre) : '' ?>
        </h2>
        <p class="text-muted mb-0 small">Situación y antecedentes del personal en el sistema JUSEA CMN</p>
    </div>
</div>

<!-- ── Datos de la persona ───────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1">DNI</div>
                <div class="fw-semibold font-monospace"><?= esc($persona->dni) ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1">Grado</div>
                <div class="fw-semibold"><?= esc($persona->grado ?? '—') ?></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1">Estado</div>
                <div>
                    <?php if ($personaActiva): ?>
                    <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                    <span class="badge bg-danger">Inactivo</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($persona->arma_especialidad)): ?>
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1">Arma / Especialidad</div>
                <div><?= esc($persona->arma_especialidad) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Resumen numérico ──────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100" style="border-top:3px solid var(--ea-blue)!important;">
            <div class="card-body py-2">
                <div class="fs-3 fw-bold" style="color:var(--ea-blue)"><?= $totalGeneral ?></div>
                <div class="small text-muted">Total registros</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100" style="border-top:3px solid #dc3545!important;">
            <div class="card-body py-2">
                <div class="fs-3 fw-bold text-danger"><?= $totalSanciones ?></div>
                <div class="small text-muted">Sanciones Disciplinarias</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100" style="border-top:3px solid #fd7e14!important;">
            <div class="card-body py-2">
                <div class="fs-3 fw-bold" style="color:#fd7e14"><?= $totalBienes ?></div>
                <div class="small text-muted">Actuaciones por Bienes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center h-100" style="border-top:3px solid #0dcaf0!important;">
            <div class="card-body py-2">
                <div class="fs-3 fw-bold text-info"><?= $totalAccidentes ?></div>
                <div class="small text-muted">Actuaciones Acc./Enf.</div>
            </div>
        </div>
    </div>
</div>

<!-- ── Tabs ───────────────────────────────────────────────────────────────── -->
<ul class="nav nav-tabs mb-0" id="historialTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-sanciones" data-bs-toggle="tab"
                data-bs-target="#pane-sanciones" type="button" role="tab">
            <i class="bi bi-file-earmark-ruled me-1"></i>
            Sanciones Disciplinarias
            <?php if ($totalSanciones > 0): ?>
            <span class="badge bg-danger ms-1"><?= $totalSanciones ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-bienes" data-bs-toggle="tab"
                data-bs-target="#pane-bienes" type="button" role="tab">
            <i class="bi bi-box-seam me-1"></i>
            Pérdida de Bienes
            <?php if ($totalBienes > 0): ?>
            <span class="badge bg-warning text-dark ms-1"><?= $totalBienes ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-accidente" data-bs-toggle="tab"
                data-bs-target="#pane-accidente" type="button" role="tab">
            <i class="bi bi-heart-pulse me-1"></i>
            Accidente / Enfermedad
            <?php if ($totalAccidentes > 0): ?>
            <span class="badge bg-info ms-1"><?= $totalAccidentes ?></span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- ══ SANCIONES DISCIPLINARIAS ══════════════════════════════════════════ -->
    <div class="tab-pane fade show active" id="pane-sanciones" role="tabpanel">
        <div class="card border-0 shadow-sm" style="border-top-left-radius:0">
            <div class="card-body p-0">
                <?php if (empty($sanciones)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-check-circle fs-2 d-block mb-2 opacity-25"></i>
                    Sin sanciones disciplinarias registradas.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:100px">Fecha</th>
                                <th>Motivo</th>
                                <th class="d-none d-md-table-cell">Tipo sanción</th>
                                <th class="d-none d-md-table-cell">Duración</th>
                                <th class="d-none d-lg-table-cell">Autoridad</th>
                                <th class="d-none d-xl-table-cell">Letra / Nro.</th>
                                <th style="width:90px">Estado</th>
                                <th style="width:70px"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($sanciones as $s): ?>
                        <tr>
                            <td class="small font-monospace"><?= fmtFecha($s['fecha_comision']) ?></td>
                            <td class="small"><?= esc($s['motivo'] ?? '—') ?></td>
                            <td class="small d-none d-md-table-cell"><?= esc($s['tipo_sancion_desc'] ?? $s['tipo_sancion'] ?? '—') ?></td>
                            <td class="small d-none d-md-table-cell"><?= esc($s['duracion'] ?? '—') ?></td>
                            <td class="small d-none d-lg-table-cell">
                                <?php if (!empty($s['grado_autoridad'])): ?>
                                <?= esc($s['grado_autoridad']) ?> <?= esc($s['apellido_autoridad'] ?? '') ?>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td class="small d-none d-xl-table-cell font-monospace">
                                <?php
                                $letraNro = trim(($s['letra'] ?? '') . ' / ' . ($s['nro'] ?? ''));
                                echo $letraNro !== ' / ' ? esc($letraNro) : '—';
                                ?>
                            </td>
                            <td><?= badgeEstado($s['estado'] ?? 'activa') ?></td>
                            <td class="text-nowrap">
                                <a href="<?= site_url('sancion/cuadros/descargar/' . $s['sancion_id'] . '/planilla') ?>"
                                   class="btn btn-xs btn-outline-secondary me-1"
                                   title="Ver planilla" target="_blank">
                                    <i class="bi bi-file-earmark-arrow-down"></i>
                                </a>
                                <?php $tipoSancion = ($s['tipo_sancion'] === 'cadetes') ? 'sancion_cadetes' : 'sancion_cuadros'; ?>
                                <a href="<?= site_url('trazabilidad/ver/' . $tipoSancion . '/' . $s['sancion_id']) ?>"
                                   class="btn btn-xs btn-outline-primary"
                                   title="Ver trazabilidad">
                                    <i class="bi bi-diagram-3"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══ ACTUACIONES POR BIENES ════════════════════════════════════════════ -->
    <div class="tab-pane fade" id="pane-bienes" role="tabpanel">
        <div class="card border-0 shadow-sm" style="border-top-left-radius:0">
            <div class="card-body p-0">
                <?php if (empty($bienes)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-check-circle fs-2 d-block mb-2 opacity-25"></i>
                    Sin actuaciones por pérdida de bienes registradas.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:100px">Fecha</th>
                                <th>Nro. Expediente</th>
                                <th class="d-none d-md-table-cell">Grado causante</th>
                                <th style="width:90px">Estado</th>
                                <th style="width:70px"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($bienes as $b): ?>
                        <tr>
                            <td class="small font-monospace"><?= fmtFecha($b['created_at']) ?></td>
                            <td class="small"><?= esc($b['nro_expediente'] ?: '—') ?></td>
                            <td class="small d-none d-md-table-cell"><?= esc($b['causante_grado'] ?? '—') ?></td>
                            <td><?= badgeEstado($b['estado'] ?? 'activa') ?></td>
                            <td class="text-nowrap">
                                <a href="<?= site_url('actuacion/bienes/ver/' . $b['id']) ?>"
                                   class="btn btn-xs btn-outline-secondary me-1"
                                   title="Ver actuación" target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= site_url('trazabilidad/ver/actuacion_bienes/' . $b['id']) ?>"
                                   class="btn btn-xs btn-outline-primary"
                                   title="Ver trazabilidad">
                                    <i class="bi bi-diagram-3"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══ ACTUACIONES POR ACCIDENTE / ENFERMEDAD ════════════════════════════ -->
    <div class="tab-pane fade" id="pane-accidente" role="tabpanel">
        <div class="card border-0 shadow-sm" style="border-top-left-radius:0">
            <div class="card-body p-0">
                <?php if (empty($accidentes)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-check-circle fs-2 d-block mb-2 opacity-25"></i>
                    Sin actuaciones por accidente o enfermedad registradas.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:100px">Fecha</th>
                                <th>Nro. Expediente</th>
                                <th class="d-none d-md-table-cell">Grado causante</th>
                                <th style="width:90px">Estado</th>
                                <th style="width:70px"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($accidentes as $a): ?>
                        <tr>
                            <td class="small font-monospace"><?= fmtFecha($a['created_at']) ?></td>
                            <td class="small"><?= esc($a['nro_expediente'] ?: '—') ?></td>
                            <td class="small d-none d-md-table-cell"><?= esc($a['causante_grado'] ?? '—') ?></td>
                            <td><?= badgeEstado($a['estado'] ?? 'activa') ?></td>
                            <td class="text-nowrap">
                                <a href="<?= site_url('actuacion/accidente/ver/' . $a['id']) ?>"
                                   class="btn btn-xs btn-outline-secondary me-1"
                                   title="Ver actuación" target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= site_url('trazabilidad/ver/actuacion_accidente/' . $a['id']) ?>"
                                   class="btn btn-xs btn-outline-primary"
                                   title="Ver trazabilidad">
                                    <i class="bi bi-diagram-3"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- /tab-content -->

<!-- Nota informativa sobre matching de actuaciones -->
<?php if ($totalBienes > 0 || $totalAccidentes > 0): ?>
<p class="text-muted small mt-3">
    <i class="bi bi-info-circle me-1"></i>
    Las actuaciones se identifican por coincidencia de apellido. Si existen personas homónimas, revisar manualmente cada expediente.
</p>
<?php endif; ?>
