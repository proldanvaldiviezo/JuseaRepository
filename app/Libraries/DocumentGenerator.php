<?php
namespace App\Libraries;

/**
 * Generador de Documentos - JUSEA CMN v2.0
 *
 * Librería centralizada que genera documentos en formato .docx (PhpWord)
 * y .pdf (TCPDF). Elimina la duplicación masiva del código original
 * unificando estilos, encabezado institucional y lógica de descarga.
 *
 * Requiere:
 * - composer require phpoffice/phpword
 * - composer require tecnickcom/tcpdf
 */
class DocumentGenerator
{
    // Estilos institucionales del Ejército Argentino
    private const FUENTE_TITULO   = ['size' => 16, 'bold' => true, 'italic' => true];
    private const FUENTE_MEMBRETE = ['size' => 9,  'bold' => false, 'italic' => true];
    private const FUENTE_UNIDAD   = ['size' => 14, 'bold' => false, 'italic' => true];
    private const FUENTE_SUBTITULO = ['size' => 12, 'bold' => true];
    private const FUENTE_NORMAL    = ['size' => 12, 'bold' => false, 'name' => 'Times New Roman'];
    private const FUENTE_NEGRITA   = ['size' => 12, 'bold' => true,  'name' => 'Times New Roman'];
    private const FUENTE_SMALL     = ['size' => 9,  'bold' => false];

    private const MARGEN_SECCION = [
        'marginTop'    => 816,
        'marginBottom' => 509,
        'marginLeft'   => 911,
        'marginRight'  => 680,
    ];

    private const PARRAFO_IZQ = ['align' => 'left', 'spaceBefore' => 0, 'spaceAfter' => 0];
    private const PARRAFO_DER = ['align' => 'right', 'spaceBefore' => 0, 'spaceAfter' => 0];
    private const PARRAFO_CENTRO = ['align' => 'center', 'spaceAfter' => 0, 'spaceBefore' => 0];

    // =========================================================
    // GENERACIÓN DE SANCIÓN DISCIPLINARIA
    // =========================================================

    /**
     * Generar documento de sanción disciplinaria.
     *
     * @param array $sancion    Datos completos de la sanción (del modelo)
     * @param object $encabezado Datos del encabezado institucional
     * @param string $tipo      'cuadros' o 'cadetes'
     * @param string $formato   'docx' o 'pdf'
     */
    public function generarSancion(array $sancion, object $encabezado, string $tipo, string $formato = 'docx')
    {
        if ($formato === 'pdf') {
            return $this->generarSancionPDF($sancion, $encabezado, $tipo);
        }

        return $this->generarSancionDocx($sancion, $encabezado, $tipo);
    }

    private function generarSancionDocx(array $s, object $enc, string $tipo)
    {
        if ($tipo === 'cadetes') {
            return $this->generarSancionCadetesDocx($s, $enc);
        }
        return $this->generarSancionCuadrosDocx($s, $enc);
    }

    /**
     * Genera planilla de sanción CUADROS (21 filas, secciones A-D).
     * Spans exactos según template_planilla_CUADROS.docx (19 columnas).
     * Col-unit = 500 twips → total tabla = 9500 twips.
     */
    private function generarSancionCuadrosDocx(array $s, object $enc)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection(self::MARGEN_SECCION);

        $this->agregarEncabezadoInstitucional($section, $enc, $s['letra'] ?? '', $s['nro'] ?? '');
        $section->addText(
            'ANEXO 1 (PLANILLA IMPOSICIÓN DIRECTA DE SANCIÓN DISCIPLINARIA)',
            self::FUENTE_SUBTITULO, self::PARRAFO_IZQ
        );
        $section->addText('', self::FUENTE_SMALL, self::PARRAFO_IZQ);

