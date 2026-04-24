<?php
/**
 * Vista: Configuración de Permisos
 * Tabla interactiva de checkboxes — exclusivo admins.
 *
 * Variables recibidas:
 *   $permisos  array<accion, [etiqueta, admin, jefe, operador, consulta]>
 *   $roles     array<clave, [label, badge, desc]>  (catálogo de UsuarioModel)
 */

// Metadatos de columnas (orden visual + estilos)
$columnas = [
    'admin'    => ['label' => 'Admin',     'bg' => '#6b0f1a', 'icon' => 'bi-shield-fill-exclamation', 'fijo' => true ],
    'jefe'     => ['label' => 'Jefe',      'bg' => '#1a3a6b', 'icon' => 'bi-star-fill',               'fijo' => false],
    'operador' => ['label' => 'Operador',  'bg' => '#1b5e20', 'icon' => 'bi-person-gear',             'fijo' => false],
    'consulta' => ['label' => 'Consulta',  'bg' => '#37474f', 'icon' => 'bi-eye-fill',                'fijo' => false],
];

// Grupos de acciones para separadores visuales
$grupos = [
    'sanciones'  => ['label' => 'Sanciones disciplinarias', 'acciones' => ['sancion_crear', 'sancion_eliminar']],
    'actuacion'  => ['label' => 'Actuaciones administrativas', 'acciones' => ['actuacion_crear', 'actuacion_eliminar']],
    'historial'  => ['label' => 'Historial', 'acciones' => ['historial_ver', 'historial_exportar']],
    'padron'     => ['label' => 'Padrón CMN', 'acciones' => ['padron_ver', 'padron_exportar', 'padron_editar', 'padron_importar', 'accion_masiva']],
    'admin'      => ['label' => 'Administración del sistema', 'acciones' => ['encabezado', 'usuarios_gestionar', 'permisos_gestionar']],
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0"><i class="bi bi-shield-check text-warning"></i> Configuración de Permisos</h4>
        <small class="text-muted">Defina qué puede hacer cada rol. Los cambios aplican en el próximo inicio de sesión del usuario.</small>
    </div>
    <a href="<?= site_url('usuarios') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Usuarios
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <form method="post" action="<?= site_url('permisos/guardar') ?>">
            <?= csrf_field() ?>

            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="tablaPermisos">
                    <thead>
                        <tr>
                            <th class="align-middle" style="min-width:220px; background:#2c2c2c; color:#c9a227; border-color:#444;">
                                Función
                            </th>
                            <?php foreach ($columnas as $col => $meta): ?>
                            <th class="text-center align-middle"
                                style="min-width:110px; background:<?= $meta['bg'] ?>; color:#fff; border-color:#444;">
                                <i class="bi <?= $meta['icon'] ?>"></i><br>
                                <?= $meta['label'] ?>
                                <?php if ($meta['fijo']): ?>
                                <br><small style="font-size:.65em;opacity:.75;">(siempre activo)</small>
                                <?php endif; ?>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($grupos as $grpKey => $grupo): ?>

                        <!-- Separador de grupo -->
                        <tr style="background:#f0f4f8;">
                            <td colspan="5" class="py-1 px-3">
                                <small class="fw-bold text-uppercase" style="color:#1a3a6b;letter-spacing:.06em;font-size:.72rem;">
                                    <i class="bi bi-chevron-right"></i> <?= $grupo['label'] ?>
                                </small>
                            </td>
                        </tr>

                        <?php foreach ($grupo['acciones'] as $accion):
                            if (!isset($permisos[$accion])) continue;
                            $perm = $permisos[$accion];
                        ?>
                        <tr class="fila-permiso" data-accion="<?= esc($accion) ?>">
                            <td class="align-middle ps-4" style="font-size:.9rem;">
                                <?= esc($perm['etiqueta']) ?>
                            </td>

                            <?php foreach ($columnas as $col => $meta): ?>
                            <td class="text-center align-middle" style="background:<?= $meta['fijo'] ? '#fff5f5' : '#fff' ?>;">
                                <?php if ($meta['fijo']): ?>
                                    <!-- Admin: siempre marcado, no editable -->
                                    <i class="bi bi-check-circle-fill text-danger fs-5"
                                       title="El administrador siempre tiene este permiso"></i>
                                    <input type="hidden"
                                           name="permisos[<?= $accion ?>][admin]"
                                           value="1">
                                <?php else: ?>
                                    <div class="form-check d-flex justify-content-center m-0">
                                        <input class="form-check-input fs-5 perm-check"
                                               type="checkbox"
                                               name="permisos[<?= $accion ?>][<?= $col ?>]"
                                               value="1"
                                               id="perm_<?= $accion ?>_<?= $col ?>"
                                               <?= !empty($perm[$col]) ? 'checked' : '' ?>
                                               title="<?= esc($meta['label']) ?>: <?= esc($perm['etiqueta']) ?>">
                                    </div>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>

                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div><!-- /.table-responsive -->

            <!-- Barra de acciones -->
            <div class="p-3 border-top d-flex justify-content-between align-items-center"
                 style="background:#faf7f2;">
                <div class="text-muted small">
                    <i class="bi bi-info-circle"></i>
                    La columna <strong>Admin</strong> no es editable: el administrador siempre tiene acceso total.
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnReset">
                        <i class="bi bi-arrow-counterclockwise"></i> Restaurar valores
                    </button>
                    <button type="submit" class="btn btn-ea">
                        <i class="bi bi-save2"></i> Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Leyenda de roles -->
<div class="row mt-3 g-2">
    <?php foreach ($columnas as $col => $meta): ?>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                <span class="badge rounded-pill" style="background:<?= $meta['bg'] ?>; font-size:.8rem; padding:.4em .7em;">
                    <i class="bi <?= $meta['icon'] ?>"></i> <?= $meta['label'] ?>
                </span>
                <small class="text-muted">
                    <?= $roles[$col]['desc'] ?? '' ?>
                </small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
// ── Guardar estado original para restaurar ────────────────────────────────
const estadoOriginal = {};
document.querySelectorAll('.perm-check').forEach(cb => {
    estadoOriginal[cb.name] = cb.checked;
});

document.getElementById('btnReset').addEventListener('click', () => {
    document.querySelectorAll('.perm-check').forEach(cb => {
        cb.checked = estadoOriginal[cb.name] ?? false;
    });
});

// ── Resaltar filas modificadas ────────────────────────────────────────────
document.querySelectorAll('.perm-check').forEach(cb => {
    cb.addEventListener('change', () => {
        const fila = cb.closest('tr.fila-permiso');
        const cambios = [...fila.querySelectorAll('.perm-check')]
            .some(c => c.checked !== estadoOriginal[c.name]);
        fila.style.background = cambios ? '#fffde7' : '';
    });
});

// ── Confirmar antes de guardar si hay cambios ─────────────────────────────
document.querySelector('form').addEventListener('submit', function(e) {
    const hayCambios = document.querySelectorAll('tr.fila-permiso[style*="fffde7"]').length > 0;
    if (hayCambios) {
        if (!confirm('¿Confirma guardar los cambios en la matriz de permisos?\n\nLos usuarios afectados verán los nuevos permisos en su próximo inicio de sesión.')) {
            e.preventDefault();
        }
    }
});
</script>
