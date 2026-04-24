<?php

namespace App\Libraries;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use ZipArchive;

/**
 * Generador de Expedientes de Actuaciones Administrativas — JUSEA CMN v2.0
 *
 * Genera los documentos .docx de cada actuación (Bienes del Estado o
 * Accidente/Enfermedad) empaquetándolos en un archivo .zip para su descarga.
 *
 * Formato institucional replicado de los modelos oficiales del CMN:
 *   - Carátula: "EJÉRCITO ARGENTINO" 36pt bold, "COLEGIO MILITAR" 18pt bold
 *   - Narrativos: encabezado "Ejército Argentino" 16pt italic + leyenda 7pt
 *
 * Requiere: phpoffice/phpword (ya instalado en el proyecto).
 */
class ActuacionDocumentGenerator
{
    // ─── Fuentes — Carátula ────────────────────────────────────────────────────
    private const F_CAR_H1    = ['name' => 'Times New Roman', 'size' => 36, 'bold' => true];
    private const F_CAR_H2    = ['name' => 'Times New Roman', 'size' => 18, 'bold' => true];
    private const F_CAR_LABEL = ['name' => 'Times New Roman', 'size' => 16, 'bold' => true];
    private const F_CAR_VALUE = ['name' => 'Times New Roman', 'size' => 16, 'bold' => false];

    // ─── Fuentes — Encabezado narrativo ───────────────────────────────────────
    private const F_HDR_INST  = ['name' => 'Times New Roman', 'size' => 16, 'italic' => true];
    private const F_HDR_MOTTO = ['name' => 'Times New Roman', 'size' => 7];
    private const F_HDR_UNIT  = ['name' => 'Times New Roman', 'size' => 14, 'italic' => true];

    // ─── Fuentes — Cuerpo ─────────────────────────────────────────────────────
    private const F_NORMAL  = ['name' => 'Times New Roman', 'size' => 12];
    private const F_BOLD    = ['name' => 'Times New Roman', 'size' => 12, 'bold' => true];
    private const F_ITALIC  = ['name' => 'Times New Roman', 'size' => 12, 'italic' => true];
    private const F_ART     = ['name' => 'Times New Roman', 'size' => 11];

    // ─── Párrafos ──────────────────────────────────────────────────────────────
    private const P_CENTER  = ['align' => 'center', 'spaceAfter' => 0, 'spaceBefore' => 0];
    private const P_LEFT    = ['align' => 'left',   'spaceAfter' => 0, 'spaceBefore' => 0];
    private const P_JUSTIFY = ['align' => 'both',   'spaceAfter' => 0, 'spaceBefore' => 0];
    private const P_AFTER   = ['align' => 'both',   'spaceAfter' => 120, 'spaceBefore' => 0];

    // ─── Márgenes (twips) — medidos de los modelos oficiales ──────────────────
    // A4: 11906 × 16838 twips  (210mm × 297mm)
    private const M_CARATULA  = [
        'marginTop'    => 1440,   // 1 inch
        'marginBottom' => 1440,
        'marginLeft'   => 1080,   // 0.75 inch
        'marginRight'  => 1080,
        'pageSizeW'    => 11906,
        'pageSizeH'    => 16838,
    ];
    private const M_NARRATIVO = [
        'marginTop'    => 1417,   // ~2.5 cm
        'marginBottom' => 1417,
        'marginLeft'   => 1701,   // ~3.0 cm  (sangría izquierda mayor)
        'marginRight'  => 1080,
        'pageSizeW'    => 11906,
        'pageSizeH'    => 16838,
    ];

    private string $tmpDir;

