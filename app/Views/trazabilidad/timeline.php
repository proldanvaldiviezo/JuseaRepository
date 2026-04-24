<?php
/**
 * Vista: Timeline de trazabilidad de un expediente
 * Variables: $tipo, $id, $expediente, $cadena, $pasoActual,
 *            $areasDisp, $estadosPaso, $tipoLabel
 */

$estadoBadge = [
    'en_tramite' => 'bg-primary',
    'recibido'   => 'bg-secondary',
    'elevado'    => 'bg-danger',
    'devuelto'   => 'bg-warning text-dark',
    'suspendido' => 'bg-secondary',
    'resuelto'   => 'bg-success',
    'archivado'  => 'bg-dark',
];

$areaActual    = $pasoActual['area']      ?? '—';
$areaLabel     = $pasoActual['area_label']?? $areaActual;
$estiloActual  = \App\Models\TrazabilidadModel::estiloArea($areaActual);
$diasActual    = $pasoActual ? \App\Models\TrazabilidadModel::diasEntre($pasoActual['fecha_entrada']) : 0;

// Tiempo total del expediente (desde primer paso)
$primerPaso = !empty($cadena) ? reset($cadena) : null;
$diasTotal  = $primerPaso ? \App\Models\TrazabilidadModel::diasEntre($primerPaso['fecha_entrada']) : 0;

function fmtDT(?string $f): string {
    if (!$f) return '—';
    try { return (new DateTime($f))->format('d/m/Y H:i'); } catch (\Exception $e) { return esc($f); }
}
function fmtD(?string $f): string {
    if (!$f) return '—';
    try { return (new DateTime($f))->format('d/m/Y'); } catch (\Exception $e) { return esc($f); }
}
?>

