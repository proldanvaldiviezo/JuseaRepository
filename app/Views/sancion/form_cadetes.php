<h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> Sancion Disciplinaria - Cadetes</h2>
<p class="text-muted">Imposicion directa - Regimen Disciplinario Formativo CMN</p>

<form action="<?= site_url('sancion/cadetes/guardar') ?>" method="post" class="needs-validation" novalidate id="formSancion">
    <?= csrf_field() ?>
    <input type="hidden" name="tipo_personal" value="CADETE">

    <!-- TABS -->
    <ul class="nav nav-tabs mb-4" id="sancionTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="tab-infractor" data-bs-toggle="tab" data-bs-target="#infractor" type="button">
                <i class="bi bi-person"></i> A. Infractor
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-sancion" data-bs-toggle="tab" data-bs-target="#sancion" type="button">
                <i class="bi bi-exclamation-triangle"></i> B. Sancion
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
                <div class="card-header bg-light"><strong>A. Datos del Infractor (Cadete)</strong></div>
                <div class="card-body">
                    <div class="row g-3">
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
                        </div>
                        <!-- Busqueda por Apellido -->
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
                            <label for="grado_infractor" class="form-label">Ano / Grado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="grado_infractor" name="grado_infractor"
                                   value="<?= esc(old('grado_infractor') ?? '') ?>" required
                                   placeholder="Ej: IVto Ano, IIdo Ano">
                        </div>
                        <div class="col-md-3">
                            <label for="arma_infractor" class="form-label">Arma / Servicio</label>
                            <input type="text" class="form-control" id="arma_infractor" name="arma_infractor"
                                   value="<?= esc(old('arma_infractor') ?? '') ?>"
                                   placeholder="Ej: Esc C, 1ra Ca I">
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
                                   placeholder="APELLIDO Nombre">
                        </div>
                        <input type="hidden" id="nombre_infractor" name="nombre_infractor" value="<?= esc(old('nombre_infractor') ?? '-') ?>">
                        <div class="col-md-6">
                            <label for="destino_infractor" class="form-label">Destino Interno (Escuadron/Cia)</label>
                            <input type="text" class="form-control" id="destino_infractor" name="destino_infractor"
                                   value="<?= esc(old('destino_infractor') ?? '') ?>"
                                   placeholder="Ej: Escuadron de Caballeria">
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
                <div class="card-header bg-light"><strong>B. Sancion Disciplinaria</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="fecha_comision" class="form-label">Fecha Comision Falta <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_comision" name="fecha_comision"
                                   value="<?= esc(old('fecha_comision') ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="reg_act_dis" class="form-label">Punto (Reg. Disc. Formativo)</label>
                            <input type="text" class="form-control" id="reg_act_dis" name="reg_act_dis"
                                   value="<?= esc(old('reg_act_dis') ?? '') ?>"
                                   placeholder="Ej: Punto 25">
                        </div>
                        <div class="col-md-3">
                            <label for="inciso" class="form-label">Nro de Falta</label>
                            <input type="text" class="form-control" id="inciso" name="inciso"
                                   value="<?= esc(old('inciso') ?? '') ?>"
                                   placeholder="Ej: Nro 16">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="w-100">
                                <button type="button" class="btn btn-outline-warning btn-sm" id="btnSugerirNormCad">
                                    <i class="bi bi-journal-bookmark"></i> Sugerir Punto/Falta con IA
                                </button>
                                <div id="normStatusCad" class="form-text mt-1" style="display:none;"></div>
                                <div id="normPreviewCad" class="mt-1 border rounded p-2 bg-warning bg-opacity-10" style="display:none; font-size:0.85rem;">
                                    <strong id="normResultCad"></strong><br>
                                    <span id="normJustCad" class="text-muted"></span>
                                    <div class="mt-1 d-flex gap-2">
                                        <button type="button" class="btn btn-warning btn-sm" id="btnAceptarNormCad">
                                            <i class="bi bi-check-lg"></i> Aplicar
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDescartarNormCad">
                                            <i class="bi bi-x-lg"></i> Descartar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="motivo" class="form-label">Motivo (Descripcion circunstanciada) <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="motivo" name="motivo" rows="5" required minlength="5"
                                      placeholder="Descripcion circunstanciada de los hechos..."><?= esc(old('motivo') ?? '') ?></textarea>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnMejorarIA">
                                    <i class="bi bi-stars"></i> Mejorar redaccion con IA
                                </button>
                                <span id="iaStatusCad" class="form-text" style="display:none;"></span>
                            </div>
                            <div id="iaPreviewCad" class="mt-2 border rounded p-2 bg-light" style="display:none;">
                                <p class="mb-1 small text-muted fw-bold"><i class="bi bi-robot"></i> Texto sugerido por IA — revisalo antes de aceptar:</p>
                                <div id="iaTextoCad" class="small" style="white-space: pre-wrap;"></div>
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
                            <label for="tipo_sancion_desc" class="form-label">Tipo de Sancion</label>
                            <select class="form-select" id="tipo_sancion_desc" name="tipo_sancion_desc">
                                <option value="Dias de Arresto" <?= old('tipo_sancion_desc') === 'Dias de Arresto' ? 'selected' : '' ?>>Dias de Arresto</option>
                                <option value="Apercibimiento" <?= old('tipo_sancion_desc') === 'Apercibimiento' ? 'selected' : '' ?>>Apercibimiento</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="duracion" class="form-label">Duracion</label>
                            <input type="text" class="form-control" id="duracion" name="duracion"
                                   value="<?= esc(old('duracion') ?? '') ?>"
                                   placeholder="Ej: TREINTA Y CINCO (35)">
                            <small class="form-text text-muted">En letras y numero</small>
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
                <div class="card-header bg-light"><strong>C. Autoridad que Impone la Sancion</strong></div>
                <div class="card-body">
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle"></i> Busque por DNI o Apellido, o complete los campos directamente.</p>
                    <div class="row g-3">
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
                        <div class="col-md-3 position-relative">
                            <label class="form-label">Buscar por Apellido</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscarApellidoAut" placeholder="Escriba apellido..."
                                       autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarApellidoAut" title="Buscar">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div id="listaApellidosAut" class="list-group position-absolute w-100" style="z-index:1050; display:none; max-height:200px; overflow-y:auto;"></div>
                        </div>
                        <div class="col-md-3 position-relative">
                            <label class="form-label">Buscar por Nombre</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscarNombreAut" placeholder="Escriba nombre..."
                                       autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarNombreAut" title="Buscar por Nombre">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div id="listaNombresAut" class="list-group position-absolute w-100" style="z-index:1050; display:none; max-height:200px; overflow-y:auto;"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div id="busquedaAutResultado" class="form-text text-success" style="display:none;">
                                <i class="bi bi-check-circle"></i> Datos cargados desde BD
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="grado_instructor" class="form-label">Grado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="grado_instructor" name="grado_instructor"
                                   value="<?= esc(old('grado_instructor') ?? '') ?>" required
                                   placeholder="Ej: CR, TC, General">
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

        <!-- TAB D: REVISOR -->
        <div class="tab-pane fade" id="revisor">
            <div class="card">
                <div class="card-header bg-light"><strong>D. Autoridad de Revision</strong></div>
                <div class="card-body">
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle"></i> Busque por DNI o Apellido, o complete los campos directamente.</p>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="dni_revisor" class="form-label">DNI Revisor</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="dni_revisor" name="dni_revisor"
                                       data-solo-numeros maxlength="10"
                                       value="<?= esc(old('dni_revisor') ?? '') ?>"
                                       placeholder="Buscar por DNI">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarDniRev" title="Buscar por DNI">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 position-relative">
                            <label class="form-label">Buscar por Apellido</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscarApellidoRev" placeholder="Escriba apellido..."
                                       autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarApellidoRev" title="Buscar">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div id="listaApellidosRev" class="list-group position-absolute w-100" style="z-index:1050; display:none; max-height:200px; overflow-y:auto;"></div>
                        </div>
                        <div class="col-md-3 position-relative">
                            <label class="form-label">Buscar por Nombre</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscarNombreRev" placeholder="Escriba nombre..."
                                       autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarNombreRev" title="Buscar por Nombre">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div id="listaNombresRev" class="list-group position-absolute w-100" style="z-index:1050; display:none; max-height:200px; overflow-y:auto;"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div id="busquedaRevResultado" class="form-text text-success" style="display:none;">
                                <i class="bi bi-check-circle"></i> Datos cargados desde BD
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="grado_revisor" class="form-label">Grado Revisor</label>
                            <input type="text" class="form-control" id="grado_revisor" name="grado_revisor"
                                   value="<?= esc(old('grado_revisor') ?? '') ?>"
                                   placeholder="Ej: CY, General">
                        </div>
                        <div class="col-md-6">
                            <label for="apellido_revisor" class="form-label">Apellido y Nombre Revisor</label>
                            <input type="text" class="form-control" id="apellido_revisor" name="apellido_revisor"
                                   value="<?= esc(old('apellido_revisor') ?? '') ?>"
                                   placeholder="Ingrese apellido y nombre">
                        </div>
                        <div class="col-md-3">
                            <label for="cargo_revisor" class="form-label">Cargo Revisor</label>
                            <input type="text" class="form-control" id="cargo_revisor" name="cargo_revisor"
                                   value="<?= esc(old('cargo_revisor') ?? 'Director') ?>">
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