        // Tabla 19 columnas, 500 twips/col → 9500 total
        $bordeTabla = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 40];
        $phpWord->addTableStyle('tSanCuadros', $bordeTabla);
        $table = $section->addTable('tSanCuadros');

        $U = 500; // twips por columna
        // Color gris exacto del template (e6e6e6)
        $gris = ['bgColor' => 'E6E6E6', 'borderSize' => 6, 'borderColor' => '000000', 'valign' => 'center'];
        $norm = ['borderSize' => 6, 'borderColor' => '000000', 'valign' => 'center'];
        $fB   = ['bold' => true,  'size' => 10, 'name' => 'Times New Roman'];
        $fN   = ['bold' => false, 'size' => 10, 'name' => 'Times New Roman'];
        $fS   = ['bold' => false, 'size' => 8,  'name' => 'Times New Roman'];
        $pI   = self::PARRAFO_IZQ;
        $pC   = self::PARRAFO_CENTRO;
        $gs   = fn(int $n) => array_merge($norm, ['gridSpan' => $n]);
        $gsG  = fn(int $n) => array_merge($gris, ['gridSpan' => $n]);

        $apellidoNombre = trim(($s['apellido_infractor'] ?? '') . ', ' . ($s['nombre_infractor'] ?? ''), ', ');
        $cargo          = ($s['cargo_autoridad'] ?? '') ?: ($s['cargo_instructor'] ?? '');
        $apellidoAut    = trim(($s['apellido_instructor'] ?? '') . ', ' . ($s['nombre_instructor'] ?? ''), ', ');
        $regArt         = 'Art. ' . ($s['reg_act_dis'] ?? '') . ' CDFFAA';
        $inciso         = 'Inc ' . ($s['inciso'] ?? '');

        // --- SECCIÓN A --- (alturas exactas del template)
        $row = $table->addRow(340); // Row[0] h=340tw=6.0mm
        $row->addCell(1*$U, $gsG(1))->addText('A', $fB, $pC);
        $row->addCell(18*$U, $gsG(18))->addText('DATOS DEL INFRACTOR', $fB, $pC);

        $row = $table->addRow(340); // Row[1] h=340tw=6.0mm
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('GRADO', $fB, $pI);
        $row->addCell(2*$U, $gs(2))->addText(' ' . ($s['grado_infractor'] ?? ''), $fN, $pI);
        $row->addCell(9*$U, $gs(9))->addText('ARMA / SERVICIO / ESPECIALIDAD', $fB, $pI);
        $row->addCell(5*$U, $gs(5))->addText(' ' . ($s['arma_infractor'] ?? ''), $fN, $pI);

        $row = $table->addRow(340); // Row[2] h=340tw=6.0mm
        $row->addCell(1*$U, $norm)->addText('2', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('APELLIDO Y NOMBRE', $fB, $pI);
        $row->addCell(14*$U, $gs(14))->addText(' ' . $apellidoNombre, $fN, $pI);

        $row = $table->addRow(350); // Row[3] h=350tw=6.2mm
        $row->addCell(1*$U, $norm)->addText('3', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('DESTINO INTERNO', $fB, $pI);
        $row->addCell(9*$U, $gs(9))->addText(' ' . ($s['destino_infractor'] ?? ''), $fN, $pI);
        $row->addCell(1*$U, $gs(1))->addText('DNI', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText(' ' . ($s['dni_infractor'] ?? ''), $fN, $pI);

        // --- SECCIÓN B ---
        $row = $table->addRow(350); // Row[4] h=350tw=6.2mm
        $row->addCell(1*$U, $gsG(1))->addText('B', $fB, $pC);
        $row->addCell(18*$U, $gsG(18))->addText('SANCIÓN DISCIPLINARIA', $fB, $pC);

        $row = $table->addRow(336); // Row[5] h=336tw=5.9mm
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('FECHA COMISION FALTA', $fB, $pI);
        $row->addCell(14*$U, $gs(14))->addText(' ' . $this->formatearFecha($s['fecha_comision'] ?? ''), $fN, $pI);

        $row = $table->addRow(340); // Row[6] h=340tw=6.0mm
        $row->addCell(1*$U, $norm)->addText('2', $fB, $pC);
        $row->addCell(10*$U, $gs(10))->addText('RÉGIMEN DE ACTUACIÓN DISCIPLINARIA', $fB, $pI);
        $row->addCell(6*$U, $gs(6))->addText(' ' . $regArt, $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText(' ' . $inciso, $fN, $pI);

        // Row[7] + Row[8] motivo - fila única con altura auto para texto largo
        $motivoTexto = ' ' . ($s['motivo'] ?? '');
        $row = $table->addRow(null); // altura automática según contenido
        $row->addCell(1*$U, $norm)->addText('3', $fB, $pC);
        $celdaLabel = $row->addCell(4*$U, array_merge($gs(4), ['valign' => 'top']));
        $celdaLabel->addText('MOTIVO DE LA SANCIÓN', $fB, $pI);
        $celdaMotivo = $row->addCell(14*$U, array_merge($gs(14), ['valign' => 'top']));
        // Párrafo con word-wrap habilitado para texto largo
        $pMotivo = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 0, 'spaceBefore' => 0];
        $celdaMotivo->addText($motivoTexto, $fN, $pMotivo);

        $row = $table->addRow(340); // Row[9] h=340tw=6.0mm
        $row->addCell(1*$U, $norm)->addText('4', $fB, $pC);
        $row->addCell(1*$U, $gs(1))->addText('TIPO', $fB, $pI);
        $row->addCell(9*$U, $gs(9))->addText('ARRESTO SIMPLE', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('DURACIÓN', $fB, $pI);
        $row->addCell(6*$U, $gs(6))->addText(' ' . ($s['duracion'] ?? ''), $fN, $pI);

        $row = $table->addRow(469); // Row[10] h=469tw=8.3mm
        $row->addCell(1*$U, $norm)->addText('5', $fB, $pC);
        $row->addCell(6*$U, $gs(6))->addText('LUGAR DE CUMPLIMIENTO', $fB, $pI);
        $row->addCell(12*$U, $gs(12))->addText(' ' . ($s['lugar_cumplimiento'] ?? ''), $fN, $pI);

        // --- SECCIÓN C ---
        $row = $table->addRow(325); // Row[11] h=325tw=5.7mm
        $row->addCell(1*$U, $gsG(1))->addText('C', $fB, $pC);
        $row->addCell(18*$U, $gsG(18))->addText('AUTORIDAD QUE IMPONE DE LA SANCIÓN', $fB, $pC);

        $row = $table->addRow(393); // Row[12] h=393tw=6.9mm
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(3*$U, $gs(3))->addText('FIRMA', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('GRADO', $fB, $pI);
        $row->addCell(4*$U, $gs(4))->addText(' ' . ($s['grado_instructor'] ?? ''), $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText('DNI', $fB, $pI);
        $row->addCell(1*$U, $gs(1))->addText(' ' . ($s['dni_instructor'] ?? ''), $fN, $pI);

        $row = $table->addRow(470); // Row[13] h=470tw=8.3mm
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('APELLIDO Y NOMBRE', $fB, $pI);
        $row->addCell(7*$U, $gs(7))->addText(' ' . $apellidoAut, $fN, $pI);

        $row = $table->addRow(375); // Row[14] h=375tw=6.6mm
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('CARGO', $fB, $pI);
        $row->addCell(7*$U, $gs(7))->addText(' ' . $cargo . ' - CMN', $fN, $pI);

        // --- SECCIÓN D ---
        $row = $table->addRow(340); // Row[15] h=340tw=6.0mm
        $row->addCell(1*$U, $gsG(1))->addText('D', $fB, $pC);
        $row->addCell(18*$U, $gsG(18))->addText('ENTERADO DEL INFRACTOR', $fB, $pC);

        $txtRecursos = 'Usted tiene derecho a recurrir la presente sanción ante el superior inmediato de quien se la impuso -siguiendo la vía jerárquica-, para lo cual dispone de CINCO (5) días corridos. El vencimiento del plazo sin que se hubiere interpuesto recurso implica su aceptación de todo lo actuado. (Art 63 y 65 Anexo II Dec Nro 2666/12)';
        $row = $table->addRow(547); // Row[16] h=547tw=9.6mm
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(18*$U, $gs(18))->addText($txtRecursos, $fS, $pI);

        $txtObs = 'Observaciones o quejas: Dec 2666/12 Art 71 – 2. (Las observaciones o quejas efectuadas no se considerarán recursos. Todo recurso debe presentarse de acuerdo a lo establecido en el Art 29 y 30 del Anexo IV Ley 26.394 y Art 63 y 64 del Anexo II del Decreto 2666/12)';
        $row = $table->addRow(1326); // Row[17] h=1326tw=23.4mm
        $row->addCell(1*$U, $norm)->addText('2', $fB, $pC);
        $row->addCell(9*$U, $gs(9))->addText($txtObs, $fS, $pI);
        $row->addCell(9*$U, $gs(9))->addText('', $fN, $pI);

        $row = $table->addRow(520); // Row[18] h=520tw=9.2mm
        $row->addCell(1*$U, $norm)->addText('3', $fB, $pC);
        $row->addCell(5*$U, $gs(5))->addText('LUGAR y FECHA', $fB, $pI);
        $row->addCell(13*$U, $gs(13))->addText('EL PALOMAR,        de _____________ del Año _____.', $fN, $pI);

        $row = $table->addRow(514); // Row[19] h=514tw=9.1mm
        $row->addCell(1*$U, $norm)->addText('4', $fB, $pC);
        $row->addCell(5*$U, $gs(5))->addText('FECHA DE CUMPLIMIENTO', $fB, $pI);
        $row->addCell(13*$U, $gs(13))->addText('EL PALOMAR,        de _____________ del Año _____.', $fN, $pI);

        $row = $table->addRow(825); // Row[20] h=825tw=14.6mm
        $row->addCell(1*$U, $norm)->addText('5', $fB, $pC);
        $row->addCell(3*$U, $gs(3))->addText('FIRMA', $fB, $pC);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('ACLARACIÓN', $fB, $pC);
        $row->addCell(6*$U, $gs(6))->addText('', $fN, $pI);

        return $this->descargarDocx($phpWord, 'Sancion_cuadros_' . ($s['dni_infractor'] ?? 'doc'));
    }

    /**
     * Genera planilla de sanción CADETES (27 filas, secciones A-D + autoridad de revisión).
     * Spans exactos según template_planilla_CADETES.docx (19 columnas).
     * Col-unit = 500 twips → total tabla = 9500 twips.
     */
    private function generarSancionCadetesDocx(array $s, object $enc)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection(self::MARGEN_SECCION);

        $this->agregarEncabezadoInstitucional($section, $enc);
        $section->addText(
            'ANEXO 1 (PLANILLA IMPOSICIÓN DIRECTA DE SANCIÓN DISCIPLINARIA)',
            self::FUENTE_SUBTITULO, self::PARRAFO_IZQ
        );
        $section->addText('', self::FUENTE_SMALL, self::PARRAFO_IZQ);

        // Tabla 19 columnas, 500 twips/col → 9500 total
        $bordeTabla = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 40];
        $phpWord->addTableStyle('tSanCadetes', $bordeTabla);
        $table = $section->addTable('tSanCadetes');

        $U   = 500;
        // Color gris exacto del template (e6e6e6)
        $gris = ['bgColor' => 'E6E6E6', 'borderSize' => 6, 'borderColor' => '000000', 'valign' => 'center'];
        $norm = ['borderSize' => 6, 'borderColor' => '000000', 'valign' => 'center'];
        $fB  = ['bold' => true,  'size' => 10, 'name' => 'Times New Roman'];
        $fN  = ['bold' => false, 'size' => 10, 'name' => 'Times New Roman'];
        $fS  = ['bold' => false, 'size' => 8,  'name' => 'Times New Roman'];
        $pI  = self::PARRAFO_IZQ;
        $pC  = self::PARRAFO_CENTRO;
        $gs  = fn(int $n) => array_merge($norm, ['gridSpan' => $n]);
        $gsG = fn(int $n) => array_merge($gris, ['gridSpan' => $n]);

        $apellidoNombre  = trim(($s['apellido_infractor'] ?? '') . ', ' . ($s['nombre_infractor'] ?? ''), ', ');
        $cargo           = ($s['cargo_autoridad'] ?? '') ?: ($s['cargo_instructor'] ?? '');
        $apellidoAut     = trim(($s['apellido_instructor'] ?? '') . ', ' . ($s['nombre_instructor'] ?? ''), ', ');
        $apellidoRev     = $s['revisor_nombre'] ?? '';

        // --- SECCIÓN A --- (alturas exactas del template cadetes)
        $row = $table->addRow(340); // Row[0] h=340tw=6.0mm
        $row->addCell(1*$U, $gsG(1))->addText('A', $fB, $pC);
        $row->addCell(18*$U, $gsG(18))->addText('DATOS DEL INFRACTOR', $fB, $pC);

        $row = $table->addRow(340); // Row[1] h=340tw=6.0mm
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(1*$U, $gs(1))->addText('GRADO', $fB, $pI);
        $row->addCell(4*$U, $gs(4))->addText(' ' . ($s['grado_infractor'] ?? ''), $fN, $pI);
        $row->addCell(8*$U, $gs(8))->addText('ARMA / SERVICIO / ESPECIALIDAD', $fB, $pI);
        $row->addCell(5*$U, $gs(5))->addText(' ' . ($s['arma_infractor'] ?? ''), $fN, $pI);

        $row = $table->addRow(340); // Row[2] h=340tw=6.0mm
        $row->addCell(1*$U, $norm)->addText('2', $fB, $pC);
        $row->addCell(5*$U, $gs(5))->addText('APELLIDO Y NOMBRE', $fB, $pI);
        $row->addCell(13*$U, $gs(13))->addText(' ' . $apellidoNombre, $fN, $pI);

        $row = $table->addRow(350); // Row[3] h=350tw=6.2mm
        $row->addCell(1*$U, $norm)->addText('3', $fB, $pC);
        $row->addCell(5*$U, $gs(5))->addText('DESTINO INTERNO', $fB, $pI);
        $row->addCell(8*$U, $gs(8))->addText(' ' . ($s['destino_infractor'] ?? ''), $fN, $pI);
        $row->addCell(1*$U, $gs(1))->addText('DNI', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText(' ' . ($s['dni_infractor'] ?? ''), $fN, $pI);

        // --- SECCIÓN B ---
        $row = $table->addRow(350); // Row[4] h=350tw=6.2mm
        $row->addCell(1*$U, $gsG(1))->addText('B', $fB, $pC);
        $row->addCell(18*$U, $gsG(18))->addText('SANCIÓN DISCIPLINARIA', $fB, $pC);

        $row = $table->addRow(336); // Row[5] h=336tw=5.9mm
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(3*$U, $gs(3))->addText('FECHA COMISION FALTA', $fB, $pI);
        $row->addCell(15*$U, $gs(15))->addText(' ' . $this->formatearFecha($s['fecha_comision'] ?? ''), $fN, $pI);

        $row = $table->addRow(340); // Row[6] h=340tw=6.0mm
        $row->addCell(1*$U, $norm)->addText('2', $fB, $pC);
        $row->addCell(10*$U, $gs(10))->addText('Régimen Disciplinario Formativo para Cadetes', $fB, $pI);
        $row->addCell(6*$U, $gs(6))->addText(' Pto: ' . ($s['reg_act_dis'] ?? ''), $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText(' ' . ($s['inciso'] ?? ''), $fN, $pI);

        // Row[7] + Row[8] motivo - fila única con altura auto para texto largo
        $motivoTexto = ' ' . ($s['motivo'] ?? '');
        $row = $table->addRow(null); // altura automática según contenido
        $row->addCell(1*$U, $norm)->addText('3', $fB, $pC);
        $celdaLabelCad = $row->addCell(3*$U, array_merge($gs(3), ['valign' => 'top']));
        $celdaLabelCad->addText('MOTIVO DE LA SANCIÓN', $fB, $pI);
        $celdaMotivoCad = $row->addCell(15*$U, array_merge($gs(15), ['valign' => 'top']));
        $pMotivoCad = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'spaceAfter' => 0, 'spaceBefore' => 0];
        $celdaMotivoCad->addText($motivoTexto, $fN, $pMotivoCad);

        $row = $table->addRow(340); // Row[9] h=340tw=6.0mm
        $row->addCell(1*$U, $norm)->addText('4', $fB, $pC);
        $row->addCell(9*$U, $gs(9))->addText('SANCIÓN: Días de Arresto', $fB, $pI);
        $row->addCell(9*$U, $gs(9))->addText(' ' . ($s['duracion'] ?? '') . ' Días de Arresto Simple.', $fN, $pI);

        $row = $table->addRow(469); // Row[10] h=469tw=8.3mm
        $row->addCell(1*$U, $norm)->addText('5', $fB, $pC);
        $row->addCell(6*$U, $gs(6))->addText('LUGAR DE CUMPLIMIENTO', $fB, $pI);
        $row->addCell(12*$U, $gs(12))->addText(' ' . ($s['lugar_cumplimiento'] ?? 'A cumplir en la Unidad'), $fN, $pI);

        // --- SECCIÓN C: AUTORIDAD QUE IMPONE ---
        $row = $table->addRow(325); // Row[11] h=325tw=5.7mm
        $row->addCell(1*$U, $gsG(1))->addText('C', $fB, $pC);
        $row->addCell(18*$U, $gsG(18))->addText('AUTORIDAD QUE IMPONE DE LA SANCIÓN', $fB, $pC);

        $row = $table->addRow(393); // Row[12] h=393tw=6.9mm
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('FIRMA', $fB, $pC);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('GRADO', $fB, $pI);
        $row->addCell(4*$U, $gs(4))->addText(' ' . ($s['grado_instructor'] ?? ''), $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText('DNI', $fB, $pI);
        $row->addCell(1*$U, $gs(1))->addText(' ' . ($s['dni_instructor'] ?? ''), $fN, $pI);

        $row = $table->addRow(470); // Row[13] h=470tw=8.3mm
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText('', $fN, $pI);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('APELLIDO Y NOMBRE', $fB, $pI);
        $row->addCell(7*$U, $gs(7))->addText(' ' . $apellidoAut, $fN, $pI);

        $row = $table->addRow(375); // Row[14] h=375tw=6.6mm
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText('', $fN, $pI);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('CARGO', $fB, $pI);
        $row->addCell(7*$U, $gs(7))->addText(' ' . $cargo . ' - Colegio Militar de la Nación', $fN, $pI);

        // --- SECCIÓN D: ENTERADO ---
        $row = $table->addRow(340); // Row[15] h=340tw=6.0mm
        $row->addCell(1*$U, $gsG(1))->addText('D', $fB, $pC);
        $row->addCell(18*$U, $gsG(18))->addText('ENTERADO DEL INFRACTOR', $fB, $pC);

        $txtRec = 'Recursos para faltas académicas Leves y Graves: El infractor dispone de CINCO (05) días corridos a partir de la notificación de la sanción para presentar el recurso ante la autoridad que lo sancionó, quien deberá elevar a su Superior Jerárquico para que resuelva el mismo conforme lo establece el Punto 33 del Régimen Disciplinario de Cadetes.';
        $row = $table->addRow(547); // Row[16] h=547tw=9.6mm
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(18*$U, $gs(18))->addText($txtRec, $fS, $pI);

        $row = $table->addRow(520); // Row[17] h=520tw=9.2mm
        $row->addCell(1*$U, $norm)->addText('2', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('LUGAR y FECHA', $fB, $pI);
        $row->addCell(14*$U, $gs(14))->addText('EL PALOMAR,        de _____________ del Año _____.', $fN, $pI);

        $row = $table->addRow(514); // Row[18] h=514tw=9.1mm
        $row->addCell(1*$U, $norm)->addText('3', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('FECHA DE CUMPLIMIENTO', $fB, $pI);
        $row->addCell(14*$U, $gs(14))->addText('EL PALOMAR,        de _____________ del Año _____.', $fN, $pI);

        $row = $table->addRow(508); // Row[19] h=508tw=9.0mm
        $row->addCell(1*$U, $norm)->addText('4', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('FIRMA', $fB, $pC);
        $row->addCell(6*$U, $gs(6))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('ACLARACIÓN', $fB, $pC);
        $row->addCell(6*$U, $gs(6))->addText('', $fN, $pI);

        // --- SECCIÓN E: AUTORIDAD DE REVISIÓN ---
        $row = $table->addRow(325); // Row[20] h=325tw=5.7mm
        $row->addCell(1*$U, $gsG(1))->addText('E', $fB, $pC);
        $row->addCell(18*$U, $gsG(18))->addText('AUTORIDAD DE REVISIÓN', $fB, $pC);

        $row = $table->addRow(393); // Row[21] h=393tw=6.9mm
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('FIRMA', $fB, $pC);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('GRADO', $fB, $pI);
        $row->addCell(4*$U, $gs(4))->addText(' ' . ($s['revisor_grado'] ?? ''), $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText('DNI', $fB, $pI);
        $row->addCell(1*$U, $gs(1))->addText(' ' . ($s['revisor_dni'] ?? ''), $fN, $pI);

        $row = $table->addRow(470); // Row[22] h=470tw=8.3mm
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText('', $fN, $pI);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('APELLIDO Y NOMBRE', $fB, $pI);
        $row->addCell(7*$U, $gs(7))->addText(' ' . $apellidoRev, $fN, $pI);

        $row = $table->addRow(375); // Row[23] h=375tw=6.6mm
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText('', $fN, $pI);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('CARGO', $fB, $pI);
        $row->addCell(7*$U, $gs(7))->addText(' ' . ($s['revisor_cargo'] ?? '') . ' - Colegio Militar de la Nación', $fN, $pI);

        $txtAtrib = 'En ejercicio de sus atribuciones contenidas en el Punto 4 del RDC, considero necesario atento a las características particulares del hecho que la sanción sea:';
        $row = $table->addRow(805); // Row[24] h=805tw=14.2mm
        $row->addCell(1*$U, $norm)->addText('2', $fB, $pC);
        $row->addCell(18*$U, $gs(18))->addText($txtAtrib, $fS, $pI);

        $row = $table->addRow(805); // Row[25] h=805tw=14.2mm
        $cell = $row->addCell(1*$U, $norm);
        $cell->addText('3', $fB, $pC);
        $cell25 = $row->addCell(18*$U, $gs(18));
        $cell25->addText('CONFIRMADA.', $fN, $pI);
        $cell25->addText('AUMENTADA EN: ________', $fN, $pI);
        $cell25->addText('DISMINUIDA EN: ________', $fN, $pI);
        $cell25->addText('DEJADA SIN EFECTO.', $fN, $pI);
        $cell25->addText('Tachar lo que no corresponda.', $fS, $pI);

        $row = $table->addRow(508); // Row[26] h=508tw=9.0mm
        $row->addCell(1*$U, $norm)->addText('4', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('FIRMA', $fB, $pC);
        $row->addCell(6*$U, $gs(6))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('ACLARACIÓN', $fB, $pC);
        $row->addCell(6*$U, $gs(6))->addText('', $fN, $pI);

        return $this->descargarDocx($phpWord, 'Sancion_cadetes_' . ($s['dni_infractor'] ?? 'doc'));
    }

    /**
     * Genera planilla REVISIÓN DE OFICIO para Cuadros (ANEXO 2).
     * Spans exactos según template_planilla_REVISION_CUADROS.docx (22 columnas).
     * Col-unit = 430 twips → total tabla = 9460 twips.
     */
    public function generarSancionRevisionCuadrosDocx(array $s, object $enc)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection(self::MARGEN_SECCION);

        $this->agregarEncabezadoInstitucional($section, $enc, $s['letra'] ?? '', $s['nro'] ?? '');
        $section->addText('', self::FUENTE_SMALL, self::PARRAFO_IZQ);
        $section->addText('ANEXO 2 (PLANILLA DE SANCIÓN DISCIPLINARIA)', self::FUENTE_SUBTITULO, self::PARRAFO_CENTRO);
        $section->addText('', self::FUENTE_SMALL, self::PARRAFO_IZQ);
        $section->addText('    REVISIÓN DE OFICIO.', self::FUENTE_NORMAL, self::PARRAFO_IZQ);
        $section->addText('', self::FUENTE_SMALL, self::PARRAFO_IZQ);

        // Tabla 22 columnas, 430 twips/col → 9460 total
        $bordeTabla = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 40];
        $phpWord->addTableStyle('tSanRevision', $bordeTabla);
        $table = $section->addTable('tSanRevision');

        $U    = 430;
        // Color gris exacto del template (e6e6e6)
        $gris = ['bgColor' => 'E6E6E6', 'borderSize' => 6, 'borderColor' => '000000', 'valign' => 'center'];
        $norm = ['borderSize' => 6, 'borderColor' => '000000', 'valign' => 'center'];
        $fB   = ['bold' => true,  'size' => 10, 'name' => 'Times New Roman'];
        $fN   = ['bold' => false, 'size' => 10, 'name' => 'Times New Roman'];
        $fS   = ['bold' => false, 'size' => 8,  'name' => 'Times New Roman'];
        $pI   = self::PARRAFO_IZQ;
        $pC   = self::PARRAFO_CENTRO;
        $gs   = fn(int $n) => array_merge($norm, ['gridSpan' => $n]);
        $gsG  = fn(int $n) => array_merge($gris, ['gridSpan' => $n]);

        $apellidoNombre = trim(($s['apellido_infractor'] ?? '') . ', ' . ($s['nombre_infractor'] ?? ''), ', ');
        $apellidoRev    = $s['revisor_nombre'] ?? '';

        // === SECCIÓN A: DATOS DEL INFRACTOR (22 columnas) ===
        // Row[0]: [1]'A' + [21]'DATOS DEL INFRACTOR'
        $row = $table->addRow(300);
        $row->addCell(1*$U, $gsG(1))->addText('A', $fB, $pC);
        $row->addCell(21*$U, $gsG(21))->addText('DATOS DEL INFRACTOR', $fB, $pC);

        // Row[1]: [1]'1' + [2]'GRADO' + [2]grado + [8]'ARMA / SERVICIO / ESPECIALIDAD' + [9]arma
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('GRADO', $fB, $pI);
        $row->addCell(2*$U, $gs(2))->addText(' ' . ($s['grado_infractor'] ?? ''), $fN, $pI);
        $row->addCell(8*$U, $gs(8))->addText('ARMA / SERVICIO / ESPECIALIDAD', $fB, $pI);
        $row->addCell(9*$U, $gs(9))->addText(' ' . ($s['arma_infractor'] ?? ''), $fN, $pI);

        // Row[2]: [1]'2' + [4]'APELLIDO Y NOMBRE' + [17]apellido, nombre
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('2', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('APELLIDO Y NOMBRE', $fB, $pI);
        $row->addCell(17*$U, $gs(17))->addText(' ' . $apellidoNombre, $fN, $pI);

        // Row[3]: [1]'3' + [4]'DESTINO INTERNO' + [9]destino + [1]'DNI' + [7]dni
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('3', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('DESTINO INTERNO', $fB, $pI);
        $row->addCell(9*$U, $gs(9))->addText(' ' . ($s['destino_infractor'] ?? ''), $fN, $pI);
        $row->addCell(1*$U, $gs(1))->addText('DNI', $fB, $pC);
        $row->addCell(7*$U, $gs(7))->addText(' ' . ($s['dni_infractor'] ?? ''), $fN, $pI);

        // === SECCIÓN B: INSTANCIA DE REVISIÓN DE OFICIO ===
        // Row[4]: [1]'B' + [21]'INSTANCIA DE REVISIÓN DE OFICIO'
        $row = $table->addRow(300);
        $row->addCell(1*$U, $gsG(1))->addText('B', $fB, $pC);
        $row->addCell(21*$U, $gsG(21))->addText('INSTANCIA DE REVISIÓN DE OFICIO', $fB, $pC);

        // Row[5]: [1]'1' + [5]'INFORMACIÓN DE LA SANCIÓN' + [5]'EXPEDIENTE NRO' + [6]'DÍA' + [3]'MES' + [2]'AÑO'
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(5*$U, $gs(5))->addText('INFORMACIÓN DE LA SANCIÓN', $fB, $pI);
        $row->addCell(5*$U, $gs(5))->addText('EXPEDIENTE NRO', $fB, $pC);
        $row->addCell(6*$U, $gs(6))->addText('DÍA', $fB, $pC);
        $row->addCell(3*$U, $gs(3))->addText('MES', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('AÑO', $fB, $pC);

        // Row[6]: datos de la sanción (blancos para completar)
        $row = $table->addRow(400);
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(6*$U, $gs(6))->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('', $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText('', $fN, $pI);

        // Row[7]: [1]'2' + [10]'RECEPCIÓN DE LA SANCIÓN DISCIPLINARIA' + [6]'DÍA' + [3]'MES' + [2]'AÑO'
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('2', $fB, $pC);
        $row->addCell(10*$U, $gs(10))->addText('RECEPCIÓN DE LA SANCIÓN DISCIPLINARIA', $fB, $pI);
        $row->addCell(6*$U, $gs(6))->addText('DÍA', $fB, $pC);
        $row->addCell(3*$U, $gs(3))->addText('MES', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('AÑO', $fB, $pC);

        // Row[8]: blancos para completar
        $row = $table->addRow(400);
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(10*$U, $gs(10))->addText('', $fN, $pI);
        $row->addCell(6*$U, $gs(6))->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('', $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText('', $fN, $pI);

        // Row[9]: [1]'3' + [21]'MODIFICACIÓN DE LA SANCIÓN'
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('3', $fB, $pC);
        $row->addCell(21*$U, $gs(21))->addText('MODIFICACIÓN DE LA SANCIÓN', $fB, $pI);

        // Row[10]: [1]'3' + [3]'NO' + [18]''
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('NO', $fB, $pC);
        $row->addCell(18*$U, $gs(18))->addText('', $fN, $pI);

        // Row[11]: [1]'3' + [3]'NO' + [8]'DISMINUCIÓN' + [10]'AUMENTO'
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('', $fN, $pI);
        $row->addCell(8*$U, $gs(8))->addText('DISMINUCIÓN', $fB, $pC);
        $row->addCell(10*$U, $gs(10))->addText('AUMENTO', $fB, $pC);

        // Row[12]: TIPO
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('TIPO', $fB, $pI);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(9*$U, $gs(9))->addText('', $fN, $pI);
        $row->addCell(1*$U, $gs(1))->addText('', $fN, $pI);

        // Row[13]: DURACIÓN
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('DURACIÓN', $fB, $pI);
        $row->addCell(5*$U, $gs(5))->addText('', $fN, $pI);
        $row->addCell(9*$U, $gs(9))->addText('', $fN, $pI);
        $row->addCell(1*$U, $gs(1))->addText('             -------------', $fN, $pC);

        // Row[14]: [1]'4' + [3]'FUNDAMENTO' + [18]''
        $row = $table->addRow(500);
        $row->addCell(1*$U, $norm)->addText('4', $fB, $pC);
        $row->addCell(3*$U, $gs(3))->addText('FUNDAMENTO', $fB, $pI);
        $row->addCell(18*$U, $gs(18))->addText('', $fN, $pI);

        // Row[15]: [1]'5' + [1]'FIRMA' + [4]'' + [2]'GRADO' + [8]grado_rev + [3]'DNI' + [3]dni_rev
        $row = $table->addRow(500);
        $row->addCell(1*$U, $norm)->addText('5', $fB, $pC);
        $row->addCell(1*$U, $gs(1))->addText('FIRMA', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('', $fN, $pI);
        $row->addCell(2*$U, $gs(2))->addText('GRADO', $fB, $pI);
        $row->addCell(8*$U, $gs(8))->addText(' ' . ($s['revisor_grado'] ?? ''), $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('DNI', $fB, $pI);
        $row->addCell(3*$U, $gs(3))->addText(' ' . ($s['revisor_dni'] ?? ''), $fN, $pI);

        // Row[16]: [1]'5' + [1]'FIRMA' + [4]'' + [6]'APELLIDO Y NOMBRE' + [10]apellido_rev
        $row = $table->addRow(400);
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(1*$U, $gs(1))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('', $fN, $pI);
        $row->addCell(6*$U, $gs(6))->addText('APELLIDO Y NOMBRE', $fB, $pI);
        $row->addCell(10*$U, $gs(10))->addText(' ' . $apellidoRev, $fN, $pI);

        // Row[17]: otra fila apellido/nombre (template lo tiene como dos filas)
        $row = $table->addRow(400);
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(1*$U, $gs(1))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('', $fN, $pI);
        $row->addCell(6*$U, $gs(6))->addText('', $fN, $pI);
        $row->addCell(10*$U, $gs(10))->addText('', $fN, $pI);

        // Row[18]: [1]'5' + [1]'FIRMA' + [4]'' + [3]'CARGO' + [13]cargo_rev
        $row = $table->addRow(400);
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(1*$U, $gs(1))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('CARGO', $fB, $pI);
        $row->addCell(13*$U, $gs(13))->addText(' ' . ($s['revisor_cargo'] ?? ''), $fN, $pI);

        // === SECCIÓN C: NOTIFICACIÓN AL INFRACTOR ===
        // Row[19]: [1]'C' + [21]'NOTIFICACIÓN AL INFRACTOR EN CASO DE MODIFICACIÓN'
        $row = $table->addRow(300);
        $row->addCell(1*$U, $gsG(1))->addText('C', $fB, $pC);
        $row->addCell(21*$U, $gsG(21))->addText('NOTIFICACIÓN AL INFRACTOR EN CASO DE MODIFICACIÓN', $fB, $pC);

        // Row[20]: [1]'1' + [6]'OPORTUNIDAD DE NOTIFICACIÓN' + [4]'DÍA' + [7]'MES' + [4]'AÑO'
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('1', $fB, $pC);
        $row->addCell(6*$U, $gs(6))->addText('OPORTUNIDAD DE NOTIFICACIÓN', $fB, $pI);
        $row->addCell(4*$U, $gs(4))->addText('DÍA', $fB, $pC);
        $row->addCell(7*$U, $gs(7))->addText('MES', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('AÑO', $fB, $pC);

        // Row[21]: blancos
        $row = $table->addRow(400);
        $row->addCell(1*$U, $norm)->addText('', $fN, $pI);
        $row->addCell(6*$U, $gs(6))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('', $fN, $pI);
        $row->addCell(7*$U, $gs(7))->addText('', $fN, $pI);
        $row->addCell(4*$U, $gs(4))->addText('', $fN, $pI);

        // Row[22]: [1]'2' + [2]'FIRMA' + [4]'' + [3]'ACLARACIÓN' + [12]''
        $row = $table->addRow(700);
        $row->addCell(1*$U, $norm)->addText('2', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('FIRMA', $fB, $pC);
        $row->addCell(4*$U, $gs(4))->addText('', $fN, $pI);
        $row->addCell(3*$U, $gs(3))->addText('ACLARACIÓN', $fB, $pC);
        $row->addCell(12*$U, $gs(12))->addText('', $fN, $pI);

        // Row[23]: [1]'3' + [2]'LUGAR' + [19]'EL PALOMAR,...'
        $row = $table->addRow(300);
        $row->addCell(1*$U, $norm)->addText('3', $fB, $pC);
        $row->addCell(2*$U, $gs(2))->addText('LUGAR', $fB, $pI);
        $row->addCell(19*$U, $gs(19))->addText('EL PALOMAR,               de _____________ del Año _____.', $fN, $pI);

        return $this->descargarDocx($phpWord, 'Revision_cuadros_' . ($s['dni_infractor'] ?? 'doc'));
    }

    private function generarSancionPDF(array $s, object $enc, string $tipo)
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('JUSEA CMN v2.0');
        $pdf->SetAuthor('Colegio Militar de la Nación');
        $pdf->SetTitle('Sanción Disciplinaria');
        $pdf->SetMargins(10, 12, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->setCellPaddings(0.5, 0.5, 0.5, 0.5);

        // Encabezado
        $pdf->SetFont('times', 'BI', 16);
        $pdf->Cell(0, 8, 'Ejército Argentino', 0, 1, 'L');
        $pdf->SetFont('times', 'I', 9);
        $pdf->Cell(0, 5, $enc->membrete ?? '', 0, 1, 'L');
        $pdf->SetFont('times', 'I', 14);
        $pdf->Cell(0, 7, $enc->unidad ?? '', 0, 1, 'L');
        $pdf->Ln(3);

        $tituloAnexo = ($tipo === 'cadetes')
            ? 'ANEXO 1 (PLANILLA IMPOSICIÓN DIRECTA DE SANCIÓN DISCIPLINARIA - CADETES)'
            : 'ANEXO 1 (PLANILLA IMPOSICIÓN DIRECTA DE SANCIÓN DISCIPLINARIA)';
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell(0, 7, $tituloAnexo, 0, 1, 'L');
        $pdf->Ln(2);

        if ($tipo === 'cadetes') {
            $this->tablaSancionCadetesPDF($pdf, $s);
        } else {
            $this->tablaSancionCuadrosPDF($pdf, $s);
        }

        return $this->descargarPDF($pdf, 'Sancion_' . $tipo . '_' . ($s['dni_infractor'] ?? 'doc'));
    }

    /**
     * Tabla PDF para sanción CUADROS (secciones A-D).
     * Estructura exacta del template_planilla_CUADROS.docx.
     * 19 columnas × 10 mm = 190 mm ancho total.
     */
    private function tablaSancionCuadrosPDF(\TCPDF $pdf, array $s): void
    {
        $u  = 10;  // mm por columna (19 cols × 10 = 190 mm)
        $h  = 6;   // altura de fila estándar (mm)
        $hC = 8;   // altura de filas sección C (firma autoridad)
        $hM = 12;  // altura de fila motivo

        // Textos derivados
        $apellidoNombre = trim(($s['apellido_infractor'] ?? '') . ', ' . ($s['nombre_infractor'] ?? ''), ', ');
        $cargo          = ($s['cargo_autoridad'] ?? '') ?: ($s['cargo_instructor'] ?? '');
        $apellidoAut    = trim(($s['apellido_instructor'] ?? '') . ', ' . ($s['nombre_instructor'] ?? ''), ', ');
        $regArt         = 'Art. ' . ($s['reg_act_dis'] ?? '') . ' CDFFAA';
        $inciso         = 'Inc ' . ($s['inciso'] ?? '');

        $txtRecursos = 'Usted tiene derecho a recurrir la presente sanción ante el superior inmediato de quien se la impuso -siguiendo la vía jerárquica-, para lo cual dispone de CINCO (5) días corridos. El vencimiento del plazo sin que se hubiere interpuesto recurso implica su aceptación de todo lo actuado. (Art 63 y 65 Anexo II Dec Nro 2666/12)';
        $txtObs      = 'Observaciones o quejas: Dec 2666/12 Art 71 – 2. (Las observaciones o quejas efectuadas no se considerarán recursos. Todo recurso debe presentarse de acuerdo a lo establecido en el Art 29 y 30 del Anexo IV Ley 26.394 y Art 63 y 64 del Anexo II del Decreto 2666/12)';

        // ── A: DATOS DEL INFRACTOR ───────────────────────────────────────────
        $this->fCabPDF($pdf, $u, $h, 'A', 'DATOS DEL INFRACTOR');

        $this->fRowPDF($pdf, $h, [
            [$u,    '1',                                    'B', 'C'],
            [$u*2,  'GRADO',                                'B', 'L'],
            [$u*2,  ' ' . ($s['grado_infractor'] ?? ''),    'N', 'L'],
            [$u*9,  'ARMA / SERVICIO / ESPECIALIDAD',       'B', 'L'],
            [$u*5,  ' ' . ($s['arma_infractor'] ?? ''),     'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '2',                                    'B', 'C'],
            [$u*4,  'APELLIDO Y NOMBRE',                    'B', 'L'],
            [$u*14, ' ' . $apellidoNombre,                  'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '3',                                    'B', 'C'],
            [$u*4,  'DESTINO INTERNO',                      'B', 'L'],
            [$u*9,  ' ' . ($s['destino_infractor'] ?? ''),  'N', 'L'],
            [$u,    'DNI',                                  'B', 'C'],
            [$u*4,  ' ' . ($s['dni_infractor'] ?? ''),      'N', 'L'],
        ]);

        // ── B: SANCIÓN DISCIPLINARIA ──────────────────────────────────────────
        $this->fCabPDF($pdf, $u, $h, 'B', 'SANCIÓN DISCIPLINARIA');

        $this->fRowPDF($pdf, $h, [
            [$u,    '1',                                                         'B', 'C'],
            [$u*4,  'FECHA COMISION FALTA',                                      'B', 'L'],
            [$u*14, ' ' . $this->formatearFecha($s['fecha_comision'] ?? ''),     'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '2',                                    'B', 'C'],
            [$u*10, 'RÉGIMEN DE ACTUACIÓN DISCIPLINARIA',   'B', 'L'],
            [$u*6,  ' ' . $regArt,                          'N', 'L'],
            [$u*2,  ' ' . $inciso,                          'N', 'L'],
        ]);
        // Motivo cuadros: altura dinámica para texto largo
        $motivoTextPDF = ' ' . ($s['motivo'] ?? '');
        $pdf->SetFont('times', '', 10);
        $hMotivoAuto = max($hM, $pdf->getStringHeight($u * 14, $motivoTextPDF));
        $pdf->SetFont('times', 'B', 10);
        $pdf->Cell($u,     $hMotivoAuto, '3',                    1, 0, 'C', false, '', 1);
        $pdf->Cell($u * 4, $hMotivoAuto, 'MOTIVO DE LA SANCIÓN', 1, 0, 'L', false, '', 1);
        $pdf->SetFont('times', '', 10);
        $pdf->MultiCell($u * 14, 0, $motivoTextPDF, 1, 'L', false, 1);
        $this->fRowPDF($pdf, $h, [
            [$u,    '4',             'B', 'C'],
            [$u,    'TIPO',          'B', 'L'],
            [$u*9,  'ARRESTO SIMPLE','B', 'C'],
            [$u*2,  'DURACIÓN',      'B', 'L'],
            [$u*6,  ' ' . ($s['duracion'] ?? ''), 'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '5',                                                                     'B', 'C'],
            [$u*6,  'LUGAR DE CUMPLIMIENTO',                                                 'B', 'L'],
            [$u*12, ' ' . ($s['lugar_cumplimiento'] ?? 'Colegio Militar de la Nación'),     'N', 'L'],
        ]);

        // ── C: AUTORIDAD QUE IMPONE DE LA SANCIÓN ───────────────────────────
        $this->fCabPDF($pdf, $u, $h, 'C', 'AUTORIDAD QUE IMPONE DE LA SANCIÓN');

        // C1: num + FIRMA (col 2-4 vacía) + GRADO + grado + DNI + dni
        $this->fRowPDF($pdf, $hC, [
            [$u,    '1',                                    'B', 'C'],
            [$u*3,  'FIRMA',                                'B', 'C'],
            [$u*4,  '',                                     'N', 'L'],
            [$u*4,  'GRADO',                                'B', 'L'],
            [$u*4,  ' ' . ($s['grado_instructor'] ?? ''),   'N', 'L'],
            [$u*2,  'DNI',                                  'B', 'L'],
            [$u,    ' ' . ($s['dni_instructor'] ?? ''),     'N', 'L'],
        ]);
        // C2: firma continúa + APELLIDO Y NOMBRE
        $this->fRowPDF($pdf, $h, [
            [$u,    '', 'N', 'C'],
            [$u*3,  '', 'N', 'L'],
            [$u*4,  '', 'N', 'L'],
            [$u*4,  'APELLIDO Y NOMBRE',   'B', 'L'],
            [$u*7,  ' ' . $apellidoAut,    'N', 'L'],
        ]);
        // C3: firma continúa + CARGO
        $this->fRowPDF($pdf, $h, [
            [$u,    '', 'N', 'C'],
            [$u*3,  '', 'N', 'L'],
            [$u*4,  '', 'N', 'L'],
            [$u*4,  'CARGO',                        'B', 'L'],
            [$u*7,  ' ' . $cargo . ' - CMN',        'N', 'L'],
        ]);

        // ── D: ENTERADO DEL INFRACTOR ─────────────────────────────────────────
        $this->fCabPDF($pdf, $u, $h, 'D', 'ENTERADO DEL INFRACTOR');

        // D1: texto recursos (auto-alto, fuente pequeña)
        $pdf->SetFont('times', '', 7);
        $hD1 = max(10, $pdf->getStringHeight($u * 18, $txtRecursos));
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell($u, $hD1, '1', 1, 0, 'C');
        $pdf->SetFont('times', '', 7);
        $pdf->MultiCell($u * 18, 0, $txtRecursos, 1, 'L', false, 1);

        // D2: texto observaciones + celda en blanco a la derecha (auto-alto)
        $pdf->SetFont('times', '', 7);
        $hD2 = max(10, $pdf->getStringHeight($u * 9, $txtObs));
        $y2  = $pdf->GetY();
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell($u, $hD2, '2', 1, 0, 'C');
        $xObs = $pdf->GetX();
        $pdf->SetFont('times', '', 7);
        $pdf->MultiCell($u * 9, 0, $txtObs, 1, 'L', false, 1);
        // Celda en blanco derecha
        $pdf->SetXY($xObs + $u * 9, $y2);
        $pdf->SetFont('times', '', 9);
        $pdf->Cell($u * 9, $hD2, '', 1, 1, 'C');

        // D3: LUGAR y FECHA
        $this->fRowPDF($pdf, $h, [
            [$u,    '3',               'B', 'C'],
            [$u*5,  'LUGAR y FECHA',   'B', 'L'],
            [$u*13, 'EL PALOMAR,        de _____________ del Año _____.', 'N', 'L'],
        ]);
        // D4: FECHA DE CUMPLIMIENTO
        $this->fRowPDF($pdf, $h, [
            [$u,    '4',                       'B', 'C'],
            [$u*5,  'FECHA DE CUMPLIMIENTO',   'B', 'L'],
            [$u*13, 'EL PALOMAR,        de _____________ del Año _____.', 'N', 'L'],
        ]);
        // D5: FIRMA / ACLARACIÓN infractor
        $this->fRowPDF($pdf, $h * 2, [
            [$u,    '5',         'B', 'C'],
            [$u*3,  'FIRMA',     'B', 'C'],
            [$u*5,  '',          'N', 'L'],
            [$u*4,  'ACLARACIÓN','B', 'C'],
            [$u*6,  '',          'N', 'L'],
        ]);
    }

    /**
     * Tabla PDF para sanción CADETES (secciones A-D + Autoridad de Revisión).
     * Estructura exacta del template_planilla_CADETES.docx.
     * 19 columnas × 10 mm = 190 mm ancho total.
     */
    private function tablaSancionCadetesPDF(\TCPDF $pdf, array $s): void
    {
        $u  = 10;
        $h  = 6;
        $hC = 8;
        $hM = 12;

        $apellidoNombre = trim(($s['apellido_infractor'] ?? '') . ', ' . ($s['nombre_infractor'] ?? ''), ', ');
        $cargo          = ($s['cargo_autoridad'] ?? '') ?: ($s['cargo_instructor'] ?? '');
        $apellidoAut    = trim(($s['apellido_instructor'] ?? '') . ', ' . ($s['nombre_instructor'] ?? ''), ', ');
        $apellidoRev    = $s['revisor_nombre'] ?? '';
        $pto            = 'Pto: ' . ($s['reg_act_dis'] ?? '');
        $inciso         = $s['inciso'] ?? '';

        $txtRec   = 'Recursos para faltas académicas Leves y Graves: El infractor dispone de CINCO (05) días corridos a partir de la notificación de la sanción para presentar el recurso ante la autoridad que lo sancionó, quien deberá elevar a su Superior Jerárquico para que resuelva el mismo conforme lo establece el Punto 33 del Régimen Disciplinario de Cadetes.';
        $txtAtrib = 'En ejercicio de sus atribuciones contenidas en el Punto 4 del RDC, considero necesario atento a las características particulares del hecho que la sanción sea:';
        $txtResol = "CONFIRMADA.\nAUMENTADA EN: ________\nDISMINUIDA EN: ________\nDEJADA SIN EFECTO.\n(Tachar lo que no corresponda)";

        // ── A: DATOS DEL INFRACTOR ───────────────────────────────────────────
        $this->fCabPDF($pdf, $u, $h, 'A', 'DATOS DEL INFRACTOR');

        $this->fRowPDF($pdf, $h, [
            [$u,    '1',                                    'B', 'C'],
            [$u,    'GRADO',                                'B', 'L'],
            [$u*4,  ' ' . ($s['grado_infractor'] ?? ''),    'N', 'L'],
            [$u*8,  'ARMA / SERVICIO / ESPECIALIDAD',       'B', 'L'],
            [$u*5,  ' ' . ($s['arma_infractor'] ?? ''),     'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '2',                                    'B', 'C'],
            [$u*5,  'APELLIDO Y NOMBRE',                    'B', 'L'],
            [$u*13, ' ' . $apellidoNombre,                  'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '3',                                    'B', 'C'],
            [$u*5,  'DESTINO INTERNO',                      'B', 'L'],
            [$u*8,  ' ' . ($s['destino_infractor'] ?? ''),  'N', 'L'],
            [$u,    'DNI',                                  'B', 'C'],
            [$u*4,  ' ' . ($s['dni_infractor'] ?? ''),      'N', 'L'],
        ]);

        // ── B: SANCIÓN DISCIPLINARIA ──────────────────────────────────────────
        $this->fCabPDF($pdf, $u, $h, 'B', 'SANCIÓN DISCIPLINARIA');

        $this->fRowPDF($pdf, $h, [
            [$u,    '1',                                                         'B', 'C'],
            [$u*3,  'FECHA COMISION FALTA',                                      'B', 'L'],
            [$u*15, ' ' . $this->formatearFecha($s['fecha_comision'] ?? ''),     'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '2',                                                'B', 'C'],
            [$u*10, 'Régimen Disciplinario Formativo para Cadetes',     'B', 'L'],
            [$u*6,  ' ' . $pto,                                         'N', 'L'],
            [$u*2,  ' ' . $inciso,                                      'N', 'L'],
        ]);
        // Motivo cadetes: altura dinámica para texto largo
        $motivoTextCadPDF = ' ' . ($s['motivo'] ?? '');
        $pdf->SetFont('times', '', 10);
        $hMotivoCadAuto = max($hM, $pdf->getStringHeight($u * 15, $motivoTextCadPDF));
        $pdf->SetFont('times', 'B', 10);
        $pdf->Cell($u,     $hMotivoCadAuto, '3', 1, 0, 'C', false, '', 1);
        $pdf->Cell($u * 3, $hMotivoCadAuto, '',  1, 0, 'L', false, '', 1);
        $pdf->SetFont('times', '', 10);
        $pdf->MultiCell($u * 15, 0, $motivoTextCadPDF, 1, 'L', false, 1);
        $this->fRowPDF($pdf, $h, [
            [$u,    '4',                                'B', 'C'],
            [$u*9,  'SANCIÓN: Días de Arresto',         'B', 'L'],
            [$u*9,  ' ' . ($s['duracion'] ?? '') . ' Días de Arresto Simple.', 'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '5',                                                                    'B', 'C'],
            [$u*6,  'LUGAR DE CUMPLIMIENTO',                                                'B', 'L'],
            [$u*12, ' ' . ($s['lugar_cumplimiento'] ?? 'A cumplir en la Unidad'),          'N', 'L'],
        ]);

        // ── C: AUTORIDAD QUE IMPONE DE LA SANCIÓN ───────────────────────────
        $this->fCabPDF($pdf, $u, $h, 'C', 'AUTORIDAD QUE IMPONE DE LA SANCIÓN');

        $this->fRowPDF($pdf, $hC, [
            [$u,    '1',                                    'B', 'C'],
            [$u*2,  'FIRMA',                                'B', 'C'],
            [$u*5,  '',                                     'N', 'L'],
            [$u*4,  'GRADO',                                'B', 'L'],
            [$u*4,  ' ' . ($s['grado_instructor'] ?? ''),   'N', 'L'],
            [$u*2,  'DNI',                                  'B', 'L'],
            [$u,    ' ' . ($s['dni_instructor'] ?? ''),     'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '', 'N', 'C'],
            [$u*2,  '', 'N', 'L'],
            [$u*5,  '', 'N', 'L'],
            [$u*4,  'APELLIDO Y NOMBRE',   'B', 'L'],
            [$u*7,  ' ' . $apellidoAut,    'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '', 'N', 'C'],
            [$u*2,  '', 'N', 'L'],
            [$u*5,  '', 'N', 'L'],
            [$u*4,  'CARGO',                                                'B', 'L'],
            [$u*7,  ' ' . $cargo . ' - Colegio Militar de la Nación',      'N', 'L'],
        ]);

        // ── D: ENTERADO DEL INFRACTOR ─────────────────────────────────────────
        $this->fCabPDF($pdf, $u, $h, 'D', 'ENTERADO DEL INFRACTOR');

        // D1: texto recursos cadetes (auto-alto, fuente pequeña)
        $pdf->SetFont('times', '', 7);
        $hD1 = max(10, $pdf->getStringHeight($u * 18, $txtRec));
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell($u, $hD1, '1', 1, 0, 'C');
        $pdf->SetFont('times', '', 7);
        $pdf->MultiCell($u * 18, 0, $txtRec, 1, 'L', false, 1);

        $this->fRowPDF($pdf, $h, [
            [$u,    '2',               'B', 'C'],
            [$u*4,  'LUGAR y FECHA',   'B', 'L'],
            [$u*14, 'EL PALOMAR,        de _____________ del Año _____.', 'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '3',                       'B', 'C'],
            [$u*4,  'FECHA DE CUMPLIMIENTO',   'B', 'L'],
            [$u*14, 'EL PALOMAR,        de _____________ del Año _____.', 'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h * 2, [
            [$u,    '4',         'B', 'C'],
            [$u*2,  'FIRMA',     'B', 'C'],
            [$u*6,  '',          'N', 'L'],
            [$u*4,  'ACLARACIÓN','B', 'C'],
            [$u*6,  '',          'N', 'L'],
        ]);

        // ── E: AUTORIDAD DE REVISIÓN ──────────────────────────────────────────
        $this->fCabPDF($pdf, $u, $h, 'E', 'AUTORIDAD DE REVISIÓN');

        $this->fRowPDF($pdf, $hC, [
            [$u,    '1',                                    'B', 'C'],
            [$u*2,  'FIRMA',                                'B', 'C'],
            [$u*5,  '',                                     'N', 'L'],
            [$u*4,  'GRADO',                                'B', 'L'],
            [$u*4,  ' ' . ($s['revisor_grado'] ?? ''),      'N', 'L'],
            [$u*2,  'DNI',                                  'B', 'L'],
            [$u,    ' ' . ($s['revisor_dni'] ?? ''),        'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '', 'N', 'C'],
            [$u*2,  '', 'N', 'L'],
            [$u*5,  '', 'N', 'L'],
            [$u*4,  'APELLIDO Y NOMBRE',       'B', 'L'],
            [$u*7,  ' ' . $apellidoRev,        'N', 'L'],
        ]);
        $this->fRowPDF($pdf, $h, [
            [$u,    '', 'N', 'C'],
            [$u*2,  '', 'N', 'L'],
            [$u*5,  '', 'N', 'L'],
            [$u*4,  'CARGO',                                                        'B', 'L'],
            [$u*7,  ' ' . ($s['revisor_cargo'] ?? 'Director') . ' - Colegio Militar de la Nación', 'N', 'L'],
        ]);

        // E4: texto atribuciones (auto-alto, fuente pequeña)
        $pdf->SetFont('times', '', 7);
        $hAt = max(8, $pdf->getStringHeight($u * 18, $txtAtrib));
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell($u, $hAt, '2', 1, 0, 'C');
        $pdf->SetFont('times', '', 7);
        $pdf->MultiCell($u * 18, 0, $txtAtrib, 1, 'L', false, 1);

        // E5: resolución (multiline)
        $pdf->SetFont('times', '', 9);
        $hR = max(20, $pdf->getStringHeight($u * 18, $txtResol));
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell($u, $hR, '3', 1, 0, 'C');
        $pdf->SetFont('times', '', 9);
        $pdf->MultiCell($u * 18, 0, $txtResol, 1, 'L', false, 1);

        // E6: firma revisor
        $this->fRowPDF($pdf, $h * 2, [
            [$u,    '4',         'B', 'C'],
            [$u*2,  'FIRMA',     'B', 'C'],
            [$u*6,  '',          'N', 'L'],
            [$u*4,  'ACLARACIÓN','B', 'C'],
            [$u*6,  '',          'N', 'L'],
        ]);
    }

    /**
     * Fila de cabecera de sección con fondo gris.
     * Siempre: [1 col] letra + [18 cols] título.
     */
    private function fCabPDF(\TCPDF $pdf, float $u, float $h, string $letra, string $titulo): void
    {
        $pdf->SetFont('times', 'B', 10);
        $pdf->SetFillColor(230, 230, 230); // E6E6E6 — color exacto del template
        // stretch=1: escala horizontal solo si el texto supera el ancho de celda (auto-encoge, no distorsiona)
        $pdf->Cell($u,      $h, $letra,  1, 0, 'C', true, '', 1);
        $pdf->Cell($u * 18, $h, $titulo, 1, 1, 'C', true, '', 1);
    }

    /**
     * Fila de datos: cada elemento es [ancho_mm, texto, estilo ('B'|'N'), alineacion ('C'|'L'|'R')].
     * El último elemento usa ln=1 (salto de línea); los demás avanzan en X.
     * stretch=1: escala horizontal solo si el texto supera el ancho (auto-encoge sin distorsionar).
     */
    private function fRowPDF(\TCPDF $pdf, float $h, array $celdas): void
    {
        $ultimo = count($celdas) - 1;
        foreach ($celdas as $i => [$w, $txt, $style, $align]) {
            $pdf->SetFont('times', $style === 'B' ? 'B' : '', 10);
            $pdf->Cell($w, $h, $txt, 1, $i === $ultimo ? 1 : 0, $align, false, '', 1);
        }
    }

    // =========================================================
    // GENERACIÓN DE NOTA OBJETO
    // =========================================================

    public function generarNotaObjeto(object $nota, object $encabezado, string $formato = 'docx')
    {
        if ($formato === 'pdf') {
            return $this->generarNotaObjetoPDF($nota, $encabezado);
        }
        return $this->generarNotaObjetoDocx($nota, $encabezado);
    }

    private function generarNotaObjetoDocx(object $n, object $enc)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection(self::MARGEN_SECCION);

        // Encabezado
        $this->agregarEncabezadoInstitucional($section, $enc);

        // Número de expediente
        $section->addText(
            'C.E. Letra ' . $n->expediente_letra . ' Nro ' . $n->expediente_numero,
            self::FUENTE_NORMAL,
            self::PARRAFO_DER
        );
        $section->addText(' ', self::FUENTE_SMALL, self::PARRAFO_IZQ);
        $section->addText(' ', self::FUENTE_SMALL, self::PARRAFO_IZQ);

        // Destinatario
        $section->addText(
            'AL ' . $n->destinatario_grado . ' DE ' . $n->destinatario_arma . ' ' .
            $n->destinatario_apellido . ' ' . $n->destinatario_nombre,
            self::FUENTE_NEGRITA,
            self::PARRAFO_IZQ
        );
        $section->addText(' ', self::FUENTE_SMALL, self::PARRAFO_IZQ);

        // Cuerpo de la nota (Art. 34 Ley 14.777)
        $textoElevacion = $n->texto_elevacion ?: 'Elevo';
        $cuerpo = "\t\t\t{$textoElevacion} a usted el presente expediente relacionado con " .
                  "la afección que padece el {$n->afectado_grado} de {$n->afectado_arma} " .
                  "{$n->afectado_apellido} {$n->afectado_nombre} (DNI: {$n->afectado_dni}) " .
                  "perteneciente a este Instituto; a los efectos que proceda a instruir la " .
                  "Información correspondiente conforme lo determinado en el Art 34 de la " .
                  "Reglamentación para el Ejército de la Ley 14.777 (Ex Ley para el Personal " .
                  "Militar) Tomo IV \"Retiros y Pensiones\".";

        $section->addText($cuerpo, self::FUENTE_NORMAL, self::PARRAFO_IZQ);
        $section->addText(' ', self::FUENTE_SMALL, self::PARRAFO_IZQ);

        $section->addText(
            "\t\t\tAsimismo comunico a Ud que para la tramitación de los presentes actuados " .
            "deberá arbitrar los medios necesarios a fin de coadyuvar con la mayor celeridad posible.",
            self::FUENTE_NORMAL,
            self::PARRAFO_IZQ
        );
        $section->addText(' ', self::FUENTE_SMALL, self::PARRAFO_IZQ);

        // Fecha y lugar
        $fechaDoc = $this->formatearFechaCompleta($n->fecha_documento);
        $section->addText(
            "\t\tEL PALOMAR, {$fechaDoc}",
            self::FUENTE_NORMAL,
            self::PARRAFO_DER
        );

        return $this->descargarDocx($phpWord, 'NotaObjeto_' . $n->expediente_letra . $n->expediente_numero);
    }

    private function generarNotaObjetoPDF(object $n, object $enc)
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('JUSEA CMN v2.0');
        $pdf->SetTitle('Nota Objeto');
        $pdf->SetMargins(20, 15, 15);
        $pdf->AddPage();

        // Encabezado
        $pdf->SetFont('times', 'BI', 16);
        $pdf->Cell(0, 8, 'Ejército Argentino', 0, 1, 'L');
        $pdf->SetFont('times', 'I', 9);
        $pdf->Cell(0, 5, $enc->membrete ?? '', 0, 1, 'L');
        $pdf->SetFont('times', 'I', 14);
        $pdf->Cell(0, 7, $enc->unidad ?? '', 0, 1, 'L');
        $pdf->Ln(3);

        // Expediente
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(0, 6, 'C.E. Letra ' . $n->expediente_letra . ' Nro ' . $n->expediente_numero, 0, 1, 'R');
        $pdf->Ln(5);

        // Destinatario
        $pdf->SetFont('times', 'B', 12);
        $pdf->Cell(0, 6, 'AL ' . $n->destinatario_grado . ' DE ' . $n->destinatario_arma . ' ' . $n->destinatario_apellido . ' ' . $n->destinatario_nombre, 0, 1, 'L');
        $pdf->Ln(5);

        // Cuerpo
        $pdf->SetFont('times', '', 12);
        $textoElevacion = $n->texto_elevacion ?: 'Elevo';
        $cuerpo = "         {$textoElevacion} a usted el presente expediente relacionado con la afección que padece el {$n->afectado_grado} de {$n->afectado_arma} {$n->afectado_apellido} {$n->afectado_nombre} (DNI: {$n->afectado_dni}) perteneciente a este Instituto; a los efectos que proceda a instruir la Información correspondiente conforme lo determinado en el Art 34 de la Reglamentación para el Ejército de la Ley 14.777 (Ex Ley para el Personal Militar) Tomo IV \"Retiros y Pensiones\".";
        $pdf->MultiCell(0, 6, $cuerpo, 0, 'J');
        $pdf->Ln(3);
        $pdf->MultiCell(0, 6, '         Asimismo comunico a Ud que para la tramitación de los presentes actuados deberá arbitrar los medios necesarios a fin de coadyuvar con la mayor celeridad posible.', 0, 'J');
        $pdf->Ln(8);

        // Fecha
        $fechaDoc = $this->formatearFechaCompleta($n->fecha_documento);
        $pdf->Cell(0, 6, 'EL PALOMAR, ' . $fechaDoc, 0, 1, 'R');

        return $this->descargarPDF($pdf, 'NotaObjeto_' . $n->expediente_letra . $n->expediente_numero);
    }

    // =========================================================
    // GENERACIÓN DE EXPEDIENTE BIENES DEL ESTADO
    // =========================================================

    public function generarExpedienteBienes(array $exp, object $encabezado, string $formato = 'docx')
    {
        if ($formato === 'pdf') {
            return $this->generarBienesPDF($exp, $encabezado);
        }
        return $this->generarBienesDocx($exp, $encabezado);
    }

    private function generarBienesDocx(array $e, object $enc)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection(self::MARGEN_SECCION);

        $this->agregarEncabezadoInstitucional($section, $enc);

        $section->addText('ACTUACIÓN POR PÉRDIDA/DAÑO DE BIENES DEL ESTADO', self::FUENTE_SUBTITULO, self::PARRAFO_CENTRO);
        $section->addText(' ', self::FUENTE_SMALL, self::PARRAFO_IZQ);
        $section->addText('Expediente N° ' . ($e['numero_expediente'] ?? ''), self::FUENTE_NEGRITA, self::PARRAFO_IZQ);
        $section->addText(' ', self::FUENTE_SMALL, self::PARRAFO_IZQ);

        // Datos del expediente
        $campos = [
            'Fecha de apertura'  => $this->formatearFecha($e['fecha_apertura'] ?? ''),
            'Tipo de bien'       => $e['tipo_bien'] ?? '',
            'Descripción'        => $e['descripcion_bien'] ?? '',
            'Valor estimado'     => isset($e['valor_estimado']) ? '$ ' . number_format((float)$e['valor_estimado'], 2, ',', '.') : 'No determinado',
            'Dependencia'        => $e['dependencia'] ?? '',
            'Responsable'        => ($e['responsable_grado'] ?? '') . ' ' . ($e['responsable_apellido'] ?? '') . ', ' . ($e['responsable_nombre'] ?? '') . ' (DNI: ' . ($e['responsable_dni'] ?? '') . ')',
            'Instructor'         => ($e['instructor_grado'] ?? '') . ' ' . ($e['instructor_apellido'] ?? '') . ', ' . ($e['instructor_nombre'] ?? ''),
            'Causa de la pérdida' => $e['causa_perdida'] ?? '',
            'Estado'             => $this->formatearEstado($e['estado'] ?? ''),
        ];

        if (!empty($e['resolucion'])) {
            $campos['Resolución'] = $e['resolucion'];
        }

        foreach ($campos as $label => $valor) {
            $textrun = $section->addTextRun(self::PARRAFO_IZQ);
            $textrun->addText($label . ': ', self::FUENTE_NEGRITA);
            $textrun->addText($valor, self::FUENTE_NORMAL);
        }

        // Espacio para firmas
        $section->addText(' ', self::FUENTE_SMALL, self::PARRAFO_IZQ);
        $section->addText(' ', self::FUENTE_SMALL, self::PARRAFO_IZQ);
        $section->addText('____________________________          ____________________________', self::FUENTE_NORMAL, self::PARRAFO_CENTRO);
        $section->addText('           FIRMA                                        ACLARACIÓN', self::FUENTE_SMALL, self::PARRAFO_CENTRO);

        return $this->descargarDocx($phpWord, 'ExpBienes_' . ($e['numero_expediente'] ?? 'doc'));
    }

    private function generarBienesPDF(array $e, object $enc)
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('JUSEA CMN v2.0');
        $pdf->SetTitle('Expediente Bienes del Estado');
        $pdf->SetMargins(20, 15, 15);
        $pdf->AddPage();

        // Encabezado
        $pdf->SetFont('times', 'BI', 16);
        $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Ejército Argentino'), 0, 1, 'L');
        $pdf->SetFont('times', 'I', 9);
        $pdf->Cell(0, 5, $enc->membrete ?? '', 0, 1, 'L');
        $pdf->SetFont('times', 'I', 14);
        $pdf->Cell(0, 7, $enc->unidad ?? '', 0, 1, 'L');
        $pdf->Ln(5);

        $pdf->SetFont('times', 'B', 13);
        $pdf->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ACTUACIÓN POR PÉRDIDA/DAÑO DE BIENES DEL ESTADO'), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->SetFont('times', 'B', 12);
        $pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Expediente N° ' . ($e['numero_expediente'] ?? '')), 0, 1, 'L');
        $pdf->Ln(5);

        // Datos
        $pdf->SetFont('times', '', 11);
        $campos = [
            'Fecha de apertura'   => $this->formatearFecha($e['fecha_apertura'] ?? ''),
            'Tipo de bien'        => $e['tipo_bien'] ?? '',
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Descripción') => $e['descripcion_bien'] ?? '',
            'Valor estimado'      => isset($e['valor_estimado']) ? '$ ' . number_format((float)$e['valor_estimado'], 2, ',', '.') : 'No determinado',
            'Dependencia'         => $e['dependencia'] ?? '',
            'Responsable'         => ($e['responsable_grado'] ?? '') . ' ' . ($e['responsable_apellido'] ?? '') . ', ' . ($e['responsable_nombre'] ?? ''),
            'Instructor'          => ($e['instructor_grado'] ?? '') . ' ' . ($e['instructor_apellido'] ?? '') . ', ' . ($e['instructor_nombre'] ?? ''),
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Causa de la pérdida') => $e['causa_perdida'] ?? '',
            'Estado'              => $this->formatearEstado($e['estado'] ?? ''),
        ];

        foreach ($campos as $label => $valor) {
            $pdf->SetFont('times', 'B', 11);
            $pdf->Cell(45, 6, $label . ':', 0, 0, 'L');
            $pdf->SetFont('times', '', 11);
            $pdf->MultiCell(0, 6, $valor, 0, 'L');
        }

        // Firmas
        $pdf->Ln(20);
        $pdf->Cell(90, 6, '________________________', 0, 0, 'C');
        $pdf->Cell(90, 6, '________________________', 0, 1, 'C');
        $pdf->Cell(90, 6, 'FIRMA', 0, 0, 'C');
        $pdf->Cell(90, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ACLARACIÓN'), 0, 1, 'C');

        return $this->descargarPDF($pdf, 'ExpBienes_' . ($e['numero_expediente'] ?? 'doc'));
    }

    // =========================================================
    // MÉTODOS AUXILIARES (reutilizables)
    // =========================================================

    /**
     * Agrega el encabezado institucional estándar a una sección Word.
     */
    private function agregarEncabezadoInstitucional($section, object $enc, string $letra = '', string $nro = ''): void
    {
        // Línea 1: Ejército Argentino  |  leyenda anual (derecha)
        $fTit  = self::FUENTE_TITULO;
        $fMem  = self::FUENTE_MEMBRETE;
        $fUni  = self::FUENTE_UNIDAD;
        $fId   = ['name' => 'Arial', 'size' => 10, 'bold' => false, 'italic' => false];

        // Párrafo tipo textrun con alineación izquierda para la institución + membrete al final
        $tr1 = $section->addTextRun(['spaceBefore' => 0, 'spaceAfter' => 0, 'align' => 'left']);
        $tr1->addText('   Ejército Argentino           ', $fTit);
        $tr1->addText($enc->membrete ?? '', $fMem);

        // Línea 2: Unidad a la izquierda; si hay Letra/Nro, los añade a la derecha
        if (!empty($letra) || !empty($nro)) {
            // Construir texto de identificación
            $partes = [];
            if (!empty($letra)) $partes[] = 'Letra: ' . $letra;
            if (!empty($nro))   $partes[] = 'Nro.: ' . $nro;
            $idTxt = implode('   ', $partes);

            // Usar TextRun con espaciado para posicionar derecha aproximada
            $tr2 = $section->addTextRun(['spaceBefore' => 0, 'spaceAfter' => 0, 'align' => 'left']);
            $tr2->addText($enc->unidad ?? '', $fUni);
            // Espaciado variable para empujar el texto hacia la derecha
            $tr2->addText('                                        ', $fId);
            $tr2->addText($idTxt, $fId);
        } else {
            $section->addText($enc->unidad ?? '', $fUni, self::PARRAFO_IZQ);
        }

        $section->addText(' ', self::FUENTE_SMALL, self::PARRAFO_IZQ);
    }

    /**
     * Formatear fecha DD/MM/YYYY.
     */
    private function formatearFecha(string $fecha): string
    {
        if (empty($fecha)) return '';
        $dt = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $dt ? $dt->format('d/m/Y') : $fecha;
    }

    /**
     * Formatear fecha completa para documentos: "20 de marzo de 2026"
     */
    private function formatearFechaCompleta(string $fecha): string
    {
        if (empty($fecha)) return '';
        $dt = \DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$dt) return $fecha;

        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];

        return $dt->format('d') . ' de ' . $meses[(int)$dt->format('m')] . ' de ' . $dt->format('Y');
    }

    /**
     * Formatear estado legible.
     */
    private function formatearEstado(string $estado): string
    {
        $estados = [
            'en_tramite' => 'En Trámite',
            'resuelto'   => 'Resuelto',
            'archivado'  => 'Archivado',
            'elevado'    => 'Elevado',
            'activa'     => 'Activa',
            'cumplida'   => 'Cumplida',
            'anulada'    => 'Anulada',
        ];
        return $estados[$estado] ?? ucfirst($estado);
    }

    /**
     * Generar y forzar descarga de archivo .docx.
     * Usa archivos temporales con nombre único (sin conflictos de concurrencia).
     */
    private function descargarDocx(\PhpOffice\PhpWord\PhpWord $phpWord, string $nombreBase)
    {
        $tempFile = tempnam(WRITEPATH . 'documents', 'doc_') . '.docx';
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        $nombreArchivo = $this->sanitizarNombre($nombreBase) . '.docx';

        // Registrar en log de documentos generados
        $this->registrarDocumento($nombreArchivo, 'docx');

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($tempFile));
        ob_clean();
        flush();
        readfile($tempFile);
        @unlink($tempFile);
        exit;
    }

    /**
     * Generar y forzar descarga de archivo PDF.
     */
    private function descargarPDF(\TCPDF $pdf, string $nombreBase)
    {
        $nombreArchivo = $this->sanitizarNombre($nombreBase) . '.pdf';
        $this->registrarDocumento($nombreArchivo, 'pdf');

        $pdf->Output($nombreArchivo, 'D');
        exit;
    }

    /**
     * Sanitizar nombre de archivo.
     */
    private function sanitizarNombre(string $nombre): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nombre);
    }

    /**
     * Registrar documento generado para auditoría.
     */
    private function registrarDocumento(string $nombre, string $formato): void
    {
        try {
            $db = \Config\Database::connect();
            $db->table('documentos_generados')->insert([
                'tipo_documento'  => 'sancion_cuadros', // Se podría parametrizar
                'referencia_id'   => 0,
                'formato'         => $formato,
                'nombre_archivo'  => $nombre,
                'generado_por'    => session()->get('usuario_id'),
            ]);
        } catch (\Exception $e) {
            log_message('warning', 'No se pudo registrar documento generado: ' . $e->getMessage());
        }
    }
}