<!-- ── CSS timeline ────────────────────────────────────────────────────────── -->
<style>
.trz-timeline { position:relative; padding-left:2.4rem; }
.trz-timeline::before {
    content:''; position:absolute; left:.65rem; top:.5rem;
    bottom:0; width:3px; background:#dee2e6; border-radius:2px;
}
.trz-paso { position:relative; margin-bottom:1.6rem; }
.trz-paso::before {
    content:''; position:absolute; left:-1.82rem; top:.45rem;
    width:14px; height:14px; border-radius:50%;
    background:#6c757d; border:3px solid #fff;
    box-shadow:0 0 0 2px #6c757d;
}
.trz-paso.paso-activo::before {
    background:#198754; box-shadow:0 0 0 2px #198754;
    animation:trz-pulse 1.6s ease-in-out infinite;
}
.trz-paso.paso-cerrado::before { background:#adb5bd; box-shadow:0 0 0 2px #adb5bd; }
@keyframes trz-pulse {
    0%,100% { box-shadow:0 0 0 2px #198754; }
    50%      { box-shadow:0 0 0 6px rgba(25,135,84,.25); }
}
.trz-card { border-left:4px solid #dee2e6; }
.trz-card.activo { border-left-color:#198754; }
.dias-badge { font-size:.75rem; font-weight:600; }
</style>

<!-- ── Encabezado ─────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <a href="<?= site_url('trazabilidad') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Panel
    </a>
    <a href="<?= site_url('trazabilidad') ?>?tipo=<?= urlencode($tipo) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-funnel"></i> <?= esc($tipoLabel) ?>
    </a>
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:48px;height:48px;background:rgba(26,58,107,.12);">
        <i class="bi bi-diagram-3-fill" style="color:var(--ea-blue)"></i>
    </div>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.2rem;">
            Trazabilidad — <?= esc($expediente['nro']) ?>
        </h2>
        <span class="text-muted small"><?= esc($tipoLabel) ?></span>
    </div>
</div>

<!-- ── Tarjeta resumen del expediente ──────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-6 col-md-3">
                <div class="small text-muted mb-1">Causante</div>
                <div class="fw-semibold"><?= esc($expediente['causante'] ?: '—') ?></div>
            </div>
            <?php if ($expediente['motivo']): ?>
            <div class="col-12 col-md-4">
                <div class="small text-muted mb-1">Motivo / Descripción</div>
                <div class="small"><?= esc($expediente['motivo']) ?></div>
            </div>
            <?php endif; ?>
            <div class="col-6 col-md-2">
                <div class="small text-muted mb-1">Área actual</div>
                <div>
                    <span class="badge <?= $estiloActual['badge'] ?> small">
                        <i class="bi <?= $estiloActual['icon'] ?> me-1"></i><?= esc($areaLabel) ?>
                    </span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="small text-muted mb-1">Días en área</div>
                <div class="fw-bold <?= \App\Models\TrazabilidadModel::colorDias($diasActual) ?>">
                    <?= \App\Models\TrazabilidadModel::labelDias($diasActual) ?>
                </div>
            </div>
            <div class="col-6 col-md-1">
                <div class="small text-muted mb-1">Total</div>
                <div class="small text-muted"><?= \App\Models\TrazabilidadModel::labelDias($diasTotal) ?></div>
            </div>
        </div>

        <!-- Links al expediente origen -->
        <div class="mt-3 d-flex gap-2 flex-wrap">
            <?php if (in_array($tipo, ['sancion_cuadros','sancion_cadetes'])): ?>
            <a href="<?= site_url('sancion/cuadros/descargar/' . $id . '/planilla') ?>"
               class="btn btn-xs btn-outline-secondary" target="_blank">
                <i class="bi bi-file-earmark-arrow-down me-1"></i>Ver planilla sanción
            </a>
            <?php elseif ($tipo === 'actuacion_bienes'): ?>
            <a href="<?= site_url('actuacion/bienes/ver/' . $id) ?>"
               class="btn btn-xs btn-outline-secondary" target="_blank">
                <i class="bi bi-eye me-1"></i>Ver actuación bienes
            </a>
            <?php elseif ($tipo === 'actuacion_accidente'): ?>
            <a href="<?= site_url('actuacion/accidente/ver/' . $id) ?>"
               class="btn btn-xs btn-outline-secondary" target="_blank">
                <i class="bi bi-eye me-1"></i>Ver actuación accidente
            </a>
            <?php endif; ?>

            <button type="button" class="btn btn-xs btn-success ms-auto"
                    data-bs-toggle="modal" data-bs-target="#modalNuevoPase">
                <i class="bi bi-arrow-right-circle-fill me-1"></i>Registrar pase
            </button>
        </div>
    </div>
</div>

<!-- ── Timeline ───────────────────────────────────────────────────────────── -->
<?php if (empty($cadena)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-clock-history fs-2 d-block mb-2 opacity-25"></i>
        No hay pases registrados aún para este expediente.<br>
        <button type="button" class="btn btn-sm btn-success mt-3"
                data-bs-toggle="modal" data-bs-target="#modalNuevoPase">
            <i class="bi bi-plus-circle me-1"></i>Registrar primer pase
        </button>
    </div>
</div>
<?php else: ?>

<h6 class="text-muted fw-semibold mb-3 small text-uppercase letter-spacing-1">
    Cadena de custodia — <?= count($cadena) ?> paso<?= count($cadena) !== 1 ? 's' : '' ?>
</h6>

<div class="trz-timeline">
<?php foreach ($cadena as $i => $paso):
    $esActivo  = (bool)$paso['activo'];
    $esCerrado = !$esActivo;
    $estilo    = \App\Models\TrazabilidadModel::estiloArea($paso['area']);
    $eBadge    = $estadoBadge[$paso['estado_paso']] ?? 'bg-secondary';
    $estadoLabel = $estadosPaso[$paso['estado_paso']] ?? $paso['estado_paso'];
    $diasEnArea  = \App\Models\TrazabilidadModel::diasEntre($paso['fecha_entrada'], $paso['fecha_salida'] ?? null);
    $colorDias   = \App\Models\TrazabilidadModel::colorDias($diasEnArea);
    $numPaso     = $i + 1;
?>

<div class="trz-paso <?= $esActivo ? 'paso-activo' : 'paso-cerrado' ?>">
    <div class="card border-0 shadow-sm trz-card <?= $esActivo ? 'activo' : '' ?>">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">

                <!-- Lado izquierdo: área + estado + responsable -->
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge <?= $estilo['badge'] ?>">
                            <i class="bi <?= $estilo['icon'] ?> me-1"></i><?= esc($paso['area_label'] ?: $paso['area']) ?>
                        </span>
                        <span class="badge <?= $eBadge ?> small"><?= esc($estadoLabel) ?></span>
                        <?php if ($esActivo): ?>
                        <span class="badge bg-success small">
                            <i class="bi bi-record-circle me-1"></i>EN PODER
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($paso['responsable_nombre']): ?>
                    <div class="small">
                        <i class="bi bi-person-fill me-1 text-muted"></i>
                        <strong><?= esc($paso['responsable_nombre']) ?></strong>
                        <?php if ($paso['responsable_cargo']): ?>
                        <span class="text-muted"> — <?= esc($paso['responsable_cargo']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($paso['observaciones']): ?>
                    <div class="small text-muted mt-1">
                        <i class="bi bi-chat-left-text me-1"></i><?= esc($paso['observaciones']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Lado derecho: fechas + tiempo -->
                <div class="text-end small">
                    <div class="font-monospace">
                        <i class="bi bi-box-arrow-in-right me-1 text-muted"></i><?= fmtDT($paso['fecha_entrada']) ?>
                    </div>
                    <?php if ($paso['fecha_salida']): ?>
                    <div class="font-monospace text-muted">
                        <i class="bi bi-box-arrow-right me-1"></i><?= fmtDT($paso['fecha_salida']) ?>
                    </div>
                    <?php else: ?>
                    <div class="text-success fst-italic">
                        <i class="bi bi-clock me-1"></i>En curso
                    </div>
                    <?php endif; ?>
                    <div class="dias-badge mt-1 <?= $colorDias ?>">
                        <i class="bi bi-hourglass-split me-1"></i><?= \App\Models\TrazabilidadModel::labelDias($diasEnArea) ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php endforeach; ?>

<!-- Botón final de la línea -->
<div style="padding-left:.1rem; margin-top:.5rem;">
    <button type="button" class="btn btn-sm btn-outline-success"
            data-bs-toggle="modal" data-bs-target="#modalNuevoPase">
        <i class="bi bi-plus-circle me-1"></i>Registrar nuevo pase
    </button>
</div>

</div><!-- /trz-timeline -->
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════════════════════
     MODAL: Registrar nuevo pase
══════════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalNuevoPase" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= site_url('trazabilidad/registrar-paso') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="tipo_expediente" value="<?= esc($tipo) ?>">
                <input type="hidden" name="id_expediente"   value="<?= (int)$id ?>">

                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-right-circle-fill text-success me-2"></i>
                        Registrar Pase de Área
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-light border small mb-3 py-2">
                        <strong>Expediente:</strong> <?= esc($expediente['nro']) ?> —
                        <strong>Causante:</strong> <?= esc($expediente['causante']) ?>
                    </div>

                    <div class="row g-3">
                        <!-- Área destino -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Área que recibe el expediente <span class="text-danger">*</span>
                            </label>
                            <select name="area" class="form-select" required>
                                <option value="">— Seleccionar área —</option>
                                <?php foreach ($areasDisp as $k => $v):
                                    $est = \App\Models\TrazabilidadModel::estiloArea($k);
                                ?>
                                <option value="<?= $k ?>"
                                    <?= ($pasoActual && $pasoActual['area'] === $k) ? '' : '' ?>>
                                    <?= esc($v) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($pasoActual): ?>
                            <div class="form-text">
                                Área actual: <strong><?= esc($pasoActual['area_label']) ?></strong>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Estado del paso -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado del paso <span class="text-danger">*</span></label>
                            <select name="estado_paso" class="form-select" required>
                                <?php foreach ($estadosPaso as $k => $v): ?>
                                <option value="<?= $k ?>" <?= $k === 'en_tramite' ? 'selected' : '' ?>>
                                    <?= esc($v) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Responsable nombre -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Responsable (Grado Apellido, Nombre)
                            </label>
                            <input type="text" name="responsable_nombre" class="form-control"
                                   placeholder="Ej: MY GARCIA, Roberto"
                                   maxlength="200">
                        </div>

                        <!-- Responsable cargo -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cargo del responsable</label>
                            <input type="text" name="responsable_cargo" class="form-control"
                                   placeholder="Ej: Jefe del Área de Personal"
                                   maxlength="200">
                        </div>

                        <!-- Fecha de entrada -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Fecha y hora de recepción <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" name="fecha_entrada"
                                   class="form-control" required
                                   value="<?= date('Y-m-d\TH:i') ?>">
                        </div>

                        <!-- Observaciones -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observaciones / Motivo del pase</label>
                            <textarea name="observaciones" class="form-control" rows="3"
                                      placeholder="Ej: Se eleva al área de Autoridad con el sumario completo. / Se suspende por licencia del actuante..."
                                      maxlength="1000"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-arrow-right-circle me-1"></i>Confirmar pase
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