<!-- Script de busqueda por DNI y Apellido -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var urlDni = '<?= site_url('api/persona/buscar-dni') ?>';
    var urlApellido = '<?= site_url('api/persona/buscar-apellido') ?>';
    var urlNombre = '<?= site_url('api/persona/buscar-nombre') ?>';

    // === Funcion comun: cargar datos del infractor ===
    function cargarInfractor(data) {
        document.getElementById('dni_infractor').value = data.dni || '';
        document.getElementById('grado_infractor').value = data.grado || '';
        document.getElementById('apellido_infractor').value = data.apellido || '';
        document.getElementById('nombre_infractor').value = data.nombre || '-';
        document.getElementById('arma_infractor').value = data.arma_especialidad || '';
        document.getElementById('destino_infractor').value = data.destino_interno || '';
        document.getElementById('busquedaResultado').style.display = 'block';
    }

    // === Buscar infractor (cadete) por DNI ===
    document.getElementById('btnBuscarDni').addEventListener('click', function() {
        var dni = document.getElementById('dni_infractor').value.trim();
        if (dni.length < 7) { alert('Ingrese un DNI valido (min 7 digitos)'); return; }
        fetch(urlDni + '?dni=' + dni + '&tipo=cadete')
            .then(r => r.json())
            .then(data => {
                if (data.found) { cargarInfractor(data.data); }
                else { document.getElementById('busquedaResultado').style.display = 'none'; alert('DNI no encontrado en BD de Cadetes.'); }
            })
            .catch(() => alert('Error de conexion'));
    });

    document.getElementById('dni_infractor').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnBuscarDni').click(); }
    });

    // === Buscar infractor (cadete) por APELLIDO ===
    var listaDiv = document.getElementById('listaApellidos');

    function buscarPorApellido() {
        var q = document.getElementById('buscarApellido').value.trim();
        if (q.length < 2) { listaDiv.style.display = 'none'; return; }
        fetch(urlApellido + '?q=' + encodeURIComponent(q) + '&tipo=cadete')
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

    document.addEventListener('click', function(e) {
        if (!listaDiv.contains(e.target) && e.target.id !== 'buscarApellido' && e.target.id !== 'btnBuscarApellido') {
            listaDiv.style.display = 'none';
        }
    });

    // =====================================================
    // === AUTORIDAD (Instructor) - DNI + Apellido ===
    // =====================================================
    function cargarAutoridad(data) {
        document.getElementById('dni_instructor').value = data.dni || '';
        document.getElementById('grado_instructor').value = data.grado || '';
        document.getElementById('apellido_instructor').value = data.apellido || '';
        document.getElementById('nombre_instructor').value = data.nombre || '-';
        document.getElementById('cargo_instructor').value = data.cargo || '';
        document.getElementById('busquedaAutResultado').style.display = 'block';
    }

    document.getElementById('btnBuscarDniAut').addEventListener('click', function() {
        var dni = document.getElementById('dni_instructor').value.trim();
        if (dni.length < 7) { alert('Ingrese un DNI valido (min 7 digitos)'); return; }
        fetch(urlDni + '?dni=' + dni + '&tipo=cuadro')
            .then(r => r.json())
            .then(data => {
                if (data.found) { cargarAutoridad(data.data); }
                else { alert('DNI no encontrado. Complete los campos manualmente.'); }
            })
            .catch(() => alert('Error de conexion'));
    });

    document.getElementById('dni_instructor').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnBuscarDniAut').click(); }
    });

    var listaAut = document.getElementById('listaApellidosAut');
    function buscarApellidoAut() {
        var q = document.getElementById('buscarApellidoAut').value.trim();
        if (q.length < 2) { listaAut.style.display = 'none'; return; }
        fetch(urlApellido + '?q=' + encodeURIComponent(q) + '&tipo=cuadro')
            .then(r => r.json())
            .then(resultados => {
                listaAut.innerHTML = '';
                if (!resultados.length) { listaAut.innerHTML = '<span class="list-group-item text-muted">Sin resultados</span>'; listaAut.style.display = 'block'; return; }
                resultados.forEach(function(p) {
                    var item = document.createElement('a');
                    item.href = '#'; item.className = 'list-group-item list-group-item-action py-1';
                    item.textContent = p.grado + ' ' + p.apellido + ' (DNI: ' + p.dni + ')';
                    item.addEventListener('click', function(e) { e.preventDefault(); cargarAutoridad(p); listaAut.style.display = 'none'; document.getElementById('buscarApellidoAut').value = ''; });
                    listaAut.appendChild(item);
                });
                listaAut.style.display = 'block';
            }).catch(() => { listaAut.style.display = 'none'; });
    }
    document.getElementById('btnBuscarApellidoAut').addEventListener('click', buscarApellidoAut);
    document.getElementById('buscarApellidoAut').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarApellidoAut(); }
        else if (this.value.trim().length >= 3) { buscarApellidoAut(); }
    });

    // === AUTORIDAD - Buscar por NOMBRE ===
    var listaNomAut = document.getElementById('listaNombresAut');
    function buscarNombreAut() {
        var q = document.getElementById('buscarNombreAut').value.trim();
        if (q.length < 2) { listaNomAut.style.display = 'none'; return; }
        fetch(urlNombre + '?q=' + encodeURIComponent(q) + '&tipo=cuadro')
            .then(r => r.json())
            .then(resultados => {
                listaNomAut.innerHTML = '';
                if (!resultados.length) { listaNomAut.innerHTML = '<span class="list-group-item text-muted">Sin resultados</span>'; listaNomAut.style.display = 'block'; return; }
                resultados.forEach(function(p) {
                    var item = document.createElement('a');
                    item.href = '#'; item.className = 'list-group-item list-group-item-action py-1';
                    item.textContent = p.grado + ' ' + p.apellido + ', ' + p.nombre + ' (DNI: ' + p.dni + ')';
                    item.addEventListener('click', function(e) { e.preventDefault(); cargarAutoridad(p); listaNomAut.style.display = 'none'; document.getElementById('buscarNombreAut').value = ''; });
                    listaNomAut.appendChild(item);
                });
                listaNomAut.style.display = 'block';
            }).catch(() => { listaNomAut.style.display = 'none'; });
    }
    document.getElementById('btnBuscarNombreAut').addEventListener('click', buscarNombreAut);
    document.getElementById('buscarNombreAut').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarNombreAut(); }
        else if (this.value.trim().length >= 3) { buscarNombreAut(); }
    });

    // =====================================================
    // === REVISOR - DNI + Apellido ===
    // =====================================================
    function cargarRevisor(data) {
        document.getElementById('dni_revisor').value = data.dni || '';
        document.getElementById('grado_revisor').value = data.grado || '';
        document.getElementById('apellido_revisor').value = data.apellido || '';
        document.getElementById('cargo_revisor').value = data.cargo || 'Director';
        document.getElementById('busquedaRevResultado').style.display = 'block';
    }

    document.getElementById('btnBuscarDniRev').addEventListener('click', function() {
        var dni = document.getElementById('dni_revisor').value.trim();
        if (dni.length < 7) { alert('Ingrese un DNI valido (min 7 digitos)'); return; }
        fetch(urlDni + '?dni=' + dni + '&tipo=cuadro')
            .then(r => r.json())
            .then(data => {
                if (data.found) { cargarRevisor(data.data); }
                else { alert('DNI no encontrado. Complete los campos manualmente.'); }
            })
            .catch(() => alert('Error de conexion'));
    });

    document.getElementById('dni_revisor').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnBuscarDniRev').click(); }
    });

    var listaRev = document.getElementById('listaApellidosRev');
    function buscarApellidoRev() {
        var q = document.getElementById('buscarApellidoRev').value.trim();
        if (q.length < 2) { listaRev.style.display = 'none'; return; }
        fetch(urlApellido + '?q=' + encodeURIComponent(q) + '&tipo=cuadro')
            .then(r => r.json())
            .then(resultados => {
                listaRev.innerHTML = '';
                if (!resultados.length) { listaRev.innerHTML = '<span class="list-group-item text-muted">Sin resultados</span>'; listaRev.style.display = 'block'; return; }
                resultados.forEach(function(p) {
                    var item = document.createElement('a');
                    item.href = '#'; item.className = 'list-group-item list-group-item-action py-1';
                    item.textContent = p.grado + ' ' + p.apellido + ' (DNI: ' + p.dni + ')';
                    item.addEventListener('click', function(e) { e.preventDefault(); cargarRevisor(p); listaRev.style.display = 'none'; document.getElementById('buscarApellidoRev').value = ''; });
                    listaRev.appendChild(item);
                });
                listaRev.style.display = 'block';
            }).catch(() => { listaRev.style.display = 'none'; });
    }
    document.getElementById('btnBuscarApellidoRev').addEventListener('click', buscarApellidoRev);
    document.getElementById('buscarApellidoRev').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarApellidoRev(); }
        else if (this.value.trim().length >= 3) { buscarApellidoRev(); }
    });

    // === REVISOR - Buscar por NOMBRE ===
    var listaNomRev = document.getElementById('listaNombresRev');
    function buscarNombreRev() {
        var q = document.getElementById('buscarNombreRev').value.trim();
        if (q.length < 2) { listaNomRev.style.display = 'none'; return; }
        fetch(urlNombre + '?q=' + encodeURIComponent(q) + '&tipo=cuadro')
            .then(r => r.json())
            .then(resultados => {
                listaNomRev.innerHTML = '';
                if (!resultados.length) { listaNomRev.innerHTML = '<span class="list-group-item text-muted">Sin resultados</span>'; listaNomRev.style.display = 'block'; return; }
                resultados.forEach(function(p) {
                    var item = document.createElement('a');
                    item.href = '#'; item.className = 'list-group-item list-group-item-action py-1';
                    item.textContent = p.grado + ' ' + p.apellido + ', ' + p.nombre + ' (DNI: ' + p.dni + ')';
                    item.addEventListener('click', function(e) { e.preventDefault(); cargarRevisor(p); listaNomRev.style.display = 'none'; document.getElementById('buscarNombreRev').value = ''; });
                    listaNomRev.appendChild(item);
                });
                listaNomRev.style.display = 'block';
            }).catch(() => { listaNomRev.style.display = 'none'; });
    }
    document.getElementById('btnBuscarNombreRev').addEventListener('click', buscarNombreRev);
    document.getElementById('buscarNombreRev').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarNombreRev(); }
        else if (this.value.trim().length >= 3) { buscarNombreRev(); }
    });

    // Cerrar listas al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (listaAut && !listaAut.contains(e.target) && e.target.id !== 'buscarApellidoAut' && e.target.id !== 'btnBuscarApellidoAut') listaAut.style.display = 'none';
        if (listaNomAut && !listaNomAut.contains(e.target) && e.target.id !== 'buscarNombreAut' && e.target.id !== 'btnBuscarNombreAut') listaNomAut.style.display = 'none';
        if (listaRev && !listaRev.contains(e.target) && e.target.id !== 'buscarApellidoRev' && e.target.id !== 'btnBuscarApellidoRev') listaRev.style.display = 'none';
        if (listaNomRev && !listaNomRev.contains(e.target) && e.target.id !== 'buscarNombreRev' && e.target.id !== 'btnBuscarNombreRev') listaNomRev.style.display = 'none';
    });

    // =====================================================
    // === MEJORAR MOTIVOS CON IA ===
    // =====================================================
    var urlIA       = '<?= site_url('api/ia/redactar-motivos') ?>';
    var iaPreview   = document.getElementById('iaPreviewCad');
    var iaTextoDiv  = document.getElementById('iaTextoCad');
    var iaStatus    = document.getElementById('iaStatusCad');
    var textoIA     = '';

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
            tipo:     'CADETE',
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
            btn.innerHTML = '<i class="bi bi-stars"></i> Mejorar redaccion con IA';
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
            btn.innerHTML = '<i class="bi bi-stars"></i> Mejorar redaccion con IA';
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
    // === SUGERIR PUNTO / Nro DE FALTA CON IA (RDF) ===
    // =====================================================
    var urlNorm       = '<?= site_url('api/ia/sugerir-normativa') ?>';
    var normPreview   = document.getElementById('normPreviewCad');
    var normStatus    = document.getElementById('normStatusCad');
    var normResult    = document.getElementById('normResultCad');
    var normJust      = document.getElementById('normJustCad');
    var normSugerida  = {};

    document.getElementById('btnSugerirNormCad').addEventListener('click', function() {
        var borrador = document.getElementById('motivo').value.trim();
        if (borrador.length < 10) {
            alert('Escribí primero la descripción de la falta en el campo Motivo.');
            return;
        }
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Analizando...';
        normPreview.style.display = 'none';
        normStatus.style.display = 'block';
        normStatus.textContent = 'Consultando normativa...';
        normStatus.className = 'form-text text-muted';

        fetch(urlNorm, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ tipo: 'CADETE', borrador: borrador })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-journal-bookmark"></i> Sugerir Punto/Falta con IA';
            if (data.error) {
                normStatus.textContent = '⚠️ ' + data.error;
                normStatus.className = 'form-text text-danger';
                return;
            }
            normSugerida = data;
            var tipoLabel = data.tipo_falta === 'leve' ? '🟡 LEVE' : (data.tipo_falta === 'grave' ? '🔴 GRAVE' : '⛔ EXPULSIÓN');
            normResult.textContent = tipoLabel + ' — ' + data.articulo + ', ' + data.inciso;
            normJust.textContent = data.justificacion;
            normPreview.style.display = 'block';
            normStatus.textContent = 'Sugerencia lista. Revisá y aplicá si es correcta.';
            normStatus.className = 'form-text text-success';
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-journal-bookmark"></i> Sugerir Punto/Falta con IA';
            normStatus.textContent = '⚠️ Error: ' + err.toString();
            normStatus.className = 'form-text text-danger';
        });
    });

    document.getElementById('btnAceptarNormCad').addEventListener('click', function() {
        if (normSugerida.articulo) {
            document.getElementById('reg_act_dis').value = normSugerida.articulo;
            document.getElementById('inciso').value = normSugerida.inciso;
            normPreview.style.display = 'none';
            normStatus.textContent = '✔ Punto y Nro de falta aplicados.';
            normStatus.className = 'form-text text-success';
        }
    });

    document.getElementById('btnDescartarNormCad').addEventListener('click', function() {
        normPreview.style.display = 'none';
        normStatus.style.display = 'none';
        normSugerida = {};
    });
});
</script>
