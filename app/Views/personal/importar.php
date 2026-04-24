<?php
/**
 * Vista: Importar Personal desde CSV — Padrón CMN
 */
?>

<!-- Encabezado -->
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle d-flex align-items-center justify-content-center"
         style="width:52px;height:52px;background:rgba(26,58,107,.12);">
        <i class="bi bi-upload fs-4" style="color:var(--ea-blue)"></i>
    </div>
    <div>
        <h2 class="mb-0 fw-bold" style="font-size:1.25rem;">Importar Personal — Padrón CMN</h2>
        <p class="text-muted mb-0 small">Cargue o actualice masivamente el personal mediante un archivo CSV</p>
    </div>
</div>

<div class="row g-4">

    <!-- FORMULARIO DE CARGA -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header card-header-blue">
                <i class="bi bi-file-earmark-spreadsheet"></i> Subir archivo CSV
            </div>
            <div class="card-body">
                <form action="<?= site_url('personal/importar') ?>" method="post"
                      enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label for="archivo_csv" class="form-label fw-semibold">
                            Archivo CSV <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="archivo_csv" id="archivo_csv"
                               class="form-control" accept=".csv,.txt" required>
                        <div class="form-text">Formato: <code>.csv</code> separado por comas, codificación UTF-8.</div>
                        <div class="invalid-feedback">Seleccione un archivo CSV.</div>
                    </div>

                    <div class="alert alert-info py-2 mb-4">
                        <i class="bi bi-info-circle"></i>
                        <strong>Comportamiento de la importación:</strong><br>
                        <ul class="mb-0 mt-1 small">
                            <li>Si ya existe un registro con el mismo <strong>DNI + tipo</strong>, sus datos se <strong>actualizan</strong>.</li>
                            <li>Si no existe, se <strong>inserta</strong> como nuevo integrante.</li>
                            <li>Los registros existentes con otros tipos permanecen sin cambios.</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= site_url('personal') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-ea-blue">
                            <i class="bi bi-upload"></i> Importar ahora
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- INSTRUCCIONES DEL FORMATO -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid var(--ea-gold)!important;">
            <div class="card-header" style="background:var(--ea-gold);color:#000;">
                <i class="bi bi-question-circle"></i> Formato del archivo
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">
                    El archivo debe tener <strong>una fila de encabezado</strong> y los datos en las siguientes columnas:
                </p>

                <table class="table table-sm table-bordered small mb-3">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th><th>Columna</th><th>Requerido</th><th>Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td><code>DNI</code></td><td><span class="text-danger">Sí</span></td><td>20123456</td></tr>
                        <tr><td>2</td><td><code>APELLIDO</code></td><td><span class="text-danger">Sí</span></td><td>GARCIA</td></tr>
                        <tr><td>3</td><td><code>NOMBRE</code></td><td>No</td><td>Juan Carlos</td></tr>
                        <tr><td>4</td><td><code>GRADO</code></td><td>No</td><td>TC</td></tr>
                        <tr><td>5</td><td><code>ARMA_ESPECIALIDAD</code></td><td>No</td><td>I</td></tr>
                        <tr><td>6</td><td><code>DESTINO_INTERNO</code></td><td>No</td><td>Cía A</td></tr>
                        <tr><td>7</td><td><code>CARGO</code></td><td>No</td><td>Jefe de Sección</td></tr>
                        <tr><td>8</td><td><code>TIPO</code></td><td>No *</td><td>cuadro</td></tr>
                    </tbody>
                </table>

                <div class="alert alert-warning py-2 small mb-3">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Columna TIPO</strong> — valores aceptados:
                    <code>cuadro</code>, <code>cadete</code>, <code>instructor</code>,
                    <code>autoridad</code>, <code>civil</code>.<br>
                    Si se omite o es inválido, se asigna <code>cuadro</code> por defecto.
                </div>

                <p class="small text-muted mb-2"><strong>Ejemplo de contenido:</strong></p>
                <pre class="bg-light border rounded p-2 small" style="font-size:.72rem;overflow-x:auto;">DNI,APELLIDO,NOMBRE,GRADO,ARMA,DESTINO,CARGO,TIPO
20123456,GARCIA,Juan Carlos,TC,I,Estado Mayor,Jefe EM,cuadro
31456789,RODRIGUEZ,María Laura,,,Div Jurídica,Asesor Jurídico,civil
99001001,LOPEZ PEREZ,,,,,,cadete</pre>

                <p class="small text-muted mb-0">
                    <i class="bi bi-lightbulb text-warning"></i>
                    Puede preparar el archivo en Excel y guardarlo como <em>CSV UTF-8</em> (con BOM).
                    El sistema detecta y descarta automáticamente el BOM.
                </p>
            </div>
        </div>
    </div>

</div>
