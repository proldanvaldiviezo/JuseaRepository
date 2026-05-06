<?php
/**
 * Vista: Padrón de Personal CMN — Listado (solo lectura, datos desde AspNetUsers)
 * Variables: $personas, $stats, $q, $arma, $armasList, $activo, $total, $perPage, $page
 */
?>

<!-- Encabezado -->
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:52px;height:52px;background:rgba(26,58,107,.12);">
        <i class="bi bi-people-fill fs-4" style="color:var(--ea-blue)"></i>
    </div>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem;">Padrón de Personal CMN</h2>
        <p class="text-muted mb-0 small">Base de datos del personal — datos gestionados desde el sistema Partes</p>
    </div>
</div>

<!-- Estadísticas -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="<?= site_url('personal?activo=1') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center h-100" style="border-top:3px solid var(--ea-blue)!important;cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div class="fs-3 fw-bold" style="color:var(--ea-blue)"><?= $stats['total'] ?></div>
                    <div class="small text-muted">Activos</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= site_url('personal?activo=0') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center h-100" style="border-top:3px solid #dc3545!important;cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div class="fs-3 fw-bold text-danger"><?= $stats['inactivos'] ?></div>
                    <div class="small text-muted">Inactivos</div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= site_url('personal') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label form-label-sm mb-1">Buscar</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Apellido, nombre, grado, DNI..."
                       value="<?= esc($q) ?>" autocomplete="off">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label form-label-sm mb-1">Arma / Especialidad</label>
                <select name="arma" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach($armasList as $a): ?>
                    <option value="<?= esc($a) ?>" <?= $arma === $a ? 'selected' : '' ?>><?= esc($a) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label form-label-sm mb-1">Estado</label>
                <select name="activo" class="form-select form-select-sm">
                    <option value="1" <?= $activo === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= $activo === '0' ? 'selected' : '' ?>>Inactivos</option>
                    <option value=""  <?= $activo === ''  ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>
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

<!-- Barra de acciones -->
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
    <div class="text-muted small">
        <i class="bi bi-list-ul"></i>
        <strong><?= $total ?></strong> registro(s) encontrado(s)
        <?php if ($arma): ?> · Arma: <strong><?= esc($arma) ?></strong><?php endif; ?>
    </div>
    <a href="<?= site_url('personal/exportar?' . http_build_query(array_filter(['q' => $q, 'arma' => $arma, 'activo' => $activo], fn($v) => $v !== ''))) ?>"
       class="btn btn-sm btn-outline-secondary" title="Exportar lista actual a CSV">
        <i class="bi bi-download"></i> Exportar CSV
    </a>
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

<!-- Tabla -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-blue table-hover table-sm mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width:95px">DNI</th>
                        <th style="width:80px">Grado</th>
                        <th>Apellido y Nombre</th>
                        <th class="d-none d-lg-table-cell">Arma / Esp.</th>
                        <th style="width:80px">Estado</th>
                        <th style="width:70px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($personas)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>
                            No se encontraron registros con los filtros actuales.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($personas as $p):
                        $activo = !($p->bajaUnidad ?? false);
                    ?>
                    <tr class="<?= !$activo ? 'table-secondary opacity-75' : '' ?>">
                        <td class="font-monospace small"><?= esc($p->dni) ?></td>
                        <td class="fw-semibold small"><?= esc($p->grado ?? '') ?></td>
                        <td>
                            <span class="fw-semibold"><?= esc($p->apellido) ?></span>
                            <?php if (!empty($p->nombre)): ?>
                            <span class="text-muted">, <?= esc($p->nombre) ?></span>
                            <?php endif; ?>
                            <?php if (!$activo): ?>
                            <span class="badge bg-danger ms-1 small">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="small d-none d-lg-table-cell text-muted"><?= esc($p->arma_especialidad ?? '—') ?></td>
                        <td>
                            <?php if ($activo): ?>
                            <span class="badge bg-success small">Activo</span>
                            <?php else: ?>
                            <span class="badge bg-danger small">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= site_url('personal/historial/' . $p->id) ?>"
                               class="btn btn-xs btn-outline-primary" title="Ver historial">
                                <i class="bi bi-clock-history"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Paginación manual -->
<?php
$totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
$qp = http_build_query(array_filter(['q' => $q, 'arma' => $arma, 'activo' => $activo], fn($v) => $v !== ''));
?>
<?php if ($totalPages > 1): ?>
<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
    <div class="text-muted small">
        Mostrando hasta <?= $perPage ?> de <?= $total ?> registros — página <?= $page ?> de <?= $totalPages ?>
    </div>
    <nav>
        <ul class="pagination pagination-sm mb-0">
            <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= site_url('personal?' . ($qp ? $qp . '&' : '') . 'page=' . ($page - 1)) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= site_url('personal?' . ($qp ? $qp . '&' : '') . 'page=' . $i) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="<?= site_url('personal?' . ($qp ? $qp . '&' : '') . 'page=' . ($page + 1)) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<?php endif; ?>
