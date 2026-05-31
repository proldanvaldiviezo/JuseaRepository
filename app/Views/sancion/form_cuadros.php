<h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> Sanción Disciplinaria - Cuadros</h2>
<p class="text-muted">Imposición directa - Art. 9/10 CDFFAA</p>

<form action="<?= site_url('sancion/cuadros/guardar') ?>" method="post" class="needs-validation" novalidate id="formSancion">
    <?= csrf_field() ?>
    <input type="hidden" name="tipo_personal" value="CUADRO">

    <!-- IDENTIFICACIÓN DEL DOCUMENTO -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-2">
            <div class="row g-3 align-items-end">
                <div class="col-auto">
                    <span class="text-muted small fw-semibold"><i class="bi bi-file-earmark-text"></i> Identificación del documento</span>
                </div>
                <div class="col-md-2">
                    <label for="letra" class="form-label form-label-sm mb-1">Letra</label>
                    <input type="text" class="form-control form-control-sm" id="letra" name="letra"
                           value="<?= esc(old('letra') ?? '') ?>"
                           placeholder="Ej: A, B, C"
                           maxlength="10">
                    <div class="form-text" style="font-size:0.72rem;">Carácter que identifica el expediente</div>
                </div>
                <div class="col-md-3">
                    <label for="nro" class="form-label form-label-sm mb-1">Nro.</label>
                    <input type="text" class="form-control form-control-sm" id="nro" name="nro"
                           value="<?= esc(old('nro') ?? '') ?>"
                           placeholder="Ej: 001/2026"
                           maxlength="20">
                    <div class="form-text" style="font-size:0.72rem;">Número correlativo del acto</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABS -->
    <ul class="nav nav-tabs mb-4" id="sancionTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="tab-infractor" data-bs-toggle="tab" data-bs-target="#infractor" type="button">
                <i class="bi bi-person"></i> A. Infractor
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-sancion" data-bs-toggle="tab" data-bs-target="#sancion" type="button">
                <i class="bi bi-exclamation-triangle"></i> B. Sanción
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-autoridad" data-bs-toggle="tab" data-bs-target="#autoridad" type="button">
                <i class="bi bi-person-badge"></i> C. Autoridad
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-revisor" data-bs-toggle="tab" data-bs-target="#revisor" type="button">
                <i class="bi bi-person-check"></i> D. Revisor
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB A: DATOS DEL INFRACTOR -->
        <div class="tab-pane fade show active" id="infractor">
            <div class="card">
                <div class="card-header bg-light"><strong>A. Datos del Infractor</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- DNI como campo principal de búsqueda -->
                        <div class="col-md-3">
                            <label for="dni_infractor" class="form-label">DNI <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="dni_infractor" name="dni_infractor"
                                       data-solo-numeros minlength="7" maxlength="10"
                                       value="<?= esc(old('dni_infractor') ?? '') ?>" required
                                       placeholder="Ingrese DNI">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarDni" title="Buscar por DNI">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">DNI válido (7-10 dígitos).</div>
                        </div>
                        <!-- Búsqueda por Apellido -->
                        <div class="col-md-3 position-relative">
                            <label class="form-label">Buscar por Apellido</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscarApellido" placeholder="Escriba apellido..."
                                       autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarApellido" title="Buscar por Apellido">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div id="listaApellidos" class="list-group position-absolute w-100" style="z-index:1050; display:none; max-height:200px; overflow-y:auto;"></div>
                        </div>
                        <div class="col-md-3">
                            <label for="grado_infractor" class="form-label">Grado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="grado_infractor" name="grado_infractor"
                                   value="<?= esc(old('grado_infractor') ?? '') ?>" required
                                   placeholder="Ej: TC, MY, Cap">
                            <div class="invalid-feedback">Ingrese el grado.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="arma_infractor" class="form-label">Arma / Servicio / Esp.</label>
                            <input type="text" class="form-control" id="arma_infractor" name="arma_infractor"
                                   value="<?= esc(old('arma_infractor') ?? '') ?>"
                                   placeholder="Ej: I, C, A, Ing">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div id="busquedaResultado" class="form-text text-success" style="display:none;">
                                <i class="bi bi-check-circle"></i> Datos cargados
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido_infractor" class="form-label">Apellido y Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellido_infractor" name="apellido_infractor"
                                   value="<?= esc(old('apellido_infractor') ?? '') ?>" required minlength="2"
                                   placeholder="APELLIDO, Nombre">
                            <div class="invalid-feedback">Ingrese apellido y nombre.</div>
                        </div>
                        <input type="hidden" id="nombre_infractor" name="nombre_infractor" value="<?= esc(old('nombre_infractor') ?? '-') ?>">
                        <div class="col-md-6">
                            <label for="destino_infractor" class="form-label">Destino Interno</label>
                            <input type="text" class="form-control" id="destino_infractor" name="destino_infractor"
                                   value="<?= esc(old('destino_infractor') ?? '') ?>"
                                   placeholder="Ej: Estado Mayor, Agrupación Básica">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-ea" onclick="document.getElementById('tab-sancion').click()">
                    Siguiente <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- TAB B: SANCION -->
        <div class="tab-pane fade" id="sancion">
            <div class="card">
                <div class="card-header bg-light"><strong>B. Sanción Disciplinaria</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="fecha_comision" class="form-label">Fecha Comisión Falta <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_comision" name="fecha_comision"
                                   value="<?= esc(old('fecha_comision') ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="reg_act_dis" class="form-label">Art. CDFFAA</label>
                            <input type="text" class="form-control" id="reg_act_dis" name="reg_act_dis"
                                   value="<?= esc(old('reg_act_dis') ?? '') ?>"
                                   placeholder="Ej: Art. 9">
                        </div>
                        <div class="col-md-3">
                            <label for="inciso" class="form-label">Inciso</label>
                            <input type="text" class="form-control" id="inciso" name="inciso"
                                   value="<?= esc(old('inciso') ?? '') ?>"
                                   placeholder="Ej: Inc 3">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="w-100">
                                <button type="button" class="btn btn-outline-warning btn-sm" id="btnSugerirNormCuad">
                                    <i class="bi bi-journal-bookmark"></i> Sugerir Art./Inciso con IA
                                </button>
                                <div id="normStatusCuad" class="form-text mt-1" style="display:none;"></div>
                                <div id="normPreviewCuad" class="mt-1 border rounded p-2 bg-warning bg-opacity-10" style="display:none; font-size:0.85rem;">
                                    <strong id="normResultCuad"></strong><br>
                                    <span id="normJustCuad" class="text-muted"></span>
                                    <div class="mt-1 d-flex gap-2">
                                        <button type="button" class="btn btn-warning btn-sm" id="btnAceptarNormCuad">
                                            <i class="bi bi-check-lg"></i> Aplicar
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDescartarNormCuad">
                                            <i class="bi bi-x-lg"></i> Descartar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="motivo" class="form-label">Motivo (Descripción circunstanciada) <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="motivo" name="motivo" rows="5" required minlength="5"
                                      placeholder="Descripción circunstanciada de los hechos..."><?= esc(old('motivo') ?? '') ?></textarea>
                            <div class="invalid-feedback">Mínimo 5 caracteres.</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnMejorarIA">
                                    <i class="bi bi-stars"></i> Mejorar redacción con IA
                                </button>
                                <span id="iaStatusCuad" class="form-text" style="display:none;"></span>
                            </div>
                            <div id="iaPreviewCuad" class="mt-2 border rounded p-2 bg-light" style="display:none;">
                                <p class="mb-1 small text-muted fw-bold"><i class="bi bi-robot"></i> Texto sugerido por IA — revisalo antes de aceptar:</p>
                                <div id="iaTextoCuad" class="small" style="white-space: pre-wrap;"></div>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm" id="btnAceptarIA">
                                        <i class="bi bi-check-lg"></i> Aceptar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDescartarIA">
                                        <i class="bi bi-x-lg"></i> Descartar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="tipo_sancion_desc" class="form-label">Tipo de Sanción</label>
                            <select class="form-select" id="tipo_sancion_desc" name="tipo_sancion_desc">
                                <option value="Arresto Simple" <?= old('tipo_sancion_desc') === 'Arresto Simple' ? 'selected' : '' ?>>Arresto Simple</option>
                                <option value="Arresto Riguroso" <?= old('tipo_sancion_desc') === 'Arresto Riguroso' ? 'selected' : '' ?>>Arresto Riguroso</option>
                                <option value="Apercibimiento" <?= old('tipo_sancion_desc') === 'Apercibimiento' ? 'selected' : '' ?>>Apercibimiento</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="duracion" class="form-label">Duración (días)</label>
                            <input type="text" class="form-control" id="duracion" name="duracion"
                                   value="<?= esc(old('duracion') ?? '') ?>"
                                   placeholder="Ej: TREINTA Y CINCO (35)">
                            <small class="form-text text-muted">En letras y número</small>
                        </div>
                        <div class="col-md-4">
                            <label for="lugar_cumplimiento" class="form-label">Lugar de Cumplimiento</label>
                            <select class="form-select" id="lugar_cumplimiento" name="lugar_cumplimiento">
                                <option value="COLEGIO MILITAR DE LA NACION" <?= old('lugar_cumplimiento') !== 'DOMICILIO PARTICULAR' ? 'selected' : '' ?>>COLEGIO MILITAR DE LA NACION</option>
                                <option value="DOMICILIO PARTICULAR" <?= old('lugar_cumplimiento') === 'DOMICILIO PARTICULAR' ? 'selected' : '' ?>>DOMICILIO PARTICULAR</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('tab-infractor').click()">
                    <i class="bi bi-arrow-left"></i> Anterior
                </button>
                <button type="button" class="btn btn-ea" onclick="document.getElementById('tab-autoridad').click()">
                    Siguiente <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- TAB C: AUTORIDAD -->
        <div class="tab-pane fade" id="autoridad">
            <div class="card">
                <div class="card-header bg-light"><strong>C. Autoridad que Impone la Sanción</strong></div>
                <div class="card-body">
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle"></i> Busque por DNI o Apellido, o complete los campos directamente.</p>
                    <div class="row g-3">
                        <!-- Búsqueda DNI autoridad -->
                        <div class="col-md-3">
                            <label for="dni_instructor" class="form-label">DNI</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="dni_instructor" name="dni_instructor"
                                       data-solo-numeros maxlength="10"
                                       value="<?= esc(old('dni_instructor') ?? '') ?>"
                                       placeholder="Buscar por DNI">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarDniAut" title="Buscar por DNI">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Búsqueda Apellido autoridad -->
                        <div class="col-md-3 position-relative">
                            <label class="form-label">Buscar por Apellido</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscarApellidoAut" placeholder="Escriba apellido..."
                                       autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarApellidoAut" title="Buscar por Apellido">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div id="listaApellidosAut" class="list-group position-absolute w-100" style="z-index:1050; display:none; max-height:200px; overflow-y:auto;"></div>
                        </div>
                        <!-- Datos cargados -->
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <div id="busquedaAutResultado" class="form-text text-success" style="display:none;">
                                <i class="bi bi-check-circle"></i> Datos cargados desde BD
                            </div>
                        </div>
                        <!-- Campos editables -->
                        <div class="col-md-3">
                            <label for="grado_instructor" class="form-label">Grado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="grado_instructor" name="grado_instructor"
                                   value="<?= esc(old('grado_instructor') ?? '') ?>" required
                                   placeholder="Ej: CR, TC, MY, Coronel">
                        </div>
                        <div class="col-md-6">
                            <label for="apellido_instructor" class="form-label">Apellido y Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellido_instructor" name="apellido_instructor"
                                   value="<?= esc(old('apellido_instructor') ?? '') ?>" required minlength="2"
                                   placeholder="Ingrese apellido y nombre">
                        </div>
                        <input type="hidden" id="nombre_instructor" name="nombre_instructor" value="<?= esc(old('nombre_instructor') ?? '-') ?>">
                        <div class="col-md-3">
                            <label for="cargo_instructor" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="cargo_instructor" name="cargo_instructor"
                                   value="<?= esc(old('cargo_instructor') ?? '') ?>"
                                   placeholder="Ej: Director, Sub y JEM">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('tab-sancion').click()">
                    <i class="bi bi-arrow-left"></i> Anterior
                </button>
                <button type="button" class="btn btn-ea" onclick="document.getElementById('tab-revisor').click()">
                    Siguiente <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- TAB D: AUTORIDAD DE REVISIÓN -->
        <div class="tab-pane fade" id="revisor">
            <div class="card">
                <div class="card-header bg-light"><strong>D. Autoridad de Revisión (para Planilla ANEXO 2)</strong></div>
                <div class="card-body">
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle"></i> Busque por DNI o Apellido, o complete los campos directamente. Datos usados para emitir la Planilla de Revisión de Oficio (ANEXO 2).</p>
                    <div class="row g-3">
                        <!-- Búsqueda DNI revisor -->
                        <div class="col-md-3">
                            <label for="dni_revisor" class="form-label">DNI</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="dni_revisor" name="revisor_dni"
                                       data-solo-numeros maxlength="10"
                                       value="<?= esc(old('revisor_dni') ?? '') ?>"
                                       placeholder="Buscar por DNI">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarDniRev" title="Buscar por DNI">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Búsqueda Apellido revisor -->
                        <div class="col-md-3 position-relative">
                            <label class="form-label">Buscar por Apellido</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscarApellidoRev" placeholder="Escriba apellido..."
                                       autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarApellidoRev" title="Buscar por Apellido">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div id="listaApellidosRev" class="list-group position-absolute w-100" style="z-index:1050; display:none; max-height:200px; overflow-y:auto;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <div id="busquedaRevResultado" class="form-text text-success" style="display:none;">
                                <i class="bi bi-check-circle"></i> Datos cargados desde BD
                            </div>
                        </div>
                        <!-- Campos editables -->
                        <div class="col-md-3">
                            <label for="revisor_grado" class="form-label">Grado</label>
                            <input type="text" class="form-control" id="revisor_grado" name="revisor_grado"
                                   value="<?= esc(old('revisor_grado') ?? '') ?>"
                                   placeholder="Ej: CR, TC, General">
                        </div>
                        <div class="col-md-6">
                            <label for="revisor_nombre" class="form-label">Apellido y Nombre</label>
                            <input type="text" class="form-control" id="revisor_nombre" name="revisor_nombre"
                                   value="<?= esc(old('revisor_nombre') ?? '') ?>"
                                   placeholder="Ingrese apellido y nombre">
                        </div>
                        <div class="col-md-3">
                            <label for="revisor_cargo" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="revisor_cargo" name="revisor_cargo"
                                   value="<?= esc(old('revisor_cargo') ?? 'Director') ?>"
                                   placeholder="Ej: Director">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('tab-autoridad').click()">
                    <i class="bi bi-arrow-left"></i> Anterior
                </button>
                <button type="submit" class="btn btn-ea btn-lg">
                    <i class="bi bi-check-circle"></i> Guardar y Generar Documento
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Script de búsqueda por DNI y Apellido -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var urlDni = '<?= site_url('api/persona/buscar-dni') ?>';
    var urlApellido = '<?= site_url('api/persona/buscar-apellido') ?>';

    // === Función común: cargar datos del infractor en el formulario ===
    function cargarInfractor(data) {
        document.getElementById('dni_infractor').value = data.dni || '';
        document.getElementById('grado_infractor').value = data.grado || '';
        document.getElementById('apellido_infractor').value = data.apellido || '';
        document.getElementById('nombre_infractor').value = data.nombre || '-';
        document.getElementById('arma_infractor').value = data.arma_especialidad || '';
        document.getElementById('destino_infractor').value = data.destino_interno || '';
        document.getElementById('busquedaResultado').style.display = 'block';
    }

    // === Buscar infractor por DNI ===
    document.getElementById('btnBuscarDni').addEventListener('click', function() {
        var dni = document.getElementById('dni_infractor').value.trim();
        if (dni.length < 7) { alert('Ingrese un DNI válido (mín. 7 dígitos)'); return; }
        fetch(urlDni + '?dni=' + dni + '&tipo=cuadro')
            .then(r => r.json())
            .then(data => {
                if (data.found) { cargarInfractor(data.data); }
                else { document.getElementById('busquedaResultado').style.display = 'none'; alert('DNI no encontrado en BD de Cuadros.'); }
            })
            .catch(() => alert('Error de conexión'));
    });

    document.getElementById('dni_infractor').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnBuscarDni').click(); }
    });

    // === Buscar infractor por APELLIDO ===
    var listaDiv = document.getElementById('listaApellidos');

    function buscarPorApellido() {
        var q = document.getElementById('buscarApellido').value.trim();
        if (q.length < 2) { listaDiv.style.display = 'none'; return; }
        fetch(urlApellido + '?q=' + encodeURIComponent(q) + '&tipo=cuadro')
            .then(r => r.json())
            .then(resultados => {
                listaDiv.innerHTML = '';
                if (resultados.length === 0) {
                    listaDiv.innerHTML = '<span class="list-group-item text-muted">Sin resultados</span>';
                    listaDiv.style.display = 'block';
                    return;
                }
                resultados.forEach(function(p) {
                    var item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action py-1';
                    item.textContent = p.grado + ' ' + p.apellido + ' (DNI: ' + p.dni + ')';
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        cargarInfractor(p);
                        listaDiv.style.display = 'none';
                        document.getElementById('buscarApellido').value = '';
                    });
                    listaDiv.appendChild(item);
                });
                listaDiv.style.display = 'block';
            })
            .catch(() => { listaDiv.style.display = 'none'; });
    }

    document.getElementById('btnBuscarApellido').addEventListener('click', buscarPorApellido);
    document.getElementById('buscarApellido').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarPorApellido(); }
        else if (this.value.trim().length >= 3) { buscarPorApellido(); }
    });

    // Cerrar lista al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!listaDiv.contains(e.target) && e.target.id !== 'buscarApellido' && e.target.id !== 'btnBuscarApellido') {
            listaDiv.style.display = 'none';
        }
    });

    // === Función común: cargar datos de autoridad ===
    function cargarAutoridad(data) {
        document.getElementById('dni_instructor').value = data.dni || '';
        document.getElementById('grado_instructor').value = data.grado || '';
        document.getElementById('apellido_instructor').value = data.apellido || '';
        document.getElementById('nombre_instructor').value = data.nombre || '-';
        document.getElementById('cargo_instructor').value = data.cargo || '';
        document.getElementById('busquedaAutResultado').style.display = 'block';
    }

    // === Buscar autoridad por DNI ===
    document.getElementById('btnBuscarDniAut').addEventListener('click', function() {
        var dni = document.getElementById('dni_instructor').value.trim();
        if (dni.length < 7) { alert('Ingrese un DNI válido (mín. 7 dígitos)'); return; }
        fetch(urlDni + '?dni=' + dni)
            .then(r => r.json())
            .then(data => {
                if (data.found) { cargarAutoridad(data.data); }
                else { alert('DNI no encontrado. Complete los campos manualmente.'); }
            })
            .catch(() => alert('Error de conexión'));
    });

    document.getElementById('dni_instructor').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnBuscarDniAut').click(); }
    });

    // === Buscar autoridad por APELLIDO ===
    var listaAut = document.getElementById('listaApellidosAut');

    function buscarApellidoAut() {
        var q = document.getElementById('buscarApellidoAut').value.trim();
        if (q.length < 2) { listaAut.style.display = 'none'; return; }
        fetch(urlApellido + '?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(resultados => {
                listaAut.innerHTML = '';
                if (resultados.length === 0) {
                    listaAut.innerHTML = '<span class="list-group-item text-muted">Sin resultados</span>';
                    listaAut.style.display = 'block';
                    return;
                }
                resultados.forEach(function(p) {
                    var item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action py-1';
                    item.textContent = p.grado + ' ' + p.apellido + ' (DNI: ' + p.dni + ')';
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        cargarAutoridad(p);
                        listaAut.style.display = 'none';
                        document.getElementById('buscarApellidoAut').value = '';
                    });
                    listaAut.appendChild(item);
                });
                listaAut.style.display = 'block';
            })
            .catch(() => { listaAut.style.display = 'none'; });
    }

    document.getElementById('btnBuscarApellidoAut').addEventListener('click', buscarApellidoAut);
    document.getElementById('buscarApellidoAut').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarApellidoAut(); }
        else if (this.value.trim().length >= 3) { buscarApellidoAut(); }
    });

    document.addEventListener('click', function(e) {
        if (listaAut && !listaAut.contains(e.target) && e.target.id !== 'buscarApellidoAut' && e.target.id !== 'btnBuscarApellidoAut') {
            listaAut.style.display = 'none';
        }
    });

    // === Función común: cargar datos del REVISOR ===
    function cargarRevisor(data) {
        document.getElementById('dni_revisor').value = data.dni || '';
        document.getElementById('revisor_grado').value = data.grado || '';
        document.getElementById('revisor_nombre').value = data.apellido + (data.nombre && data.nombre !== '-' ? ', ' + data.nombre : '') || '';
        document.getElementById('revisor_cargo').value = data.cargo || 'Director';
        document.getElementById('busquedaRevResultado').style.display = 'block';
    }

    // === Buscar revisor por DNI ===
    document.getElementById('btnBuscarDniRev').addEventListener('click', function() {
        var dni = document.getElementById('dni_revisor').value.trim();
        if (dni.length < 7) { alert('Ingrese un DNI válido (mín. 7 dígitos)'); return; }
        fetch(urlDni + '?dni=' + dni)
            .then(r => r.json())
            .then(data => {
                if (data.found) { cargarRevisor(data.data); }
                else { alert('DNI no encontrado. Complete los campos manualmente.'); }
            })
            .catch(() => alert('Error de conexión'));
    });

    document.getElementById('dni_revisor').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnBuscarDniRev').click(); }
    });

    // === Buscar revisor por APELLIDO ===
    var listaRev = document.getElementById('listaApellidosRev');

    function buscarApellidoRev() {
        var q = document.getElementById('buscarApellidoRev').value.trim();
        if (q.length < 2) { listaRev.style.display = 'none'; return; }
        fetch(urlApellido + '?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(resultados => {
                listaRev.innerHTML = '';
                if (resultados.length === 0) {
                    listaRev.innerHTML = '<span class="list-group-item text-muted">Sin resultados</span>';
                    listaRev.style.display = 'block';
                    return;
                }
                resultados.forEach(function(p) {
                    var item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action py-1';
                    item.textContent = p.grado + ' ' + p.apellido + ' (DNI: ' + p.dni + ')';
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        cargarRevisor(p);
                        listaRev.style.display = 'none';
                        document.getElementById('buscarApellidoRev').value = '';
                    });
                    listaRev.appendChild(item);
                });
                listaRev.style.display = 'block';
            })
            .catch(() => { listaRev.style.display = 'none'; });
    }

    document.getElementById('btnBuscarApellidoRev').addEventListener('click', buscarApellidoRev);
    document.getElementById('buscarApellidoRev').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarApellidoRev(); }
        else if (this.value.trim().length >= 3) { buscarApellidoRev(); }
    });

    document.addEventListener('click', function(e) {
        if (listaRev && !listaRev.contains(e.target) && e.target.id !== 'buscarApellidoRev' && e.target.id !== 'btnBuscarApellidoRev') {
            listaRev.style.display = 'none';
        }
    });

    // =====================================================
    // === MEJORAR MOTIVOS CON IA ===
    // =====================================================
    var urlIA        = '<?= site_url('api/ia/redactar-motivos') ?>';
    var iaPreview    = document.getElementById('iaPreviewCuad');
    var iaTextoDiv   = document.getElementById('iaTextoCuad');
    var iaStatus     = document.getElementById('iaStatusCuad');
    var textoIA      = '';

    document.getElementById('btnMejorarIA').addEventListener('click', function() {
        var borrador = document.getElementById('motivo').value.trim();
        if (borrador.length < 10) {
            alert('Escribí primero una descripción breve de la falta en el campo Motivo.');
            return;
        }

        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Procesando...';
        iaPreview.style.display = 'none';
        iaStatus.style.display = 'block';
        iaStatus.textContent = 'Consultando API de Claude...';
        iaStatus.className = 'form-text text-muted';

        var payload = {
            tipo:     'CUADRO',
            grado:    document.getElementById('grado_infractor').value || '',
            nombre:   document.getElementById('apellido_infractor').value || '',
            fecha:    document.getElementById('fecha_comision').value || '',
            articulo: document.getElementById('reg_act_dis').value || '',
            inciso:   document.getElementById('inciso').value || '',
            dias:     document.getElementById('duracion').value || '',
            borrador: borrador
        };

        fetch(urlIA, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-stars"></i> Mejorar redacción con IA';
            if (data.error) {
                iaStatus.textContent = '⚠️ ' + data.error;
                iaStatus.className = 'form-text text-danger';
                return;
            }
            textoIA = data.texto;
            iaTextoDiv.textContent = textoIA;
            iaPreview.style.display = 'block';
            iaStatus.textContent = 'Texto listo. Revisalo y decidí si aceptarlo.';
            iaStatus.className = 'form-text text-success';
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-stars"></i> Mejorar redacción con IA';
            iaStatus.textContent = '⚠️ Error de conexión: ' + err.toString();
            iaStatus.className = 'form-text text-danger';
        });
    });

    document.getElementById('btnAceptarIA').addEventListener('click', function() {
        if (textoIA) {
            document.getElementById('motivo').value = textoIA;
            iaPreview.style.display = 'none';
            iaStatus.textContent = '✔ Texto aceptado y cargado en el campo Motivo.';
            iaStatus.className = 'form-text text-success';
        }
    });

    document.getElementById('btnDescartarIA').addEventListener('click', function() {
        iaPreview.style.display = 'none';
        iaStatus.style.display = 'none';
        textoIA = '';
    });

    // =====================================================
    // === SUGERIR ART. / INCISO CON IA (CDFFAA) ===
    // =====================================================
    var urlNorm2       = '<?= site_url('api/ia/sugerir-normativa') ?>';
    var normPreview2   = document.getElementById('normPreviewCuad');
    var normStatus2    = document.getElementById('normStatusCuad');
    var normResult2    = document.getElementById('normResultCuad');
    var normJust2      = document.getElementById('normJustCuad');
    var normSugerida2  = {};

    document.getElementById('btnSugerirNormCuad').addEventListener('click', function() {
        var borrador = document.getElementById('motivo').value.trim();
        if (borrador.length < 10) {
            alert('Escribí primero la descripción de la falta en el campo Motivo.');
            return;
        }
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Analizando...';
        normPreview2.style.display = 'none';
        normStatus2.style.display = 'block';
        normStatus2.textContent = 'Consultando normativa CDFFAA...';
        normStatus2.className = 'form-text text-muted';

        fetch(urlNorm2, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ tipo: 'CUADRO', borrador: borrador })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-journal-bookmark"></i> Sugerir Art./Inciso con IA';
            if (data.error) {
                normStatus2.textContent = '⚠️ ' + data.error;
                normStatus2.className = 'form-text text-danger';
                return;
            }
            normSugerida2 = data;
            var tipoLabel = data.tipo_falta === 'leve' ? '🟡 LEVE' : (data.tipo_falta === 'grave' ? '🔴 GRAVE' : '⛔ GRAVE+');
            normResult2.textContent = tipoLabel + ' — ' + data.articulo + ', ' + data.inciso;
            normJust2.textContent = data.justificacion;
            normPreview2.style.display = 'block';
            normStatus2.textContent = 'Sugerencia lista. Revisá y aplicá si es correcta.';
            normStatus2.className = 'form-text text-success';
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-journal-bookmark"></i> Sugerir Art./Inciso con IA';
            normStatus2.textContent = '⚠️ Error: ' + err.toString();
            normStatus2.className = 'form-text text-danger';
        });
    });

    document.getElementById('btnAceptarNormCuad').addEventListener('click', function() {
        if (normSugerida2.articulo) {
            document.getElementById('reg_act_dis').value = normSugerida2.articulo;
            document.getElementById('inciso').value = normSugerida2.inciso;
            normPreview2.style.display = 'none';
            normStatus2.textContent = '✔ Art. e Inciso aplicados.';
            normStatus2.className = 'form-text text-success';
        }
    });

    document.getElementById('btnDescartarNormCuad').addEventListener('click', function() {
        normPreview2.style.display = 'none';
        normStatus2.style.display = 'none';
        normSugerida2 = {};
    });
});
</script>