    public function __construct()
    {
        $this->tmpDir = sys_get_temp_dir() . '/jusea_' . uniqid();
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }
    }

    // =========================================================================
    // PUNTO DE ENTRADA PÚBLICO
    // =========================================================================

    /**
     * Genera todos los documentos del expediente y devuelve la ruta al ZIP.
     *
     * @param object $actuacion  Objeto devuelto por ActuacionModel::obtenerConDatos()
     * @return string            Ruta absoluta al archivo .zip generado
     */
    public function generarExpediente(object $actuacion): string
    {
        $d    = (array) ($actuacion->campos ?? []);
        $tipo = $actuacion->tipo;

        $archivos = ($tipo === 'bienes')
            ? $this->generarBienes($d)
            : $this->generarAccidente($d);

        return $this->empaquetarZip($archivos, $tipo, $d);
    }

    // =========================================================================
    // GENERADORES MÓDULO: BIENES DEL ESTADO  (v2 — sustitución de plantillas)
    // =========================================================================

    /**
     * Genera los 6 documentos del expediente de bienes del Estado usando las
     * plantillas DOCX oficiales del CMN mediante sustitución de {{PLACEHOLDERS}}.
     * Garantiza fidelidad total al formato institucional (membrete, sello,
     * bordes de página, tipografía Times New Roman) sin re-renderizado.
     */
    private function generarBienes(array $d): array
    {
        $tplDir = APPPATH . 'Templates/bienes/';

        // ── Variables compartidas entre todos los documentos ──────────────
        $base = [
            'CE_LETRA'                       => strtoupper($this->g($d, 'CE_LETRA')),
            'CE_NUMERO'                      => $this->g($d, 'CE_NUMERO'),
            'TIPO_NOVEDAD'                   => $this->g($d, 'TIPO_NOVEDAD'),
            'TIPO_EFECTOS'                   => $this->g($d, 'TIPO_EFECTOS'),
            'NNE_EFECTO'                     => $this->g($d, 'NNE_EFECTO'),
            'DESCRIPCION_EFECTO'             => $this->g($d, 'DESCRIPCION_EFECTO'),
            'DESCRIPCION_CAUSA'              => $this->g($d, 'DESCRIPCION_CAUSA'),
            'CAUSANTE_GRADO'                 => $this->g($d, 'CAUSANTE_GRADO'),
            'CAUSANTE_NOMBRE'                => $this->g($d, 'CAUSANTE_NOMBRE'),
            'CAUSANTE_APELLIDO'              => strtoupper($this->g($d, 'CAUSANTE_APELLIDO')),
            'CAUSANTE_DNI'                   => $this->g($d, 'CAUSANTE_DNI'),
            'CAUSANTE_EDAD'                  => $this->g($d, 'CAUSANTE_EDAD'),
            'CAUSANTE_ESTADO_CIVIL'          => $this->g($d, 'CAUSANTE_ESTADO_CIVIL'),
            'CAUSANTE_DEPENDENCIA'           => $this->g($d, 'CAUSANTE_DEPENDENCIA'),
            'CAUSANTE_JURAMENTO_NOTA'        => $this->g($d, 'CAUSANTE_JURAMENTO_NOTA'),
            'CAUSANTE_RESP_COMO'             => $this->g($d, 'CAUSANTE_RESP_COMO'),
            'CAUSANTE_RESP_CUANDO'           => $this->g($d, 'CAUSANTE_RESP_CUANDO'),
            'TESTIGO_GRADO'                  => $this->g($d, 'TESTIGO_GRADO'),
            'TESTIGO_APELLIDO'               => strtoupper($this->g($d, 'TESTIGO_APELLIDO')),
            'TESTIGO_CARGO'                  => $this->g($d, 'TESTIGO_CARGO'),
            'TESTIGO_DNI'                    => $this->g($d, 'TESTIGO_DNI'),
            'TESTIGO_DOMICILIO'              => $this->g($d, 'TESTIGO_DOMICILIO'),
            'TESTIGO_EDAD'                   => $this->g($d, 'TESTIGO_EDAD'),
            'TESTIGO_ESTADO_CIVIL'           => $this->g($d, 'TESTIGO_ESTADO_CIVIL'),
            'TESTIGO_PROFESION'              => $this->g($d, 'TESTIGO_PROFESION'),
            'TESTIGO_SITUACION_REVISTA'      => $this->g($d, 'TESTIGO_SITUACION_REVISTA'),
            'TESTIGO_RESP_CUANDO'            => $this->g($d, 'TESTIGO_RESP_CUANDO'),
            'TESTIGO_RESP_DONDE'             => $this->g($d, 'TESTIGO_RESP_DONDE'),
            'TESTIGO_JURAMENTO_NOTA'         => $this->g($d, 'TESTIGO_JURAMENTO_NOTA'),
            // Campo compuesto: se auto-genera combinando grado + apellido del testigo
            'TESTIGO_GRADO_APELLIDO'         => trim($this->g($d, 'TESTIGO_GRADO') . ' ' . strtoupper($this->g($d, 'TESTIGO_APELLIDO'))),
            'TESTIGO_CARGO_EN_HECHO'         => $this->g($d, 'TESTIGO_CARGO_EN_HECHO'),
        ];

        // ── DOC 00 — CARÁTULA ─────────────────────────────────────────────
        $vars00 = $base + [
            'FECHA_INICIO' => $this->fechaLiteral($d['DIA_INICIO'] ?? '', $d['MES_INICIO'] ?? '', $d['ANIO_INICIO'] ?? ''),
            'ANIO_INICIO'  => '',   // se incluye en FECHA_INICIO; este placeholder queda vacío
        ];

        // ── DOC 01 — ACTA DE INICIO ───────────────────────────────────────
        $vars01 = $base + [
            'FECHA_LITERAL'             => $this->fechaLiteral($d['DIA_ACTA01'] ?? '', $d['MES_ACTA01'] ?? '', $d['ANIO_ACTA01'] ?? ''),
            'FECHA_ORDEN_LITERAL'       => $this->fechaLiteral($d['DIA_ORDEN'] ?? '', $d['MES_ORDEN'] ?? '', $d['ANIO_ORDEN'] ?? ''),
            'AUTORIDAD_ORDENANTE_CARGO' => $this->g($d, 'AUTORIDAD_ORDENANTE_CARGO', 'Subdirector del Colegio Militar de la Nación'),
            'INFORMANTE_GRADO'          => $this->g($d, 'INFORMANTE_GRADO'),
            'INFORMANTE_APELLIDO'       => strtoupper($this->g($d, 'INFORMANTE_APELLIDO')),
            'INFORMANTE_NOMBRE'         => $this->g($d, 'INFORMANTE_NOMBRE'),
        ];

        // ── DOC 02 — ACTA AGREGANDO DOCUMENTACIÓN ─────────────────────────
        $vars02 = $base + [
            'FECHA_LITERAL'                    => $this->fechaLiteral($d['DIA_ACTA02'] ?? '', $d['MES_ACTA02'] ?? '', $d['ANIO_ACTA02'] ?? ''),
            'CANTIDAD_FOJAS'                   => $this->g($d, 'CANTIDAD_FOJAS'),
            'DESCRIPCION_DOCUMENTOS_AGREGADOS' => $this->g($d, 'DESCRIPCION_DOCUMENTOS_AGREGADOS'),
        ];

        // ── DOC 03 — ACTA DECLARACIÓN TESTIGO ────────────────────────────
        $vars03 = $base + [
            'FECHA_LITERAL'    => $this->fechaLiteral($d['DIA_ACTA03'] ?? '', $d['MES_ACTA03'] ?? '', $d['ANIO_ACTA03'] ?? ''),
            'HORA_DECLARACION' => $this->horaDeclaracion($d['HORA_ACTA03'] ?? '', $d['MIN_ACTA03'] ?? ''),
        ];

        // ── DOC 04 — ACTA DECLARACIÓN CAUSANTE ───────────────────────────
        $vars04 = $base + [
            'FECHA_LITERAL'    => $this->fechaLiteral($d['DIA_ACTA04'] ?? '', $d['MES_ACTA04'] ?? '', $d['ANIO_ACTA04'] ?? ''),
            'HORA_DECLARACION' => $this->horaDeclaracion($d['HORA_ACTA04'] ?? '', $d['MIN_ACTA04'] ?? ''),
        ];

        // ── DOC 06 — INFORME FINAL ────────────────────────────────────────
        $vars06 = $base + [
            'FECHA_INFORME_LITERAL'                  => $this->fechaLiteral($d['DIA_INFORME'] ?? '', $d['MES_INFORME'] ?? '', $d['ANIO_INFORME'] ?? ''),
            'AUTORIDAD_DESTINATARIA'                 => $this->g($d, 'AUTORIDAD_DESTINATARIA', 'Director del Colegio Militar de la Nación'),
            'SOLICITANTE_CARGO'                      => $this->g($d, 'SOLICITANTE_CARGO'),
            'NORDEN_DOCUMENTAL'                      => $this->g($d, 'NORDEN_DOCUMENTAL'),
            'ID_GDE_DOCUMENTAL'                      => $this->g($d, 'ID_GDE_DOCUMENTAL'),
            'NORDEN_DICTAMEN'                        => $this->g($d, 'NORDEN_DICTAMEN'),
            'NRO_DICTAMEN'                           => $this->g($d, 'NRO_DICTAMEN'),
            'NORDEN_ACTO_ADM'                        => $this->g($d, 'NORDEN_ACTO_ADM'),
            'RESUMEN_ACTO_ADM'                       => $this->g($d, 'RESUMEN_ACTO_ADM'),
            'NORDEN_DECLARACION'                     => $this->g($d, 'NORDEN_DECLARACION'),
            'RESUMEN_DECLARACION_TESTIGO'            => $this->g($d, 'RESUMEN_DECLARACION_TESTIGO'),
            'NORDEN_DOCUMENTACION'                   => $this->g($d, 'NORDEN_DOCUMENTACION'),
            'ID_GDE_DOCUMENTACION'                   => $this->g($d, 'ID_GDE_DOCUMENTACION'),
            'LISTADO_DOCUMENTACION'                  => $this->g($d, 'LISTADO_DOCUMENTACION'),
            'NORDEN_JUSTIPRECIO'                     => $this->g($d, 'NORDEN_JUSTIPRECIO'),
            'ID_GDE_JUSTIPRECIO'                     => $this->g($d, 'ID_GDE_JUSTIPRECIO'),
            'VALOR_REPOSICION_NUMERICO'              => $this->g($d, 'VALOR_REPOSICION_NUMERICO'),
            'VALOR_REPOSICION_LITERAL'               => $this->g($d, 'VALOR_REPOSICION_LITERAL'),
            'SINTESIS_HECHOS_PROBADOS'               => $this->g($d, 'SINTESIS_HECHOS_PROBADOS'),
            'ANALISIS_RESPONSABILIDAD_DISCIPLINARIA' => $this->g($d, 'ANALISIS_RESPONSABILIDAD_DISCIPLINARIA'),
            'ANALISIS_RESPONSABILIDAD_PATRIMONIAL'   => $this->g($d, 'ANALISIS_RESPONSABILIDAD_PATRIMONIAL'),
            'DISPOSITIVO_CONCLUSION'                 => $this->g($d, 'DISPOSITIVO_CONCLUSION'),
            'TEXTO_ADICIONAL_CONCLUSION'             => $this->g($d, 'TEXTO_ADICIONAL_CONCLUSION'),
        ];

        return [
            '01_CARATULA.docx'      => $this->procesarTemplate($tplDir . 'BIENES_00_CARATULA_TPL.docx',    $vars00, '01_CARATULA'),
            '02_ACTA_INICIO.docx'   => $this->procesarTemplate($tplDir . 'BIENES_01_ACTA_INICIO_TPL.docx', $vars01, '02_ACTA_INICIO'),
            '03_ACTA_AGREGANDO.docx'=> $this->procesarTemplate($tplDir . 'BIENES_02_ACTA_AGREGANDO_TPL.docx', $vars02, '03_ACTA_AGREGANDO'),
            '04_ACTA_TESTIGO.docx'  => $this->procesarTemplate($tplDir . 'BIENES_03_ACTA_TESTIGO_TPL.docx',   $vars03, '04_ACTA_TESTIGO'),
            '05_ACTA_CAUSANTE.docx' => $this->procesarTemplate($tplDir . 'BIENES_04_ACTA_CAUSANTE_TPL.docx',  $vars04, '05_ACTA_CAUSANTE'),
            '06_INFORME_FINAL.docx' => $this->procesarTemplate($tplDir . 'BIENES_06_INFORME_FINAL_TPL.docx',  $vars06, '06_INFORME_FINAL'),
        ];
    }

    /**
     * Procesa una plantilla DOCX substituyendo {{PLACEHOLDER}} por los valores dados.
     *
     * Método: lee TODAS las entradas del template (binarias y XML), aplica
     * str_replace en los XML, y reempaqueta todo en un ZIP nuevo desde cero.
     * Esto garantiza:
     *   - Sin entradas duplicadas (problema de ZipArchive en modo append/modify)
     *   - Preservación exacta de header/footer con sello VML, bordes, estilos
     *   - Fidelidad total al formato institucional CMN
     *
     * @param  string $tplPath    Ruta absoluta a la plantilla .docx
     * @param  array  $vars       Mapa [PLACEHOLDER => valor]
     * @param  string $outNombre  Nombre base del archivo de salida (sin extensión)
     * @return string             Ruta al archivo .docx generado en tmpDir
     */
    private function procesarTemplate(string $tplPath, array $vars, string $outNombre): string
    {
        $outPath = $this->tmpDir . '/' . $outNombre . '.docx';

        // ── Fase 1: leer TODAS las entradas del template original ─────────
        $zipR = new ZipArchive();
        if ($zipR->open($tplPath) !== true) {
            throw new \RuntimeException("No se pudo abrir plantilla: {$tplPath}");
        }
        $entradas = [];  // [nombre => contenido_string]
        for ($i = 0; $i < $zipR->numFiles; $i++) {
            $name = $zipR->getNameIndex($i);
            $entradas[$name] = $zipR->getFromIndex($i);
        }
        $zipR->close();

        // ── Fase 2: aplicar sustituciones en archivos XML/rels ────────────
        foreach ($entradas as $name => &$content) {
            if (!str_ends_with($name, '.xml') && !str_ends_with($name, '.rels')) {
                continue;   // imágenes, fuentes binarias → sin modificar
            }
            foreach ($vars as $key => $val) {
                $content = str_replace(
                    '{{' . $key . '}}',
                    htmlspecialchars((string)$val, ENT_XML1 | ENT_QUOTES, 'UTF-8'),
                    $content
                );
            }
        }
        unset($content);

        // ── Fase 3: reempaquetar como ZIP nuevo (sin duplicados) ──────────
        $zipW = new ZipArchive();
        if ($zipW->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("No se pudo crear archivo de salida: {$outPath}");
        }
        foreach ($entradas as $name => $content) {
            $zipW->addFromString($name, $content);
        }
        $zipW->close();

        return $outPath;
    }

    // ── Bienes: DILIGENCIA INICIANDO ACTUACIÓN ADMINISTRATIVA ─────────────────
    // LEGACY — método mantenido pero ya no invocado (sustituido por procesarTemplate)
    private function docBienesInicio(array $d): string
    {
        $pw = $this->nuevaPhpWord();
        $s  = $pw->addSection(self::M_NARRATIVO);
        $this->hdrNarrativo($s);

        $s->addTextBreak(1);
        $tr = $s->addTextRun(self::P_CENTER);
        $tr->addText('CE: Letra ', self::F_NORMAL);
        $tr->addText($this->g($d, 'letraExpH1'), self::F_BOLD);
        $tr->addText(' Nro. ', self::F_NORMAL);
        $tr->addText($this->g($d, 'NroExpH1'), self::F_BOLD);

        $s->addText('DILIGENCIA INICIANDO ACTUACIÓN ADMINISTRATIVA', self::F_BOLD, self::P_CENTER);
        $s->addTextBreak(1);

        $fecha  = $this->fechaEscrita($d['DiaH2'] ?? '', $d['MesH2'] ?? '', $d['AnioH2'] ?? '');
        $hora   = $this->g($d, 'HoraH2', '00') . ':' . $this->g($d, 'MinH2', '00');
        $orden  = $this->fechaOrden($d['DiaOrdenH2'] ?? '', $d['MesOrdenH2'] ?? '', $d['AnioOrdenH2'] ?? '');
        $cargo  = $this->g($d, 'CargoH2', 'Subdirector del Colegio Militar de la Nación');
        $gRespFull = trim($this->g($d, 'GradRespH2') . ' ' . $this->g($d, 'ApeRespH2') . ' ' . $this->g($d, 'NomRespH2'));
        $causa  = $this->g($d, 'var1d1', 'la pérdida del bien de dotación');

        $par = $s->addTextRun(self::P_JUSTIFY);
        $par->addText("En El Palomar, asiento del Colegio Militar de la Nación, siendo las {$hora} horas, {$fecha}, "
            . "en cumplimiento a lo ordenado por el {$cargo}, mediante Orden de fecha {$orden}, "
            . "procedo a iniciar la presente Actuación Administrativa instruida con motivo de ", self::F_NORMAL);
        $par->addText($causa, self::F_BOLD);
        $par->addText(", responsabilizado el ", self::F_NORMAL);
        $par->addText($gRespFull, self::F_BOLD);
        $par->addText(", conforme lo determinado en el Art. 12 del Decreto Reglamentario Nro. 2666/12 según lo "
            . "establecido en la Resolución JEMGE Nro. 2346/15 referente al trámite que deberá adoptarse "
            . "ante la pérdida, inutilización o deterioro de Bienes del Estado.", self::F_NORMAL);

        $s->addTextBreak(3);
        $this->pieFirma($s, $d, 'GradOfH2', 'ApeOfH2', 'NomOfH2', 'ArmaOfH2');

        return $this->guardarDocx($pw, '02_DILIGENCIA_INICIO');
    }

    // ── Bienes: DILIGENCIA AGREGANDO DOCUMENTACIÓN ─────────────────────────────
    private function docBienesAgregaDoc(array $d): string
    {
        $pw = $this->nuevaPhpWord();
        $s  = $pw->addSection(self::M_NARRATIVO);
        $this->hdrNarrativo($s);

        $s->addTextBreak(1);
        $tr = $s->addTextRun(self::P_CENTER);
        $tr->addText('CE: Letra ', self::F_NORMAL);
        $tr->addText($this->g($d, 'letraExpH1'), self::F_BOLD);
        $tr->addText(' Nro. ', self::F_NORMAL);
        $tr->addText($this->g($d, 'NroExpH1'), self::F_BOLD);

        $s->addText('DILIGENCIA AGREGANDO DOCUMENTACIÓN', self::F_BOLD, self::P_CENTER);
        $s->addTextBreak(1);

        $fecha  = $this->fechaEscrita($d['DiaH3'] ?? '', $d['MesH3'] ?? '', $d['AnioH3'] ?? '');
        $formSRE = $this->g($d, 'FormSRE137', 'SRE 137');
        $pon    = $this->g($d, 'PON', '');

        $par = $s->addTextRun(self::P_JUSTIFY);
        $par->addText("En El Palomar, asiento del Colegio Militar de la Nación, siendo las ", self::F_NORMAL);
        $par->addText($fecha, self::F_BOLD);
        $par->addText(", procedo a agregar la siguiente documentación al presente expediente: ", self::F_NORMAL);
        $par->addText($formSRE, self::F_BOLD);

        if (!empty($pon)) {
            $par->addText(" y el parte de novedades (PON) Nro. ", self::F_NORMAL);
            $par->addText($pon, self::F_BOLD);
        }
        $par->addText(".", self::F_NORMAL);

        $s->addTextBreak(3);
        $this->pieFirma($s, $d, 'GradOfH2', 'ApeOfH2', 'NomOfH2', 'ArmaOfH2');

        return $this->guardarDocx($pw, '03_DILIGENCIA_AGREGA_DOC');
    }

    // ── Bienes: ACTA DE TESTIGO (1 o 2) ────────────────────────────────────────
    private function docBienesTestigo(array $d, int $n): string
    {
        $pw = $this->nuevaPhpWord();
        $s  = $pw->addSection(self::M_NARRATIVO);
        $this->hdrNarrativo($s);

        $s->addTextBreak(1);
        $tr = $s->addTextRun(self::P_CENTER);
        $tr->addText('CE: Letra ', self::F_NORMAL);
        $tr->addText($this->g($d, 'letraExpH1'), self::F_BOLD);
        $tr->addText(' Nro. ', self::F_NORMAL);
        $tr->addText($this->g($d, 'NroExpH1'), self::F_BOLD);

        $s->addText("ACTA DE DECLARACIÓN TESTIMONIAL N° {$n}", self::F_BOLD, self::P_CENTER);
        $s->addTextBreak(1);

        $pfx = "var{$n}";    // var4 o var5
        $grado  = $this->g($d, "{$pfx}a1");
        $arma   = $this->g($d, "{$pfx}a2");
        $nombre = $this->g($d, "{$pfx}a3");
        $apelli = $this->g($d, "{$pfx}a4");
        $cargo  = $this->g($d, "{$pfx}a5");

        // Fecha/hora del acta
        $fechaAct = $this->fechaEscrita($d['DiaH2'] ?? '', $d['MesH2'] ?? '', $d['AnioH2'] ?? '');
        $horaAct  = $this->g($d, 'HoraH2', '00') . ':' . $this->g($d, 'MinH2', '00');

        $par = $s->addTextRun(self::P_JUSTIFY);
        $par->addText("En El Palomar, asiento del Colegio Militar de la Nación, siendo las {$horaAct} horas, {$fechaAct}, "
            . "quien suscribe recibe declaración testimonial de ", self::F_NORMAL);
        $par->addText("{$grado} {$arma} {$apelli} {$nombre}", self::F_BOLD);
        $par->addText(", {$cargo}, quien luego de ser impuesto del objeto de la presente actuación, manifiesta:", self::F_NORMAL);

        $s->addTextBreak(1);

        // Preguntas y respuestas
        for ($i = 1; $i <= 11; $i++) {
            $pregKey = "{$pfx}b{$i}";
            $respKey = "{$pfx}c" . ($i <= 8 ? $i : '');
            $preg = $this->g($d, $pregKey);
            $resp = ($i <= 8) ? $this->g($d, "{$pfx}c{$i}") : '';
            if (!empty($preg)) {
                $this->preguntaRespuesta($s, $preg, $resp);
            }
        }

        $s->addTextBreak(2);
        $this->pieFirma($s, $d, 'GradOfH2', 'ApeOfH2', 'NomOfH2', 'ArmaOfH2');

        return $this->guardarDocx($pw, "0" . (3 + $n) . "_ACTA_TESTIGO_{$n}");
    }

    // ── Bienes: INFORME FINAL ──────────────────────────────────────────────────
    private function docBienesInformeFinal(array $d): string
    {
        $pw = $this->nuevaPhpWord();
        $s  = $pw->addSection(self::M_NARRATIVO);
        $this->hdrNarrativo($s);

        $s->addTextBreak(1);
        $tr = $s->addTextRun(self::P_CENTER);
        $tr->addText('CE: Letra ', self::F_NORMAL);
        $tr->addText($this->g($d, 'letraExpH1'), self::F_BOLD);
        $tr->addText(' Nro. ', self::F_NORMAL);
        $tr->addText($this->g($d, 'NroExpH1'), self::F_BOLD);

        $s->addText('INFORME FINAL', self::F_BOLD, self::P_CENTER);
        $s->addTextBreak(1);

        $gCaus  = $this->g($d, 'GradCausanteH1');
        $apeCaus = $this->g($d, 'ApeCausanteH1');
        $nomCaus = $this->g($d, 'NomCausanteH1');
        $causa  = $this->g($d, 'var1d1', 'la pérdida del bien');

        $par = $s->addTextRun(self::P_JUSTIFY);
        $par->addText("En mérito de los antecedentes obrantes en el expediente, corresponde informar que el ", self::F_NORMAL);
        $par->addText("{$gCaus} {$apeCaus} {$nomCaus}", self::F_BOLD);
        $par->addText(", presuntamente responsable de ", self::F_NORMAL);
        $par->addText($causa, self::F_BOLD);
        $par->addText(", ha dado cumplimiento a los recaudos reglamentarios exigidos para este tipo de actuaciones. "
            . "Por lo expuesto, se eleva el presente expediente a la superioridad para su consideración y resolución "
            . "conforme a las normas vigentes.", self::F_NORMAL);

        $s->addTextBreak(1);
        $this->articulosCP($s, $d, 'var6c');

        $s->addTextBreak(3);
        $this->pieFirma($s, $d, 'GradOfH2', 'ApeOfH2', 'NomOfH2', 'ArmaOfH2');

        return $this->guardarDocx($pw, '06_INFORME_FINAL');
    }

    // =========================================================================
    // GENERADORES MÓDULO: ACCIDENTE / ENFERMEDAD
    // =========================================================================

    private function generarAccidente(array $d): array
    {
        return [
            '01_CARATULA.docx'                  => $this->docCaratula($d, 'accidente'),
            '02_ACTA_INICIO.docx'               => $this->docAccidenteInicio($d),
            '03_DILIGENCIA_AGREGA_DOC.docx'     => $this->docAccidenteAgregaDoc($d),
            '04_DECLARACION_CAUSANTE.docx'      => $this->docAccidenteDeclaracionCausante($d),
            '05_ACTA_TESTIGO_1.docx'            => $this->docAccidenteTestigo($d, 1),
            '06_ACTA_TESTIGO_2.docx'            => $this->docAccidenteTestigo($d, 2),
            '07_INFORME_FINAL.docx'             => $this->docAccidenteInformeFinal($d),
        ];
    }

    // ── Accidente: ACTA DE INICIO ──────────────────────────────────────────────
    private function docAccidenteInicio(array $d): string
    {
        $pw = $this->nuevaPhpWord();
        $s  = $pw->addSection(self::M_NARRATIVO);
        $this->hdrNarrativo($s);

        $s->addTextBreak(1);
        $tr = $s->addTextRun(self::P_CENTER);
        $tr->addText('CE: Letra ', self::F_NORMAL);
        $tr->addText($this->g($d, 'letraExpH1'), self::F_BOLD);
        $tr->addText(' Nro. ', self::F_NORMAL);
        $tr->addText($this->g($d, 'NroExpH1'), self::F_BOLD);

        $s->addText('ACTA DE INICIO DE ACTUACIÓN ADMINISTRATIVA', self::F_BOLD, self::P_CENTER);
        $s->addTextBreak(1);

        $fecha  = $this->fechaEscrita($d['var2a1'] ?? '', $d['var2a2'] ?? '', $d['var2a3'] ?? '');
        $hora   = $this->g($d, 'var2a4', '00') . ':' . $this->g($d, 'var2a5', '00');
        $gCaus  = $this->g($d, 'GradCausanteH1');
        $apeCaus = $this->g($d, 'ApeCausanteH1');
        $nomCaus = $this->g($d, 'NomCausanteH1');
        $hecho  = $this->g($d, 'var1d1', 'el accidente sufrido');

        $par = $s->addTextRun(self::P_JUSTIFY);
        $par->addText("En El Palomar, asiento del Colegio Militar de la Nación, siendo las {$hora} horas, {$fecha}, "
            . "procedo a iniciar la presente Actuación Administrativa con motivo de ", self::F_NORMAL);
        $par->addText($hecho, self::F_BOLD);
        $par->addText(" por parte del ", self::F_NORMAL);
        $par->addText("{$gCaus} {$apeCaus} {$nomCaus}", self::F_BOLD);
        $par->addText(", conforme a las disposiciones vigentes en materia de accidentes y enfermedades del "
            . "personal militar.", self::F_NORMAL);

        // Datos complementarios s2b
        for ($i = 1; $i <= 4; $i++) {
            $val = $this->g($d, "var2b{$i}");
            if (!empty($val)) {
                $s->addTextBreak(1);
                $s->addText($val, self::F_NORMAL, self::P_JUSTIFY);
            }
        }

        $s->addTextBreak(3);
        $this->pieFirma($s, $d, 'var2a1', 'var2a2', 'var2a3', '');

        return $this->guardarDocx($pw, '02_ACTA_INICIO');
    }

    // ── Accidente: DILIGENCIA AGREGANDO DOCUMENTACIÓN ─────────────────────────
    private function docAccidenteAgregaDoc(array $d): string
    {
        $pw = $this->nuevaPhpWord();
        $s  = $pw->addSection(self::M_NARRATIVO);
        $this->hdrNarrativo($s);

        $s->addTextBreak(1);
        $tr = $s->addTextRun(self::P_CENTER);
        $tr->addText('CE: Letra ', self::F_NORMAL);
        $tr->addText($this->g($d, 'letraExpH1'), self::F_BOLD);
        $tr->addText(' Nro. ', self::F_NORMAL);
        $tr->addText($this->g($d, 'NroExpH1'), self::F_BOLD);

        $s->addText('DILIGENCIA AGREGANDO DOCUMENTACIÓN', self::F_BOLD, self::P_CENTER);
        $s->addTextBreak(1);

        $fecha = $this->fechaEscrita($d['var3a1'] ?? '', $d['var3a2'] ?? '', $d['var3a3'] ?? '');

        $par = $s->addTextRun(self::P_JUSTIFY);
        $par->addText("En El Palomar, asiento del Colegio Militar de la Nación, {$fecha}, "
            . "procedo a agregar la siguiente documentación al presente expediente:", self::F_NORMAL);

        for ($i = 1; $i <= 10; $i++) {
            $doc = $this->g($d, "var3b{$i}");
            if (!empty($doc)) {
                $trItem = $s->addTextRun(self::P_LEFT);
                $trItem->addText("- {$doc}", self::F_NORMAL);
            }
        }

        $s->addTextBreak(3);
        $this->pieFirma($s, $d, 'var3a4', 'var3a5', '', '');

        return $this->guardarDocx($pw, '03_DILIGENCIA_AGREGA_DOC');
    }

    // ── Accidente: DECLARACIÓN DEL CAUSANTE ───────────────────────────────────
    private function docAccidenteDeclaracionCausante(array $d): string
    {
        $pw = $this->nuevaPhpWord();
        $s  = $pw->addSection(self::M_NARRATIVO);
        $this->hdrNarrativo($s);

        $s->addTextBreak(1);
        $tr = $s->addTextRun(self::P_CENTER);
        $tr->addText('CE: Letra ', self::F_NORMAL);
        $tr->addText($this->g($d, 'letraExpH1'), self::F_BOLD);
        $tr->addText(' Nro. ', self::F_NORMAL);
        $tr->addText($this->g($d, 'NroExpH1'), self::F_BOLD);

        $s->addText('DECLARACIÓN DEL CAUSANTE', self::F_BOLD, self::P_CENTER);
        $s->addTextBreak(1);

        $gCaus  = $this->g($d, 'GradCausanteH1');
        $armCaus = $this->g($d, 'ArmaCausanteH1');
        $apeCaus = $this->g($d, 'ApeCausanteH1');
        $nomCaus = $this->g($d, 'NomCausanteH1');
        $edad   = $this->g($d, 'var1b5');
        $ecivil = $this->g($d, 'var1b6');
        $dni    = $this->g($d, 'var1b7');
        $fecha  = $this->fechaEscrita($d['var4a1'] ?? '', $d['var4a2'] ?? '', $d['var4a3'] ?? '');
        $hora   = $this->g($d, 'var4a4', '00') . ':' . $this->g($d, 'var4a5', '00');

        $par = $s->addTextRun(self::P_JUSTIFY);
        $par->addText("En El Palomar, asiento del Colegio Militar de la Nación, siendo las {$hora} horas, {$fecha}, "
            . "procedo a tomar declaración a ", self::F_NORMAL);
        $par->addText("{$gCaus} {$armCaus} {$apeCaus} {$nomCaus}", self::F_BOLD);
        $par->addText(", de {$edad} años de edad, estado civil {$ecivil}, DNI Nro. {$dni}, "
            . "quien luego de ser debidamente impuesto del objeto de la actuación, manifiesta:", self::F_NORMAL);

        $s->addTextBreak(1);

        for ($i = 1; $i <= 6; $i++) {
            $preg = $this->g($d, "var4b{$i}");
            $resp = $this->g($d, "var4c{$i}");
            if (!empty($preg)) {
                $this->preguntaRespuesta($s, $preg, $resp);
            }
        }

        $s->addTextBreak(2);
        $this->pieFirma($s, $d, 'GradCausanteH1', 'ApeCausanteH1', 'NomCausanteH1', 'ArmaCausanteH1');

        return $this->guardarDocx($pw, '04_DECLARACION_CAUSANTE');
    }

    // ── Accidente: ACTA DE TESTIGO (1 o 2) ────────────────────────────────────
    private function docAccidenteTestigo(array $d, int $n): string
    {
        $pw = $this->nuevaPhpWord();
        $s  = $pw->addSection(self::M_NARRATIVO);
        $this->hdrNarrativo($s);

        $s->addTextBreak(1);
        $tr = $s->addTextRun(self::P_CENTER);
        $tr->addText('CE: Letra ', self::F_NORMAL);
        $tr->addText($this->g($d, 'letraExpH1'), self::F_BOLD);
        $tr->addText(' Nro. ', self::F_NORMAL);
        $tr->addText($this->g($d, 'NroExpH1'), self::F_BOLD);

        $s->addText("ACTA DE DECLARACIÓN TESTIMONIAL N° {$n}", self::F_BOLD, self::P_CENTER);
        $s->addTextBreak(1);

        $base = ($n === 1) ? 4 : 5;   // var4 o var5
        $pfx  = "var{$base}";

        $grado  = $this->g($d, "{$pfx}a1");
        $arma   = $this->g($d, "{$pfx}a2");
        $apelli = $this->g($d, "{$pfx}a4");
        $nombre = $this->g($d, "{$pfx}a3");
        $cargo  = $this->g($d, "{$pfx}a5");
        $fecha  = $this->fechaEscrita($d["{$pfx}a1"] ?? '', $d["{$pfx}a2"] ?? '', $d["{$pfx}a3"] ?? '');
        $hora   = $this->g($d, "{$pfx}a4", '00') . ':' . $this->g($d, "{$pfx}a5", '00');

        // Para accidente los prefijos de testigo son distintos
        $grado  = $this->g($d, "var" . (4 + $n - 1) . "a1");
        $apelli = $this->g($d, "var" . (4 + $n - 1) . "a2");
        $nombre = $this->g($d, "var" . (4 + $n - 1) . "a3");
        $cargo  = $this->g($d, "var" . (4 + $n - 1) . "a4");
        $fechaT = $this->fechaEscrita($d['var2a1'] ?? '', $d['var2a2'] ?? '', $d['var2a3'] ?? '');
        $horaT  = $this->g($d, 'var2a4', '00') . ':' . $this->g($d, 'var2a5', '00');

        $par = $s->addTextRun(self::P_JUSTIFY);
        $par->addText("En El Palomar, asiento del Colegio Militar de la Nación, siendo las {$horaT} horas, {$fechaT}, "
            . "quien suscribe recibe declaración testimonial de ", self::F_NORMAL);
        $par->addText("{$grado} {$apelli} {$nombre}", self::F_BOLD);
        $par->addText(", {$cargo}, quien luego de ser impuesto del objeto de la presente actuación, manifiesta:", self::F_NORMAL);

        $s->addTextBreak(1);

        $pref = "var" . (4 + $n - 1);
        for ($i = 1; $i <= 6; $i++) {
            $preg = $this->g($d, "{$pref}b{$i}");
            $resp = $this->g($d, "{$pref}c{$i}");
            if (!empty($preg)) {
                $this->preguntaRespuesta($s, $preg, $resp);
            }
        }

        // var4/5 c3a si existe
        $c3a = $this->g($d, "{$pref}c3a");
        if (!empty($c3a)) {
            $s->addText($c3a, self::F_NORMAL, self::P_JUSTIFY);
        }

        $s->addTextBreak(2);
        $this->pieFirma($s, $d, 'GradCausanteH1', 'ApeCausanteH1', 'NomCausanteH1', 'ArmaCausanteH1');

        return $this->guardarDocx($pw, "0" . (4 + $n) . "_ACTA_TESTIGO_{$n}");
    }

    // ── Accidente: INFORME FINAL ───────────────────────────────────────────────
    private function docAccidenteInformeFinal(array $d): string
    {
        $pw = $this->nuevaPhpWord();
        $s  = $pw->addSection(self::M_NARRATIVO);
        $this->hdrNarrativo($s);

        $s->addTextBreak(1);
        $tr = $s->addTextRun(self::P_CENTER);
        $tr->addText('CE: Letra ', self::F_NORMAL);
        $tr->addText($this->g($d, 'letraExpH1'), self::F_BOLD);
        $tr->addText(' Nro. ', self::F_NORMAL);
        $tr->addText($this->g($d, 'NroExpH1'), self::F_BOLD);

        $s->addText('INFORME FINAL', self::F_BOLD, self::P_CENTER);
        $s->addTextBreak(1);

        $gCaus  = $this->g($d, 'GradCausanteH1');
        $apeCaus = $this->g($d, 'ApeCausanteH1');
        $nomCaus = $this->g($d, 'NomCausanteH1');
        $hecho  = $this->g($d, 'var1d1', 'el accidente sufrido');

        $par = $s->addTextRun(self::P_JUSTIFY);
        $par->addText("En mérito de los antecedentes obrantes en el expediente, corresponde informar que el ", self::F_NORMAL);
        $par->addText("{$gCaus} {$apeCaus} {$nomCaus}", self::F_BOLD);
        $par->addText(", causante de ", self::F_NORMAL);
        $par->addText($hecho, self::F_BOLD);
        $par->addText(", ha dado cumplimiento a los recaudos reglamentarios exigidos para este tipo de actuaciones. "
            . "La presente actuación se encuentra en condiciones de ser elevada a la superioridad para su "
            . "consideración y resolución.", self::F_NORMAL);

        // Datos adicionales var6
        for ($i = 1; $i <= 5; $i++) {
            $val = $this->g($d, "var6a{$i}");
            if (!empty($val)) {
                $s->addTextBreak(1);
                $s->addText($val, self::F_NORMAL, self::P_JUSTIFY);
            }
        }

        $s->addTextBreak(1);
        $this->articulosCP($s, $d, 'var6b');

        $s->addTextBreak(3);
        $this->pieFirma($s, $d, 'GradCausanteH1', 'ApeCausanteH1', 'NomCausanteH1', 'ArmaCausanteH1');

        return $this->guardarDocx($pw, '07_INFORME_FINAL');
    }

    // =========================================================================
    // CARÁTULA COMPARTIDA (bienes y accidente)
    // =========================================================================

    private function docCaratula(array $d, string $tipo): string
    {
        $pw = $this->nuevaPhpWord();
        $s  = $pw->addSection(self::M_CARATULA);

        // ── MEMBRETE INSTITUCIONAL ────────────────────────────────────────────
        $s->addText('EJÉRCITO ARGENTINO', self::F_CAR_H1, self::P_CENTER);
        $s->addText('COLEGIO MILITAR DE LA NACIÓN', self::F_CAR_H2, self::P_CENTER);
        $s->addTextBreak(2);

        // ── DATOS DEL EXPEDIENTE ─────────────────────────────────────────────
        $letra = strtoupper($this->g($d, 'letraExpH1', ''));
        $nro   = $this->g($d, 'NroExpH1', '');
        $gCaus = $this->g($d, 'GradCausanteH1', '');
        $aCaus = $this->g($d, 'ApeCausanteH1', '');
        $nCaus = $this->g($d, 'NomCausanteH1', '');
        $armaCaus = $this->g($d, 'ArmaCausanteH1', '');
        $edad  = $this->g($d, 'EdadH1', '') ?: $this->g($d, 'var1b5', '');
        $ecivil = $this->g($d, 'EstadoCivilH1', '') ?: $this->g($d, 'var1b6', '');
        $dni   = $this->g($d, 'DniH1', '') ?: $this->g($d, 'var1b7', '');

        $tituloAct = ($tipo === 'bienes')
            ? 'PÉRDIDA/RUPTURA/INUTILIZACIÓN DE BIENES DEL ESTADO'
            : 'ACCIDENTE / ENFERMEDAD';

        $causa = $this->g($d, 'var1d1', '');
        $motivo = $this->g($d, 'var1d2', '') ?: $causa;

        $this->lineaCE($s, 'EXPEDIENTE', "Letra {$letra} Nro. {$nro}");
        $s->addTextBreak(1);
        $this->lineaCE($s, 'ACTUACIÓN', $tituloAct);
        $s->addTextBreak(1);
        $this->lineaCE($s, 'CAUSANTE', "{$gCaus} {$armaCaus} {$aCaus} {$nCaus}");
        $s->addTextBreak(1);
        $this->lineaCE($s, 'EDAD', $edad . '  —  E. Civil: ' . $ecivil . '  —  DNI: ' . $dni);
        $s->addTextBreak(1);

        if (!empty($motivo)) {
            $this->lineaCE($s, 'MOTIVO', $motivo);
            $s->addTextBreak(1);
        }

        // Datos adicionales var1c (descripción del bien / lesión)
        for ($i = 1; $i <= 3; $i++) {
            $val = $this->g($d, "var1c{$i}");
            if (!empty($val)) {
                $trV = $s->addTextRun(self::P_LEFT);
                $trV->addText($val, self::F_CAR_VALUE);
            }
        }

        $s->addTextBreak(3);

        // Año
        $anio = $this->g($d, 'AnioH2', '') ?: date('Y');
        $s->addText($anio, self::F_CAR_VALUE, self::P_CENTER);

        return $this->guardarDocx($pw, '01_CARATULA');
    }

    // =========================================================================
    // HELPERS — ELEMENTOS DE DOCUMENTO
    // =========================================================================

    /**
     * Encabezado institucional para documentos narrativos.
     * Línea 1: "   Ejército Argentino      [año]"  (16pt italic + 7pt)
     * Línea 2: "   Colegio Militar de la Nación"   (14pt italic)
     */
    private function hdrNarrativo(Section $s): void
    {
        $anio = date('Y');
        $tr1  = $s->addTextRun(self::P_LEFT);
        $tr1->addText('   Ejército Argentino           ', self::F_HDR_INST);
        $tr1->addText('"' . $anio . ' - Año del Bicentenario del Congreso de Tucumán"', self::F_HDR_MOTTO);
        $s->addText('   Colegio Militar de la Nación', self::F_HDR_UNIT, self::P_LEFT);
        $s->addTextBreak(1);
    }

    /**
     * Línea "LABEL: valor" para la carátula.
     */
    private function lineaCE(Section $s, string $label, string $valor): void
    {
        $tr = $s->addTextRun(self::P_LEFT);
        $tr->addText($label . ': ', self::F_CAR_LABEL);
        $tr->addText($valor, self::F_CAR_VALUE);
    }

    /**
     * Pie de firma estándar.
     * Los cuatro parámetros son claves en el array $d.
     */
    private function pieFirma(Section $s, array $d,
        string $kGrado, string $kApellido, string $kNombre, string $kArma): void
    {
        $grado  = $this->g($d, $kGrado);
        $apelli = $this->g($d, $kApellido);
        $nombre = $this->g($d, $kNombre);
        $arma   = $this->g($d, $kArma);

        $firmaLine = trim("{$grado} {$arma} {$apelli} {$nombre}");

        $s->addText('_______________________________', self::F_NORMAL, self::P_CENTER);
        $s->addText($firmaLine ?: 'Oficial Instructor', self::F_BOLD, self::P_CENTER);
        $s->addText('Oficial Instructor', self::F_NORMAL, self::P_CENTER);
    }

    /**
     * Bloque pregunta / respuesta.
     */
    private function preguntaRespuesta(Section $s, string $preg, string $resp = ''): void
    {
        $trP = $s->addTextRun(self::P_JUSTIFY);
        $trP->addText('P: ', self::F_BOLD);
        $trP->addText($preg, self::F_NORMAL);

        $trR = $s->addTextRun(self::P_JUSTIFY);
        $trR->addText('R: ', self::F_BOLD);
        $trR->addText(!empty($resp) ? $resp : '————', self::F_NORMAL);

        $s->addTextBreak(1);
    }

    /**
     * Artículos del Código Penal (var6c1 … var6c6 o var6b1 … var6b4).
     */
    private function articulosCP(Section $s, array $d, string $prefijo): void
    {
        $maxItems = str_contains($prefijo, 'var6c') ? 6 : 4;
        for ($i = 1; $i <= $maxItems; $i++) {
            $art = $this->g($d, "{$prefijo}{$i}");
            if (!empty($art)) {
                $s->addText($art, self::F_ART, self::P_JUSTIFY);
                $s->addTextBreak(1);
            }
        }
    }

    // =========================================================================
    // HELPERS — FECHAS Y TEXTO
    // =========================================================================

    /**
     * Genera una fecha literal en español para los placeholders de plantilla.
     * Ejemplo: fechaLiteral('5','3','2026') → '5 de Marzo de 2026'
     */
    private function fechaLiteral(string $dia, string $mes, string $anio): string
    {
        if (empty($dia) || empty($mes) || empty($anio)) {
            return '____ de ________ de ____';
        }
        $mesNom = is_numeric($mes) ? ucfirst($this->mesNombre((int)$mes)) : $mes;
        return "{$dia} de {$mesNom} de {$anio}";
    }

    /**
     * Genera la hora en formato HH:MM para el placeholder HORA_DECLARACION.
     */
    private function horaDeclaracion(string $hora, string $min): string
    {
        $h = str_pad($hora ?: '00', 2, '0', STR_PAD_LEFT);
        $m = str_pad($min  ?: '00', 2, '0', STR_PAD_LEFT);
        return "{$h}:{$m}";
    }

    private function fechaEscrita(string $dia, string $mes, string $anio): string
    {
        if (empty($dia) && empty($mes) && empty($anio)) {
            return 'fecha a determinar';
        }
        $diaNum  = is_numeric($dia) ? $this->numToWords((int)$dia) : $dia;
        $mesNom  = is_numeric($mes) ? $this->mesNombre((int)$mes) : $mes;
        return "a los {$diaNum} ({$dia}) días del mes de {$mesNom} del año {$anio}";
    }

    private function fechaOrden(string $dia, string $mes, string $anio): string
    {
        if (empty($dia) && empty($mes) && empty($anio)) {
            return '——/——/——';
        }
        $m = is_numeric($mes) ? $this->mesAbrev((int)$mes) : $mes;
        return "{$dia} {$m} {$anio}";
    }

    private function numToWords(int $n): string
    {
        $words = [
            0 => 'cero', 1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro',
            5 => 'cinco', 6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve',
            10 => 'diez', 11 => 'once', 12 => 'doce', 13 => 'trece',
            14 => 'catorce', 15 => 'quince', 16 => 'dieciséis', 17 => 'diecisiete',
            18 => 'dieciocho', 19 => 'diecinueve', 20 => 'veinte', 21 => 'veintiuno',
            22 => 'veintidós', 23 => 'veintitrés', 24 => 'veinticuatro',
            25 => 'veinticinco', 26 => 'veintiséis', 27 => 'veintisiete',
            28 => 'veintiocho', 29 => 'veintinueve', 30 => 'treinta', 31 => 'treinta y uno',
        ];
        return $words[$n] ?? (string)$n;
    }

    private function mesNombre(int $m): string
    {
        $meses = ['enero','febrero','marzo','abril','mayo','junio',
                  'julio','agosto','septiembre','octubre','noviembre','diciembre'];
        return $meses[max(1, min(12, $m)) - 1];
    }

    private function mesAbrev(int $m): string
    {
        $meses = ['ENE','FEB','MAR','ABR','MAY','JUN',
                  'JUL','AGO','SEP','OCT','NOV','DIC'];
        return $meses[max(1, min(12, $m)) - 1];
    }

    // =========================================================================
    // HELPERS — PHPWORD
    // =========================================================================

    private function nuevaPhpWord(): PhpWord
    {
        $pw = new PhpWord();
        $pw->setDefaultFontName('Times New Roman');
        $pw->setDefaultFontSize(12);
        return $pw;
    }

    private function g(array $d, string $key, string $default = ''): string
    {
        return (string)($d[$key] ?? $default);
    }

    private function guardarDocx(PhpWord $pw, string $nombre): string
    {
        $path = $this->tmpDir . '/' . $nombre . '.docx';
        $writer = IOFactory::createWriter($pw, 'Word2007');
        $writer->save($path);
        return $path;
    }

    private function empaquetarZip(array $archivos, string $tipo, array $d): string
    {
        // Soporta nombres de campo v2 (bienes: CAUSANTE_APELLIDO/CE_NUMERO) y legacy (accidente: ApeCausanteH1/NroExpH1)
        $apellido = strtolower(preg_replace('/\s+/', '_',
            $d['CAUSANTE_APELLIDO'] ?? $d['ApeCausanteH1'] ?? 'expediente'));
        $nro      = preg_replace('/[^A-Za-z0-9]/', '',
            $d['CE_NUMERO'] ?? $d['NroExpH1'] ?? 'exp');
        $zipNombre = "EXPEDIENTE_{$tipo}_{$apellido}_{$nro}_" . date('Ymd_His') . '.zip';
        $zipPath   = $this->tmpDir . '/' . $zipNombre;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("No se pudo crear el archivo ZIP: {$zipPath}");
        }

        foreach ($archivos as $nombre => $ruta) {
            if (file_exists($ruta)) {
                $zip->addFile($ruta, $nombre);
            }
        }
        $zip->close();

        return $zipPath;
    }
}
