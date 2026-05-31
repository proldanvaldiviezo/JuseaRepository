<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo ?? 'JUSEA CMN') ?></title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --ea-crimson:     #6b0f1a;   /* carmesí profundo — navbar */
            --ea-crimson-dk:  #4a0810;   /* hover navbar / sidebar */
            --ea-crimson-int: #8B1520;   /* carmesí interior logo — botones, íconos */
            --ea-gold:        #c9a227;   /* dorado logo */
            --ea-gold-light:  #e8c84a;   /* dorado claro */
            --ea-blue:        #1a3a6b;   /* azul CMN profundo — actuaciones */
            --ea-blue-md:     #2557a7;   /* azul medio — hover actuaciones */
            --ea-blue-lt:     #dde8f5;   /* azul claro — fondos, badges */
        }
        body { background-color: #faf7f2; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        /* ── NAVBAR ── */
        .navbar-ea { background: linear-gradient(90deg, var(--ea-crimson) 0%, #5a0d16 100%) !important; border-bottom: 2px solid var(--ea-gold); }
        .navbar-ea .navbar-brand { color: #fff !important; }
        /* ── SIDEBAR ── */
        .sidebar { min-height: calc(100vh - 56px); background: linear-gradient(180deg, #1e0507 0%, #1a1f2e 55%, #0d1e38 100%); border-right: 1px solid rgba(201,162,39,.15); }
        .sidebar .nav-link { color: #c8c8d8; padding: 0.75rem 1rem; border-left: 3px solid transparent; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background: rgba(201,162,39,.08);
            border-left-color: var(--ea-gold);
        }
        .sidebar .nav-link.actuacion-link:hover, .sidebar .nav-link.actuacion-link.active {
            background: rgba(37,87,167,.15);
            border-left-color: var(--ea-blue-md);
        }
        .sidebar .nav-link i { margin-right: 0.5rem; width: 20px; text-align: center; }
        .sidebar .section-label-blue { color: #6ea8fe !important; opacity: .85; }
        /* ── CONTENIDO ── */
        .content-area { padding: 1.5rem; }
        .card-modulo { transition: transform 0.2s; cursor: pointer; border: none; box-shadow: 0 2px 8px rgba(107,15,26,.12); }
        .card-modulo:hover { transform: translateY(-4px); box-shadow: 0 4px 16px rgba(107,15,26,.2); }
        .card-modulo .card-body i { font-size: 2.5rem; color: var(--ea-crimson-int); }
        /* Cards de actuaciones — acento azul CMN */
        .card-modulo-blue { border-top: 3px solid var(--ea-blue) !important; box-shadow: 0 2px 8px rgba(26,58,107,.13); }
        .card-modulo-blue:hover { box-shadow: 0 4px 16px rgba(26,58,107,.25); }
        .card-modulo-blue .card-body i { color: var(--ea-blue); }
        /* ── BOTONES ── */
        .btn-ea { background-color: var(--ea-crimson-int); border-color: var(--ea-crimson-int); color: #fff; }
        .btn-ea:hover { background-color: var(--ea-crimson-dk); border-color: var(--ea-crimson-dk); color: #fff; }
        .btn-ea-blue { background-color: var(--ea-blue); border-color: var(--ea-blue); color: #fff; }
        .btn-ea-blue:hover { background-color: var(--ea-blue-md); border-color: var(--ea-blue-md); color: #fff; }
        .btn-outline-blue { border-color: var(--ea-blue); color: var(--ea-blue); }
        .btn-outline-blue:hover { background-color: var(--ea-blue-lt); color: var(--ea-blue); border-color: var(--ea-blue); }
        .badge-estado { font-size: 0.85em; }
        /* ── CARD HEADERS ── */
        .card-header { background: linear-gradient(90deg, var(--ea-crimson) 0%, #8B1520 100%); color: #fff; border-bottom: 2px solid var(--ea-gold); }
        .card-header-blue { background: linear-gradient(90deg, var(--ea-blue) 0%, var(--ea-blue-md) 100%) !important; border-bottom-color: var(--ea-gold) !important; }
        /* ── TABLA header ── */
        .table thead th { background-color: var(--ea-crimson); color: #fff; border-color: rgba(201,162,39,.3); }
        .table-blue thead th { background-color: var(--ea-blue) !important; border-color: rgba(201,162,39,.25) !important; }
        /* ── Badge azul para actuaciones ── */
        .badge-actuacion { background-color: var(--ea-blue-lt); color: var(--ea-blue); font-weight: 600; }
        /* ── TEXTO MUTED en sidebar ── */
        .sidebar .text-muted { color: #a08080 !important; }
    </style>
</head>
<body>
    <!-- Navbar superior -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-ea">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-decoration-none" href="<?= site_url('dashboard') ?>">
                <!-- Escudo CMN División Jurídica -->
                <img src="data:image/png;base64,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" height="40" alt="Escudo CMN División Jurídica" style="flex-shrink:0;filter:drop-shadow(0 1px 3px rgba(0,0,0,.5));">
                <span>
                    JUSEA CMN
                    <span class="d-block fw-normal" style="font-size:.62em;opacity:.82;line-height:1;letter-spacing:.04em;">División Jurídica</span>
                </span>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">
                    <i class="bi bi-person-circle"></i>
                    <?= esc(session()->get('usuario_nombre') ?? '') ?>
                    <span class="badge bg-warning text-dark ms-1"><?= esc(ucfirst(session()->get('usuario_rol') ?? '')) ?></span>
                </span>
                <a href="<?= site_url('logout') ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar py-3">
                <?php
                // Permisos dinámicos desde la BD (un solo query cacheado por request)
                $_pCrearSancion   = \App\Models\UsuarioModel::puedeActual('sancion_crear');
                $_pVerHistorial   = \App\Models\UsuarioModel::puedeActual('historial_ver');
                $_pCrearActuacion = \App\Models\UsuarioModel::puedeActual('actuacion_crear');
                $_pVerPadron      = \App\Models\UsuarioModel::puedeActual('padron_ver');
                $_pEncabezado     = \App\Models\UsuarioModel::puedeActual('encabezado');
                $_pUsuarios       = \App\Models\UsuarioModel::puedeActual('usuarios_gestionar');
                $_pPermisos       = \App\Models\UsuarioModel::puedeActual('permisos_gestionar');
                ?>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('dashboard') ?>">
                            <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>

                    <?php if ($_pCrearSancion): ?>
                    <li class="nav-item mt-2">
                        <small class="px-3" style="color:#c9a227;opacity:.85;font-size:.70rem;text-transform:uppercase;letter-spacing:.07em;font-weight:600;">Sanciones</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('sancion/cuadros') ?>">
                            <i class="bi bi-file-earmark-text"></i> Cuadros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('sancion/cadetes') ?>">
                            <i class="bi bi-file-earmark-text"></i> Cadetes
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($_pVerHistorial): ?>
                    <li class="nav-item <?= $_pCrearSancion ? '' : 'mt-2' ?>">
                        <a class="nav-link" href="<?= site_url('historial') ?>">
                            <i class="bi bi-clock-history"></i> Historial
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($_pCrearActuacion): ?>
                    <li class="nav-item mt-2">
                        <small class="section-label-blue px-3">Actuaciones</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link actuacion-link" href="<?= site_url('actuacion/bienes') ?>">
                            <i class="bi bi-box-seam"></i> Bienes del Estado
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link actuacion-link" href="<?= site_url('actuacion/accidente') ?>">
                            <i class="bi bi-heart-pulse"></i> Accidente / Enf.
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($_pVerPadron): ?>
                    <li class="nav-item mt-2">
                        <small class="px-3" style="color:#c9a227;opacity:.85;font-size:.70rem;text-transform:uppercase;letter-spacing:.07em;font-weight:600;">Personal</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('personal') ?>">
                            <i class="bi bi-people-fill"></i> Padrón CMN
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($_pEncabezado || $_pUsuarios || $_pPermisos): ?>
                    <li class="nav-item mt-2">
                        <small class="text-muted px-3" style="font-size:.70rem;text-transform:uppercase;letter-spacing:.07em;">Administración</small>
                    </li>
                    <?php if ($_pEncabezado): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('encabezado') ?>">
                            <i class="bi bi-gear"></i> Encabezado
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($_pUsuarios): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('usuarios') ?>">
                            <i class="bi bi-people"></i> Usuarios
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($_pPermisos): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('permisos') ?>">
                            <i class="bi bi-shield-check"></i> Permisos
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <!-- Crédito División Jurídica -->
                <div class="px-2 py-3 text-center" style="margin-top:auto;border-top:1px solid rgba(255,255,255,.08);">
                    <small class="text-muted d-block" style="font-size:.62em;line-height:1.5;opacity:.6;">
                        Desarrollo<br>
                        <span style="color:#c9a227;opacity:.85;">División Jurídica</span><br>
                        Colegio Militar de la Nación
                    </small>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main class="col-md-10 content-area">
                <!-- Alertas flash -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <?= esc(session()->getFlashdata('error')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Errores de validación:</strong>
                        <ul class="mb-0 mt-1">
                            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                                <li><?= esc($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Botón de descarga si hay sancion_id -->
                <?php
                $sancionId   = session()->getFlashdata('sancion_id');
                $sancionTipo = session()->getFlashdata('sancion_tipo') ?? 'cuadros';
                $rutaDescarga = ($sancionTipo === 'cadetes') ? 'sancion/cadetes/descargar' : 'sancion/cuadros/descargar';
                ?>
                <?php if ($sancionId): ?>
                    <div class="alert alert-info d-flex align-items-center flex-wrap gap-2">
                        <strong><i class="bi bi-download"></i> Planilla ANEXO 1:</strong>
                        <a href="<?= site_url($rutaDescarga . '/' . $sancionId . '/docx') ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-file-earmark-word"></i> Word
                        </a>
                        <a href="<?= site_url($rutaDescarga . '/' . $sancionId . '/pdf') ?>" class="btn btn-sm btn-danger">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                        <?php if ($sancionTipo === 'cuadros'): ?>
                            &nbsp;|&nbsp;<strong><i class="bi bi-file-earmark-ruled"></i> Planilla ANEXO 2 (Revisión de Oficio):</strong>
                            <a href="<?= site_url('sancion/cuadros/revision/' . $sancionId) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-word"></i> Word
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?= $contenido ?? '' ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Validación client-side -->
    <script>
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // Solo números para campos DNI
    document.querySelectorAll('input[data-solo-numeros]').forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) e.preventDefault();
        });
        input.addEventListener('paste', function(e) { e.preventDefault(); });
    });
    </script>
</body>
</html>
