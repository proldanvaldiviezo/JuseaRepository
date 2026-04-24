<?php
/**
 * Vista: Formulario Alta / Edición — Padrón de Personal CMN
 * Variables recibidas: $persona (null = nuevo, object = editar), $grados (array)
 */
$esEdicion = $persona !== null;
$titulo    = $esEdicion ? 'Editar Integrante' : 'Nuevo Integrante';
$accion    = $esEdicion
    ? site_url('personal/actualizar/' . $persona->id)
    : site_url('personal/guardar');

// Función auxiliar para recuperar valor del campo
function val(string $campo, ?object $persona, $default = ''): string {
    $old = old($campo);
    if ($old !== null) return esc($old);
    if ($persona !== null && isset($persona->$campo)) return esc($persona->$campo);
    return esc($default);
}

$tiposOpciones = [
    'cuadro'     => 'Personal de Cuadros (Oficial / Suboficial / Soldado)',
    'cadete'     => 'Cadete',
    'instructor' => 'Instructor / Cuadro formador',
    'autoridad'  => 'Autoridad / Firmante',
    'civil'      => 'Personal Civil',
];
?>

<!-- Encabezado -->
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:52px;height:52px;background:rgba(26,58,107,.12);">
        <i class="bi bi-person-<?= $esEdicion ? 'gear' : 'plus-fill' ?> fs-4" style="color:var(--ea-blue)"></i>
    </div>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem;"><?= $titulo ?> — Padrón CMN</h2>
        <p class="text-muted mb-0 small">
            <?= $esEdicion
                ? 'Modifique los datos del integrante y guarde los cambios.'
                : 'Complete los datos del nuevo integrante.' ?>
        </p>
    </div>
</div>

<form action="<?= $accion ?>" method="post" class="needs-validation" novalidate>
    <?= csrf_field() ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header card-header-blue">
            <i class="bi bi-person-vcard"></i> Datos del Integrante
        </div>
        <div class="card-body">

            <!-- Tipo (primero — condiciona grados disponibles) -->
            <div class="row g-4 mb-3">
                <div class="col-md-4">
                    <label for="tipo" class="form-label fw-semibold">
                        Tipo de personal <span class="text-danger">*</span>
                    </label>
                    <select name="tipo" id="tipo" class="form-select" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($tiposOpciones as $k => $v): ?>
                        <option value="<?= $k ?>"
                            <?= val('tipo', $persona) === $k ? 'selected' : '' ?>>
                            <?= esc($v) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Seleccione el tipo de personal.</div>
                </div>
                <div class="col-md-4">
                    <label for="grado" class="form-label fw-semibold">Grado / Jerarquía</label>
                    <input type="text" name="grado" id="grado"
                           class="form-control"
                           list="listGrados"
                           value="<?= val('grado', $persona) ?>"
                           placeholder="Ej: TC, MY, Cap, Sargento..."
                           maxlength="80" autocomplete="off">
                    <datalist id="listGrados">
                        <?php foreach ($grados as $abrev => $desc): ?>
                        <option value="<?= esc($abrev) ?>"><?= esc($desc) ?></option>
                        <?php endforeach; ?>
                    </datalist>
                    <div class="form-text">Puede escribir o seleccionar de la lista.</div>
                </div>
                <div class="col-md-4">
                    <label for="arma_especialidad" class="form-label fw-semibold">Arma / Servicio / Especialidad</label>
                    <input type="text" name="arma_especialidad" id="arma_especialidad"
                           class="form-control"
                           value="<?= val('arma_especialidad', $persona) ?>"
                           placeholder="Ej: I, C, A, Ing, S"
                           maxlength="100">
                </div>
            </div>

            <!-- Apellido y Nombre -->
            <div class="row g-4 mb-3">
                <div class="col-md-4">
                    <label for="apellido" class="form-label fw-semibold">
                        Apellido <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="apellido" id="apellido"
                           class="form-control text-uppercase"
                           value="<?= val('apellido', $persona) ?>"
                           placeholder="APELLIDO"
                           minlength="2" maxlength="100" required
                           oninput="this.value=this.value.toUpperCase()">
                    <div class="invalid-feedback">Ingrese el apellido.</div>
                </div>
                <div class="col-md-4">
                    <label for="nombre" class="form-label fw-semibold">Nombre(s)</label>
                    <input type="text" name="nombre" id="nombre"
                           class="form-control text-uppercase"
                           value="<?= val('nombre', $persona) ?>"
                           placeholder="NOMBRE"
                           maxlength="100"
                           oninput="this.value=this.value.toUpperCase()">
                </div>
                <div class="col-md-4">
                    <label for="dni" class="form-label fw-semibold">
                        DNI <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="dni" id="dni"
                           class="form-control font-monospace"
                           value="<?= val('dni', $persona) ?>"
                           placeholder="Sin puntos ni espacios"
                           minlength="7" maxlength="10"
                           data-solo-numeros required>
                    <div class="invalid-feedback">DNI válido: 7 a 10 dígitos numéricos.</div>
                </div>
            </div>

            <!-- Destino y Cargo -->
            <div class="row g-4 mb-3">
                <div class="col-md-6">
                    <label for="destino_interno" class="form-label fw-semibold">Destino Interno</label>
                    <input type="text" name="destino_interno" id="destino_interno"
                           class="form-control"
                           value="<?= val('destino_interno', $persona) ?>"
                           placeholder="Ej: Estado Mayor, Compañía A, División Jurídica"
                           maxlength="150">
                    <div class="form-text">Dependencia o subunidad dentro del CMN.</div>
                </div>
                <div class="col-md-6">
                    <label for="cargo" class="form-label fw-semibold">Cargo / Función</label>
                    <input type="text" name="cargo" id="cargo"
                           class="form-control"
                           value="<?= val('cargo', $persona) ?>"
                           placeholder="Ej: Jefe de Estado Mayor, Director, Oficial de Guardia"
                           maxlength="150">
                    <div class="form-text">Cargo actual — relevante para la firma de documentos.</div>
                </div>
            </div>

            <!-- Estado activo -->
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo"
                               value="1"
                               <?= (val('activo', $persona, '1') === '1' || (!$esEdicion && old('activo') === null)) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="activo">
                            Integrante activo
                        </label>
                        <div class="form-text">Desactivar oculta al integrante de las búsquedas en los formularios.</div>
                    </div>
                </div>
            </div>

        </div><!-- /card-body -->
    </div><!-- /card -->

    <div class="d-flex gap-2 justify-content-between">
        <a href="<?= site_url('personal') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Padrón
        </a>
        <button type="submit" class="btn btn-ea-blue btn-lg">
            <i class="bi bi-check-circle-fill"></i>
            <?= $esEdicion ? 'Guardar cambios' : 'Registrar integrante' ?>
        </button>
    </div>

</form>
