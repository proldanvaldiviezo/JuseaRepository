<h2 class="mb-1">Panel Principal</h2>
<p class="text-muted mb-4">Seleccione el módulo con el que desea trabajar:</p>

<div class="row g-4">

    <!-- ── Sancion Disciplinaria — Cuadros ─────────────────────────────── -->
    <div class="col-md-4">
        <a href="<?= site_url('sancion/cuadros') ?>" class="text-decoration-none">
            <div class="card card-modulo h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-file-earmark-text"></i>
                    <h5 class="mt-3 text-dark">Sanción Disciplinaria</h5>
                    <p class="text-muted small mb-0">Cuadros — Imposición directa</p>
                </div>
            </div>
        </a>
    </div>

    <!-- ── Sancion Disciplinaria — Cadetes ─────────────────────────────── -->
    <div class="col-md-4">
        <a href="<?= site_url('sancion/cadetes') ?>" class="text-decoration-none">
            <div class="card card-modulo h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-file-earmark-text"></i>
                    <h5 class="mt-3 text-dark">Sanción Disciplinaria</h5>
                    <p class="text-muted small mb-0">Cadetes — Imposición directa</p>
                </div>
            </div>
        </a>
    </div>

    <!-- ── Historial de Sanciones ───────────────────────────────────────── -->
    <div class="col-md-4">
        <a href="<?= site_url('historial') ?>" class="text-decoration-none">
            <div class="card card-modulo h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-clock-history"></i>
                    <h5 class="mt-3 text-dark">Historial de Sanciones</h5>
                    <p class="text-muted small mb-0">Búsqueda por DNI o apellido</p>
                </div>
            </div>
        </a>
    </div>

    <!-- ── Bienes del Estado ────────────────────────────────────────────── -->
    <div class="col-md-4">
        <div class="card card-modulo card-modulo-blue h-100">
            <div class="card-body text-center py-4 d-flex flex-column">
                <div class="flex-grow-1">
                    <i class="bi bi-box-seam"></i>
                    <h5 class="mt-3 text-dark">Bienes del Estado</h5>
                    <p class="text-muted small mb-3">Pérdida, ruptura o inutilización</p>
                </div>
                <div class="d-grid gap-2">
                    <a href="<?= site_url('actuacion/bienes') ?>" class="btn btn-ea-blue btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Nueva Actuación
                    </a>
                    <a href="<?= site_url('actuacion/bienes/historial') ?>" class="btn btn-outline-blue btn-sm">
                        <i class="bi bi-list-ul me-1"></i>Historial
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Accidente / Enfermedad ───────────────────────────────────────── -->
    <div class="col-md-4">
        <div class="card card-modulo card-modulo-blue h-100">
            <div class="card-body text-center py-4 d-flex flex-column">
                <div class="flex-grow-1">
                    <i class="bi bi-heart-pulse"></i>
                    <h5 class="mt-3 text-dark">Accidente / Enfermedad</h5>
                    <p class="text-muted small mb-3">Actuación por accidente o afección</p>
                </div>
                <div class="d-grid gap-2">
                    <a href="<?= site_url('actuacion/accidente') ?>" class="btn btn-ea-blue btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Nueva Actuación
                    </a>
                    <a href="<?= site_url('actuacion/accidente/historial') ?>" class="btn btn-outline-blue btn-sm">
                        <i class="bi bi-list-ul me-1"></i>Historial
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Padrón de Personal CMN ──────────────────────────────────────── -->
    <div class="col-md-4">
        <div class="card card-modulo card-modulo-blue h-100">
            <div class="card-body text-center py-4 d-flex flex-column">
                <div class="flex-grow-1">
                    <i class="bi bi-people-fill"></i>
                    <h5 class="mt-3 text-dark">Padrón de Personal</h5>
                    <p class="text-muted small mb-3">Base de datos del personal CMN</p>
                </div>
                <div class="d-grid gap-2">
                    <a href="<?= site_url('personal') ?>" class="btn btn-ea-blue btn-sm">
                        <i class="bi bi-list-ul me-1"></i>Ver Padrón
                    </a>
                    <?php if (session()->get('usuario_rol') === 'admin'): ?>
                    <a href="<?= site_url('personal/nuevo') ?>" class="btn btn-outline-blue btn-sm">
                        <i class="bi bi-person-plus me-1"></i>Nuevo Integrante
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Configurar Encabezado (solo admin) ──────────────────────────── -->
    <?php if (session()->get('usuario_rol') === 'admin'): ?>
    <div class="col-md-4">
        <a href="<?= site_url('encabezado') ?>" class="text-decoration-none">
            <div class="card card-modulo h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-gear"></i>
                    <h5 class="mt-3 text-dark">Configurar Encabezado</h5>
                    <p class="text-muted small mb-0">Año, membrete y unidad</p>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>

</div>
