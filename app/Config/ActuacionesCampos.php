<?php

namespace App\Config;

/**
 * JUSEA CMN v2 — Definición de campos para módulos de Actuaciones Administrativas
 *
 * Contiene:
 *  - Constantes compartidas (grados, armas, fechas, etc.)
 *  - Definición de campos: Bienes del Estado
 *  - Definición de campos: Accidente / Enfermedad
 */
class ActuacionesCampos
{
    // =========================================================
    // CONSTANTES COMPARTIDAS
    // =========================================================

    public static function grados_completos(): array
    {
        return [
            'CR'           => 'Coronel',
            'TC'           => 'Teniente Coronel',
            'MY'           => 'Mayor',
            'CT'           => 'Capitan',
            'TP'           => 'Teniente 1ro',
            'TT'           => 'Teniente',
            'ST'           => 'Subteniente',
            'SM'           => 'Suboficial Mayor',
            'SP'           => 'Suboficial Principal',
            'SA'           => 'Sargento Ayudante',
            'SI'           => 'Sargento 1ro',
            'SG'           => 'Sargento',
            'CI'           => 'Cabo 1ro',
            'CB Art. 11'   => 'Cabo Art. 11',
            'CB'           => 'Cabo',
            'VP'           => 'Soldado Voluntario 1ra',
            'VS'           => 'Soldado Voluntario 2da',
            'VS EC'        => 'Soldado Voluntario 2da "EC"',
            'SM Cad'       => 'Suboficial Mayor Cadete',
            'SP Cad'       => 'Suboficial Principal Cadete',
            'SA cad'       => 'Sargento Ayudante Cadete',
            'SI cad'       => 'Sargento 1ro Cadete',
            'SG Cad'       => 'Sargento Cadete',
            'CI Cad'       => 'Cabo 1ro Cadete',
            'CB cad'       => 'Cabo Cadete',
            'Cad Prof'     => 'Cad Profesional',
            'Cad Vto Ano'  => 'Cadete Vto Año',
            'Cad IVto Ano' => 'Cadete IVto Año',
            'Cad IIIe Ano' => 'Cadete IIIer Año',
            'Cad IIdo Ano' => 'Cadete IIdo Año',
            'Cad Ier Ano'  => 'Cadete Ier Año',
        ];
    }

    public static function grados_reducidos(): array
    {
        return [
            'Cap'            => 'Capitan',
            'Tte 1ro'        => 'Teniente 1ro',
            'Subt'           => 'Subteniente',
            'Subof My'       => 'Suboficial Mayor',
            'Subof Pr'       => 'Suboficial Principal',
            'Sarg Ay'        => 'Sargento Ayudante',
            'Sarg 1ro'       => 'Sargento 1ro',
            'Sarg'           => 'Sargento',
            'Cbo 1ro'        => 'Cabo 1ro',
            'Cbo Art. 11'    => 'Cabo Art. 11',
            'Cbo'            => 'Cabo',
            'Sol Vol 1ra'    => 'Soldado Voluntario 1ra',
            'Sol Vol 2da'    => 'Soldado Voluntario 2da',
            'Sol Vol 2da EC' => 'Soldado Voluntario 2da "EC"',
            'Subof My Cad'   => 'Suboficial Mayor Cadete',
            'Subof Pr Cad'   => 'Suboficial Principal Cadete',
            'Sarg Ay cad'    => 'Sargento Ayudante Cadete',
            'Sarg 1ro cad'   => 'Sargento 1ro Cadete',
            'Sarg Cad'       => 'Sargento Cadete',
            'Cbo 1ro Cad'    => 'Cabo 1ro Cadete',
            'Cbo cad'        => 'Cabo Cadete',
            'Cad Prof'       => 'Cad Profesional',
            'Cad Vto Ano'    => 'Cadete Vto Año',
            'Cad IVto Ano'   => 'Cadete IVto Año',
            'Cad IIIe Ano'   => 'Cadete IIIer Año',
            'Cad IIdo Ano'   => 'Cadete IIdo Año',
            'Cad Ier Ano'    => 'Cadete Ier Año',
        ];
    }

    public static function armas_servicios(): array
    {
        return [
            'I'              => 'Infanteria',
            'C'              => 'Caballeria',
            'Ing'            => 'Ingenieros',
            'Com'            => 'Comunicaciones',
            'Ars'            => 'Arsenales',
            'Int'            => 'Intendencia',
            'A'              => 'Artilleria',
            'SCD'            => 'SCD',
            'Aud'            => 'Auditor',
            'Pil Ej'         => 'Piloto Ejercito',
            'Educ Fis'       => 'Educacion Fisica',
            'Mus'            => 'Musico',
            'Dir Bda'        => 'Director de Banda',
            'Med'            => 'Medico',
            'Vet'            => 'Veterinario',
            'Cond Mot'       => 'Conductor Motorista',
            'Tal'            => 'Talabartero',
            'Baq'            => 'Baqueano',
            'Zap'            => 'Zapatero',
            'Sas'            => 'Sastre',
            'Ofic'           => 'Oficinista',
            'Coc'            => 'Cocinero',
            'Enf'            => 'Enfermero',
            'Enf Vet'        => 'Enfermero Veterinario',
            'Enf Prof'       => 'Enfermero Profesional',
            'Mec Inf'        => 'Mecanico Informatico',
            'Mec Mot Rda'    => 'Mecanico Mot Rda',
            'Mec Mot Elec'   => 'Mecanico Mot Elec',
            'Mec Mot Oruga'  => 'Mecanico Mot Oruga',
            'Mec Arm'        => 'Mecanico Arm',
            'Mec Mun Expl'   => 'Mecanico Mun Expl',
            'Mec A'          => 'Mecanico A',
            'Mec Inst'       => 'Mecanico Inst',
            'Mec Ing'        => 'Mecanico Ing',
            'Mec Av'         => 'Mecanico Av',
            'Mec Op Apar Pr' => 'Mecanico Op Apar Pr',
            'Mec Eq Camp'    => 'Mecanico Eq Camp',
            'Mec Eq Fij'     => 'Mecanico Eq Fij',
            'Mec de Radar'   => 'Mecanico de Radar',
            'Cam'            => 'Camarero',
            'Carp'           => 'Carpintero',
            'Herr'           => 'Herrero',
            'Aux Enf'        => 'Auxiliar Enfermeria',
        ];
    }

    public static function meses(): array
    {
        return ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    }

    public static function estado_civil(): array
    {
        return ['Soltero','Casado','Divorciado','Viudo'];
    }

    public static function si_no(): array
    {
        return ['NO','SI'];
    }

    public static function lugares_atencion_medica(): array
    {
        return [
            ''                               => '-- Seleccione --',
            'Seccion Sanidad'                => 'Seccion Sanidad',
            'Hospital Militar Central'       => 'Hospital Militar Central',
            'Hospital Militar Campo de Mayo' => 'Hospital Militar Campo de Mayo',
        ];
    }

    public static function dias(): array   { return range(1, 31); }
    public static function horas(): array  { return array_map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT), range(0, 23)); }
    public static function minutos(): array { return ['00','05','10','15','20','25','30','35','40','45','50','55']; }
    public static function anios(): array  { $y = (int)date('Y'); return range($y - 8, $y + 2); }

    /** Resuelve el nombre de un grupo de opciones al array correspondiente. */
    public static function resolverOpciones(string $grupo): array
    {
        return match($grupo) {
            'GRADOS_COMPLETOS'       => self::grados_completos(),
            'GRADOS_REDUCIDOS'       => self::grados_reducidos(),
            'ARMAS_SERVICIOS'        => self::armas_servicios(),
            'MESES'                  => self::meses(),
            'ESTADO_CIVIL'           => self::estado_civil(),
            'SI_NO'                  => self::si_no(),
            'LUGARES_ATENCION_MEDICA'=> self::lugares_atencion_medica(),
            'DIAS'                   => self::dias(),
            'HORAS'                  => self::horas(),
            'MINUTOS'                => self::minutos(),
            'ANIOS'                  => self::anios(),
            default                  => [],
        };
    }

    // =========================================================
    // SECCIONES: BIENES DEL ESTADO
    // =========================================================

    public static function secciones_bienes(): array
    {
        return [

            // ─────────────────────────────────────────────────────────────
            // S1 — Identificación del Expediente
            // Cubre: BIENES_00 (carátula) — CE_LETRA, CE_NUMERO, fechas inicio
            // ─────────────────────────────────────────────────────────────
            's1' => [
                'titulo'     => 'IDENTIFICACION DEL EXPEDIENTE',
                'icono'      => 'bi-file-earmark-text',
                'subtitulos' => ['Expediente','Fecha de Inicio de Actuaciones'],
                'campos'     => [
                    'CE_LETRA'    => ['label'=>'Expediente — Letra',  'type'=>'text',   'placeholder'=>'Ej: A'],
                    'CE_NUMERO'   => ['label'=>'Expediente — Número', 'type'=>'text',   'placeholder'=>'Ej: 001'],
                    'DIA_INICIO'  => ['label'=>'Día de Inicio',       'type'=>'select', 'options'=>'DIAS'],
                    'MES_INICIO'  => ['label'=>'Mes de Inicio',       'type'=>'select', 'options'=>'MESES'],
                    'ANIO_INICIO' => ['label'=>'Año de Inicio',       'type'=>'select', 'options'=>'ANIOS'],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // S2 — Datos del Causante
            // Cubre: BIENES_00, BIENES_04
            // ─────────────────────────────────────────────────────────────
            's2' => [
                'titulo'     => 'DATOS DEL CAUSANTE',
                'icono'      => 'bi-person-badge',
                'subtitulos' => ['Identificacion','Datos Personales'],
                'campos'     => [
                    'CAUSANTE_GRADO'        => ['label'=>'Grado',                      'type'=>'select',   'options'=>'GRADOS_COMPLETOS'],
                    'CAUSANTE_APELLIDO'     => ['label'=>'Apellido',                   'type'=>'text',     'placeholder'=>'Apellido del causante'],
                    'CAUSANTE_NOMBRE'       => ['label'=>'Nombre',                     'type'=>'text',     'placeholder'=>'Nombre del causante'],
                    'CAUSANTE_DNI'          => ['label'=>'DNI',                        'type'=>'text',     'placeholder'=>'Nro. de DNI'],
                    'CAUSANTE_EDAD'         => ['label'=>'Edad',                       'type'=>'number',   'placeholder'=>'Edad en años'],
                    'CAUSANTE_ESTADO_CIVIL' => ['label'=>'Estado Civil',               'type'=>'select',   'options'=>'ESTADO_CIVIL'],
                    'CAUSANTE_DEPENDENCIA'  => ['label'=>'Dependencia donde presta servicios', 'type'=>'text', 'placeholder'=>'Ej: Cía A / Batallón de Cadetes'],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // S3 — El Hecho
            // Cubre: BIENES_00, BIENES_01, BIENES_03, BIENES_04, BIENES_06
            // ─────────────────────────────────────────────────────────────
            's3' => [
                'titulo'     => 'EL HECHO',
                'icono'      => 'bi-exclamation-triangle',
                'subtitulos' => ['Identificacion del Efecto','Descripcion'],
                'campos'     => [
                    'TIPO_NOVEDAD'      => ['label'=>'Tipo de novedad (pérdida / deterioro / inutilización)', 'type'=>'text',     'placeholder'=>'Ej: pérdida'],
                    'NNE_EFECTO'        => ['label'=>'NNE del efecto',           'type'=>'text',     'placeholder'=>'Nomenclador Nacional de Efectos'],
                    'DESCRIPCION_EFECTO'=> ['label'=>'Descripción del efecto',   'type'=>'textarea', 'placeholder'=>'Ej: fusil de asalto FMK3 cal. 9mm Nro de serie XXXXX'],
                    'TIPO_EFECTOS'      => ['label'=>'Tipo de efectos',          'type'=>'text',     'placeholder'=>'Ej: armamento / equipo individual / ropa'],
                    'DESCRIPCION_CAUSA' => ['label'=>'Descripción de la causa',  'type'=>'textarea', 'placeholder'=>'Relato breve de las causas que motivaron la actuacion'],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // S4 — Acta de Inicio
            // Cubre: BIENES_01 — FECHA_LITERAL, FECHA_ORDEN_LITERAL,
            //        AUTORIDAD_ORDENANTE_CARGO, INFORMANTE_*
            // ─────────────────────────────────────────────────────────────
            's4' => [
                'titulo'     => 'ACTA DE INICIO',
                'icono'      => 'bi-pencil-square',
                'subtitulos' => ['Fecha del Acta','Fecha de la Orden','Datos del Oficial Informante'],
                'campos'     => [
                    'DIA_ACTA01'               => ['label'=>'Día del Acta de Inicio',              'type'=>'select', 'options'=>'DIAS'],
                    'MES_ACTA01'               => ['label'=>'Mes del Acta de Inicio',              'type'=>'select', 'options'=>'MESES'],
                    'ANIO_ACTA01'              => ['label'=>'Año del Acta de Inicio',              'type'=>'select', 'options'=>'ANIOS'],
                    'DIA_ORDEN'                => ['label'=>'Día de la Orden del Subdirector',     'type'=>'select', 'options'=>'DIAS'],
                    'MES_ORDEN'                => ['label'=>'Mes de la Orden',                     'type'=>'select', 'options'=>'MESES'],
                    'ANIO_ORDEN'               => ['label'=>'Año de la Orden',                     'type'=>'select', 'options'=>'ANIOS'],
                    'AUTORIDAD_ORDENANTE_CARGO'=> ['label'=>'Cargo de la autoridad ordenante',     'type'=>'text',   'placeholder'=>'Ej: Subdirector del Colegio Militar de la Nación'],
                    'INFORMANTE_GRADO'         => ['label'=>'Grado (Oficial Informante)',          'type'=>'select', 'options'=>'GRADOS_COMPLETOS'],
                    'INFORMANTE_APELLIDO'      => ['label'=>'Apellido (Oficial Informante)',       'type'=>'text',   'placeholder'=>'Apellido'],
                    'INFORMANTE_NOMBRE'        => ['label'=>'Nombre (Oficial Informante)',         'type'=>'text',   'placeholder'=>'Nombre'],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // S5 — Acta Agregando Documentación
            // Cubre: BIENES_02 — FECHA_LITERAL, CANTIDAD_FOJAS,
            //        DESCRIPCION_DOCUMENTOS_AGREGADOS
            // ─────────────────────────────────────────────────────────────
            's5' => [
                'titulo'     => 'ACTA AGREGANDO DOCUMENTACION',
                'icono'      => 'bi-paperclip',
                'subtitulos' => ['Fecha del Acta','Documentacion'],
                'campos'     => [
                    'DIA_ACTA02'                      => ['label'=>'Día',                           'type'=>'select',   'options'=>'DIAS'],
                    'MES_ACTA02'                      => ['label'=>'Mes',                           'type'=>'select',   'options'=>'MESES'],
                    'ANIO_ACTA02'                     => ['label'=>'Año',                           'type'=>'select',   'options'=>'ANIOS'],
                    'CANTIDAD_FOJAS'                  => ['label'=>'Cantidad de fojas',             'type'=>'number',   'placeholder'=>'Número de fojas que se agregan'],
                    'DESCRIPCION_DOCUMENTOS_AGREGADOS'=> ['label'=>'Descripción de los documentos agregados', 'type'=>'textarea', 'placeholder'=>'Enumeración de la documentación incorporada al expediente'],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // S6 — Declaración del Testigo
            // Cubre: BIENES_03 — FECHA_LITERAL, HORA_DECLARACION, TESTIGO_*
            // ─────────────────────────────────────────────────────────────
            's6' => [
                'titulo'     => 'DECLARACION DEL TESTIGO',
                'icono'      => 'bi-person-lines-fill',
                'subtitulos' => ['Fecha y Hora','Datos Personales del Testigo','Declaracion'],
                'campos'     => [
                    'DIA_ACTA03'                => ['label'=>'Día de la Declaración del Testigo', 'type'=>'select', 'options'=>'DIAS'],
                    'MES_ACTA03'                => ['label'=>'Mes',                               'type'=>'select', 'options'=>'MESES'],
                    'ANIO_ACTA03'               => ['label'=>'Año',                               'type'=>'select', 'options'=>'ANIOS'],
                    'HORA_ACTA03'               => ['label'=>'Hora',                              'type'=>'select', 'options'=>'HORAS'],
                    'MIN_ACTA03'                => ['label'=>'Minutos',                           'type'=>'select', 'options'=>'MINUTOS'],
                    'TESTIGO_GRADO'             => ['label'=>'Grado',                             'type'=>'select', 'options'=>'GRADOS_COMPLETOS'],
                    'TESTIGO_APELLIDO'          => ['label'=>'Apellido',                          'type'=>'text',   'placeholder'=>'Apellido del testigo'],
                    'TESTIGO_CARGO'             => ['label'=>'Cargo / Función actual',            'type'=>'text',   'placeholder'=>'Ej: Jefe de Sección / Comandante de Pelotón'],
                    'TESTIGO_DNI'               => ['label'=>'DNI',                               'type'=>'text',   'placeholder'=>'Nro. de DNI'],
                    'TESTIGO_EDAD'              => ['label'=>'Edad',                              'type'=>'number', 'placeholder'=>'Edad en años'],
                    'TESTIGO_ESTADO_CIVIL'      => ['label'=>'Estado Civil',                      'type'=>'select', 'options'=>'ESTADO_CIVIL'],
                    'TESTIGO_PROFESION'         => ['label'=>'Profesión',                         'type'=>'text',   'placeholder'=>'Ej: Militar / Empleado Civil'],
                    'TESTIGO_SITUACION_REVISTA' => ['label'=>'Situación de revista',              'type'=>'text',   'placeholder'=>'Ej: en actividad en esta Unidad'],
                    'TESTIGO_DOMICILIO'         => ['label'=>'Domicilio real',                    'type'=>'text',   'placeholder'=>'Calle, número, ciudad'],
                    'TESTIGO_JURAMENTO_NOTA'    => ['label'=>'Juramento — nota (ej: AFIRMATIVO / PROMESA)', 'type'=>'textarea', 'placeholder'=>'Texto que completa "...juró contestar... Dijo:"'],
                    'TESTIGO_RESP_CUANDO'       => ['label'=>'DIJO — ¿Cuándo se produjo el hecho?',       'type'=>'textarea', 'placeholder'=>'Respuesta del testigo'],
                    'TESTIGO_RESP_DONDE'        => ['label'=>'DIJO — ¿Dónde se produjo el hecho?',        'type'=>'textarea', 'placeholder'=>'Respuesta del testigo'],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // S7 — Declaración del Causante
            // Cubre: BIENES_04 — FECHA_LITERAL, HORA_DECLARACION,
            //        CAUSANTE_JURAMENTO_NOTA, CAUSANTE_RESP_COMO/CUANDO
            // ─────────────────────────────────────────────────────────────
            's7' => [
                'titulo'     => 'DECLARACION DEL CAUSANTE',
                'icono'      => 'bi-person-exclamation',
                'subtitulos' => ['Fecha y Hora','Declaracion'],
                'campos'     => [
                    'DIA_ACTA04'              => ['label'=>'Día de la Declaración del Causante', 'type'=>'select',   'options'=>'DIAS'],
                    'MES_ACTA04'              => ['label'=>'Mes',                                'type'=>'select',   'options'=>'MESES'],
                    'ANIO_ACTA04'             => ['label'=>'Año',                                'type'=>'select',   'options'=>'ANIOS'],
                    'HORA_ACTA04'             => ['label'=>'Hora',                               'type'=>'select',   'options'=>'HORAS'],
                    'MIN_ACTA04'              => ['label'=>'Minutos',                            'type'=>'select',   'options'=>'MINUTOS'],
                    'CAUSANTE_JURAMENTO_NOTA' => ['label'=>'Juramento — nota (ej: AFIRMATIVO / PROMESA)', 'type'=>'textarea', 'placeholder'=>'Texto que completa "...juró contestar... Dijo:"'],
                    'CAUSANTE_RESP_COMO'      => ['label'=>'DIJO — ¿Cómo se produjo el hecho?',  'type'=>'textarea', 'placeholder'=>'Respuesta del causante'],
                    'CAUSANTE_RESP_CUANDO'    => ['label'=>'DIJO — ¿Cuándo se produjo el hecho?','type'=>'textarea', 'placeholder'=>'Respuesta del causante'],
                ],
            ],

            // ─────────────────────────────────────────────────────────────
            // S8 — Informe Final
            // Cubre: BIENES_06 — todos los campos del informe conclusivo
            // ─────────────────────────────────────────────────────────────
            's8' => [
                'titulo'     => 'INFORME FINAL',
                'icono'      => 'bi-file-earmark-check',
                'subtitulos' => ['Fecha del Informe','Antecedentes del Expediente','Analisis y Conclusiones'],
                'campos'     => [
                    'DIA_INFORME'                            => ['label'=>'Día del Informe Final',                  'type'=>'select',   'options'=>'DIAS'],
                    'MES_INFORME'                            => ['label'=>'Mes',                                    'type'=>'select',   'options'=>'MESES'],
                    'ANIO_INFORME'                           => ['label'=>'Año',                                    'type'=>'select',   'options'=>'ANIOS'],
                    'AUTORIDAD_DESTINATARIA'                 => ['label'=>'Autoridad destinataria',                 'type'=>'text',     'placeholder'=>'Ej: Director del Colegio Militar de la Nación'],
                    'SOLICITANTE_CARGO'                      => ['label'=>'Cargo del solicitante de la actuación',  'type'=>'text',     'placeholder'=>'Ej: Subdirector del CMN'],
                    'TESTIGO_CARGO_EN_HECHO'                 => ['label'=>'Cargo del testigo al momento del hecho', 'type'=>'text',     'placeholder'=>'Ej: Comandante de Pelotón'],
                    'NORDEN_DOCUMENTAL'                      => ['label'=>'N° de Orden — Solicitud (Documental)',   'type'=>'text',     'placeholder'=>'Ej: 01'],
                    'ID_GDE_DOCUMENTAL'                      => ['label'=>'ID GDE — Solicitud documental',          'type'=>'text',     'placeholder'=>'Ej: EX-2026-01234567-APN-CMN'],
                    'NORDEN_DICTAMEN'                        => ['label'=>'N° de Orden — Dictamen Jurídico',        'type'=>'text',     'placeholder'=>'Ej: 02'],
                    'NRO_DICTAMEN'                           => ['label'=>'Número del Dictamen Jurídico',           'type'=>'text',     'placeholder'=>'Ej: 0001/2026'],
                    'NORDEN_ACTO_ADM'                        => ['label'=>'N° de Orden — Acto Administrativo',     'type'=>'text',     'placeholder'=>'Ej: 03'],
                    'RESUMEN_ACTO_ADM'                       => ['label'=>'Artículo 1° del Acto Administrativo',   'type'=>'textarea', 'placeholder'=>'Texto del Artículo 1° de la resolución que ordenó la instrucción'],
                    'NORDEN_DECLARACION'                     => ['label'=>'N° de Orden — Declaración Testimonial', 'type'=>'text',     'placeholder'=>'Ej: 04'],
                    'RESUMEN_DECLARACION_TESTIGO'            => ['label'=>'Resumen de la declaración testimonial',  'type'=>'textarea', 'placeholder'=>'Síntesis de lo declarado por el testigo'],
                    'NORDEN_DOCUMENTACION'                   => ['label'=>'N° de Orden — Documentación Adicional', 'type'=>'text',     'placeholder'=>'Ej: 05'],
                    'ID_GDE_DOCUMENTACION'                   => ['label'=>'ID GDE — Documentación adicional',      'type'=>'text',     'placeholder'=>'Ej: EX-2026-01234568-APN-CMN'],
                    'LISTADO_DOCUMENTACION'                  => ['label'=>'Listado de documentación incorporada',  'type'=>'textarea', 'placeholder'=>'Ej: el informe técnico de Arsenales / el parte de novedades'],
                    'NORDEN_JUSTIPRECIO'                     => ['label'=>'N° de Orden — Justiprecio',             'type'=>'text',     'placeholder'=>'Ej: 06'],
                    'ID_GDE_JUSTIPRECIO'                     => ['label'=>'ID GDE — Justiprecio',                  'type'=>'text',     'placeholder'=>'Ej: EX-2026-01234569-APN-CMN'],
                    'VALOR_REPOSICION_NUMERICO'              => ['label'=>'Valor de reposición (en números)',       'type'=>'number',   'placeholder'=>'Ej: 250000'],
                    'VALOR_REPOSICION_LITERAL'               => ['label'=>'Valor de reposición (en letras)',        'type'=>'text',     'placeholder'=>'Ej: DOSCIENTOS CINCUENTA MIL PESOS'],
                    'SINTESIS_HECHOS_PROBADOS'               => ['label'=>'Síntesis de los hechos probados',        'type'=>'textarea', 'placeholder'=>'Relato fáctico acreditado en el expediente'],
                    'ANALISIS_RESPONSABILIDAD_DISCIPLINARIA' => ['label'=>'Análisis de responsabilidad disciplinaria', 'type'=>'textarea', 'placeholder'=>'Análisis sobre si corresponde responsabilidad disciplinaria'],
                    'ANALISIS_RESPONSABILIDAD_PATRIMONIAL'   => ['label'=>'Análisis de responsabilidad patrimonial',   'type'=>'textarea', 'placeholder'=>'Análisis sobre si corresponde responsabilidad patrimonial'],
                    'DISPOSITIVO_CONCLUSION'                 => ['label'=>'Dispositivo de la conclusión',          'type'=>'textarea', 'placeholder'=>'Ej: se exima de responsabilidad patrimonial al causante / se impute...'],
                    'TEXTO_ADICIONAL_CONCLUSION'             => ['label'=>'Texto adicional de la conclusión (opcional)', 'type'=>'textarea', 'placeholder'=>'Texto adicional que cierra el párrafo de conclusiones, si lo hubiere'],
                ],
            ],
        ];
    }

    // =========================================================
    // SECCIONES: ACCIDENTE / ENFERMEDAD
    // =========================================================

    public static function secciones_accidente(): array
    {
        return [
            's1' => [
                'titulo'     => 'CARATULA',
                'icono'      => 'bi-file-earmark',
                'subtitulos' => ['Informacion del Acta','Informacion del Causante','Dato de Fecha','Causas y Observaciones'],
                'campos'     => [
                    'letraExpH1'     => ['label'=>'Expte (letra)',              'type'=>'text',     'placeholder'=>'Ej: A'],
                    'NroExpH1'       => ['label'=>'Nro Expediente',             'type'=>'number',   'placeholder'=>'Número de expediente'],
                    'GradCausanteH1' => ['label'=>'Grado',                      'type'=>'select',   'options'=>'GRADOS_REDUCIDOS'],
                    'ArmaCausanteH1' => ['label'=>'Arma/Servicio/Especialidad', 'type'=>'select',   'options'=>'ARMAS_SERVICIOS'],
                    'NomCausanteH1'  => ['label'=>'Nombre del Causante',        'type'=>'text',     'placeholder'=>'Nombre'],
                    'ApeCausanteH1'  => ['label'=>'Apellido del Causante',      'type'=>'text',     'placeholder'=>'Apellido'],
                    'var1b5'         => ['label'=>'Edad',                       'type'=>'number',   'placeholder'=>'Edad'],
                    'var1b6'         => ['label'=>'Estado Civil',               'type'=>'select',   'options'=>'ESTADO_CIVIL'],
                    'var1b7'         => ['label'=>'DNI',                        'type'=>'number',   'placeholder'=>'DNI del Causante'],
                    'var1c1'         => ['label'=>'Dia',                        'type'=>'select',   'options'=>'DIAS'],
                    'var1c2'         => ['label'=>'Mes',                        'type'=>'select',   'options'=>'MESES'],
                    'var1c3'         => ['label'=>'Anio',                       'type'=>'select',   'options'=>'ANIOS'],
                    'var1d1'         => ['label'=>'Causa',                      'type'=>'text',     'placeholder'=>'Causa de la actuacion'],
                    'var1d2'         => ['label'=>'Observaciones',              'type'=>'textarea', 'placeholder'=>'Observaciones adicionales'],
                ],
            ],
            's2' => [
                'titulo'     => 'DILIGENCIA — INICIANDO TRAMITE',
                'icono'      => 'bi-pencil-square',
                'subtitulos' => ['Datos de Fecha','Informacion del Oficial Informante'],
                'campos'     => [
                    'var2a1' => ['label'=>'Dia',    'type'=>'select','options'=>'DIAS'],
                    'var2a2' => ['label'=>'Mes',    'type'=>'select','options'=>'MESES'],
                    'var2a3' => ['label'=>'Anio',   'type'=>'select','options'=>'ANIOS'],
                    'var2a4' => ['label'=>'Hora',   'type'=>'select','options'=>'HORAS'],
                    'var2a5' => ['label'=>'Minutos','type'=>'select','options'=>'MINUTOS'],
                    'var2b1' => ['label'=>'Grado (Oficial Informante)',          'type'=>'select','options'=>'GRADOS_REDUCIDOS'],
                    'var2b2' => ['label'=>'Arma/Servicio (Oficial Informante)',  'type'=>'select','options'=>'ARMAS_SERVICIOS'],
                    'var2b3' => ['label'=>'Nombre (Oficial Informante)',         'type'=>'text',  'placeholder'=>'Nombre'],
                    'var2b4' => ['label'=>'Apellido (Oficial Informante)',       'type'=>'text',  'placeholder'=>'Apellido'],
                ],
            ],
            's3' => [
                'titulo'     => 'ACTA DECLARACION DEL CAUSANTE',
                'icono'      => 'bi-chat-left-text',
                'subtitulos' => ['Datos de Fecha','Preguntas al Causante'],
                'campos'     => [
                    'var3a1'  => ['label'=>'Dia',    'type'=>'select','options'=>'DIAS'],
                    'var3a2'  => ['label'=>'Mes',    'type'=>'select','options'=>'MESES'],
                    'var3a3'  => ['label'=>'Anio',   'type'=>'select','options'=>'ANIOS'],
                    'var3a4'  => ['label'=>'Hora',   'type'=>'select','options'=>'HORAS'],
                    'var3a5'  => ['label'=>'Minutos','type'=>'select','options'=>'MINUTOS'],
                    'var3b1'  => ['label'=>'1. Cual afeccion/lesion lo aqueja y como se produjo?',           'type'=>'text','placeholder'=>'Respuesta'],
                    'var3b2'  => ['label'=>'2. Tiene antecedentes medicos anteriores?',                      'type'=>'text','placeholder'=>'Respuesta'],
                    'var3b3'  => ['label'=>'3. Si tiene antecedentes, se los realizaron con anterioridad AJM?','type'=>'text','placeholder'=>'Respuesta'],
                    'var3b4'  => ['label'=>'4. Se le brindo la asistencia medica necesaria en su momento?',  'type'=>'text','placeholder'=>'Respuesta'],
                    'var3b5'  => ['label'=>'5. Quien fue el medico que lo atendio en la actualidad?',        'type'=>'text','placeholder'=>'Respuesta'],
                    'var3b6'  => ['label'=>'6. Como es su estado actual de salud?',                          'type'=>'text','placeholder'=>'Respuesta'],
                    'var3b7'  => ['label'=>'7. Quien puede atestiguar su afeccion?',                         'type'=>'text','placeholder'=>'Respuesta'],
                    'var3b8'  => ['label'=>'8. (Pregunta adicional)',                                        'type'=>'text','placeholder'=>'Respuesta'],
                    'var3b9'  => ['label'=>'Pregunta libre',                                                 'type'=>'text','placeholder'=>'Pregunta personalizada'],
                    'var3b10' => ['label'=>'Respuesta libre',                                                'type'=>'text','placeholder'=>'Respuesta'],
                ],
            ],
            's4' => [
                'titulo'     => 'ACTA DECLARACION — TESTIGO 1',
                'icono'      => 'bi-person-lines-fill',
                'subtitulos' => ['Datos de Fecha','Informacion del Testigo 1','Preguntas al Testigo 1'],
                'campos'     => [
                    'var4a1'  => ['label'=>'Dia',    'type'=>'select','options'=>'DIAS'],
                    'var4a2'  => ['label'=>'Mes',    'type'=>'select','options'=>'MESES'],
                    'var4a3'  => ['label'=>'Anio',   'type'=>'select','options'=>'ANIOS'],
                    'var4a4'  => ['label'=>'Hora',   'type'=>'select','options'=>'HORAS'],
                    'var4a5'  => ['label'=>'Minutos','type'=>'select','options'=>'MINUTOS'],
                    'var4b1'  => ['label'=>'Grado',                      'type'=>'select','options'=>'GRADOS_REDUCIDOS'],
                    'var4b2'  => ['label'=>'Arma/Servicio/Especialidad', 'type'=>'select','options'=>'ARMAS_SERVICIOS'],
                    'var4b3'  => ['label'=>'Nombre',  'type'=>'text',  'placeholder'=>'Nombre del Testigo 1'],
                    'var4b4'  => ['label'=>'Apellido','type'=>'text',  'placeholder'=>'Apellido del Testigo 1'],
                    'var4b5'  => ['label'=>'Edad',    'type'=>'number','placeholder'=>'Edad'],
                    'var4b6'  => ['label'=>'DNI',     'type'=>'number','placeholder'=>'DNI'],
                    'var4c1'  => ['label'=>'1. Tiene conocimiento de la afeccion/lesion del causante y desde cuando?','type'=>'text','placeholder'=>'Respuesta'],
                    'var4c2'  => ['label'=>'2. Que sintomas observa u observo?',                                      'type'=>'text','placeholder'=>'Respuesta'],
                    'var4c3'  => ['label'=>'3. Se hizo atender por el servicio medico de su unidad?',                 'type'=>'select','options'=>'SI_NO'],
                    'var4c3a' => ['label'=>'3a. Donde fue atendido?',                                                 'type'=>'select','options'=>'LUGARES_ATENCION_MEDICA'],
                    'var4c4'  => ['label'=>'4. Se le brindo la correspondiente asistencia medica?',                   'type'=>'select','options'=>'SI_NO'],
                    'var4c5'  => ['label'=>'5. Considera que se produjo en ACTOS DEL SERVICIO?',                      'type'=>'select','options'=>'SI_NO'],
                    'var4c6'  => ['label'=>'6. Tiene algo que agregar, quitar o enmendar?',                           'type'=>'text',  'placeholder'=>'Respuesta'],
                ],
            ],
            's5' => [
                'titulo'     => 'ACTA DECLARACION — TESTIGO 2',
                'icono'      => 'bi-person-lines-fill',
                'subtitulos' => ['Datos de Fecha','Informacion del Testigo 2','Preguntas al Testigo 2'],
                'campos'     => [
                    'var5a1'  => ['label'=>'Dia',    'type'=>'select','options'=>'DIAS'],
                    'var5a2'  => ['label'=>'Mes',    'type'=>'select','options'=>'MESES'],
                    'var5a3'  => ['label'=>'Anio',   'type'=>'select','options'=>'ANIOS'],
                    'var5a4'  => ['label'=>'Hora',   'type'=>'select','options'=>'HORAS'],
                    'var5a5'  => ['label'=>'Minutos','type'=>'select','options'=>'MINUTOS'],
                    'var5b1'  => ['label'=>'Grado',                      'type'=>'select','options'=>'GRADOS_REDUCIDOS'],
                    'var5b2'  => ['label'=>'Arma/Servicio/Especialidad', 'type'=>'select','options'=>'ARMAS_SERVICIOS'],
                    'var5b3'  => ['label'=>'Nombre',  'type'=>'text',  'placeholder'=>'Nombre del Testigo 2'],
                    'var5b4'  => ['label'=>'Apellido','type'=>'text',  'placeholder'=>'Apellido del Testigo 2'],
                    'var5b5'  => ['label'=>'Edad',    'type'=>'number','placeholder'=>'Edad'],
                    'var5b6'  => ['label'=>'DNI',     'type'=>'number','placeholder'=>'DNI'],
                    'var5c1'  => ['label'=>'1. Tiene conocimiento de la afeccion/lesion del causante y desde cuando?','type'=>'text','placeholder'=>'Respuesta'],
                    'var5c2'  => ['label'=>'2. Que sintomas observa u observo?',                                      'type'=>'text','placeholder'=>'Respuesta'],
                    'var5c3'  => ['label'=>'3. Se hizo atender por el servicio medico de su unidad?',                 'type'=>'select','options'=>'SI_NO'],
                    'var5c3a' => ['label'=>'3a. Donde fue atendido?',                                                 'type'=>'select','options'=>'LUGARES_ATENCION_MEDICA'],
                    'var5c4'  => ['label'=>'4. Se le brindo la correspondiente asistencia medica?',                   'type'=>'select','options'=>'SI_NO'],
                    'var5c5'  => ['label'=>'5. Considera que se produjo en ACTOS DEL SERVICIO?',                      'type'=>'select','options'=>'SI_NO'],
                    'var5c6'  => ['label'=>'6. Tiene algo que agregar, quitar o enmendar?',                           'type'=>'text',  'placeholder'=>'Respuesta'],
                ],
            ],
            's6' => [
                'titulo'     => 'CONCLUSIONES',
                'icono'      => 'bi-check2-circle',
                'subtitulos' => ['Afeccion/Lesion Causante','Asistio al Causante','Fecha de Cancelacion'],
                'campos'     => [
                    'var6a1' => ['label'=>'Afeccion/Lesion',               'type'=>'text',   'placeholder'=>'Afeccion o lesion'],
                    'var6a2' => ['label'=>'Causa de Afeccion/Lesion',      'type'=>'text',   'placeholder'=>'Causa'],
                    'var6a3' => ['label'=>'Obran Antecedentes',            'type'=>'select', 'options'=>'SI_NO'],
                    'var6a4' => ['label'=>'Actos del Servicio',            'type'=>'select', 'options'=>'SI_NO'],
                    'var6a5' => ['label'=>'Asistencia Medica en el Momento','type'=>'select','options'=>'SI_NO'],
                    'var6b1' => ['label'=>'Grado (medico que asistio)',    'type'=>'select', 'options'=>'GRADOS_REDUCIDOS'],
                    'var6b2' => ['label'=>'Arma/Servicio (medico)',        'type'=>'select', 'options'=>'ARMAS_SERVICIOS'],
                    'var6b3' => ['label'=>'Nombre (medico)',               'type'=>'text',   'placeholder'=>'Nombre'],
                    'var6b4' => ['label'=>'Apellido (medico)',             'type'=>'text',   'placeholder'=>'Apellido'],
                    'var6c1' => ['label'=>'Mes (fecha cancelacion)',       'type'=>'select', 'options'=>'MESES'],
                    'var6c2' => ['label'=>'Anio (fecha cancelacion)',      'type'=>'select', 'options'=>'ANIOS'],
                ],
            ],
        ];
    }

    /** Retorna las secciones según el tipo de actuación. */
    public static function secciones(string $tipo): array
    {
        return match($tipo) {
            'bienes'    => self::secciones_bienes(),
            'accidente' => self::secciones_accidente(),
            default     => [],
        };
    }

    /** Metadatos de cada tipo de actuacion. */
    public static function meta(string $tipo): array
    {
        return match($tipo) {
            'bienes' => [
                'titulo_corto' => 'Bienes del Estado',
                'titulo_largo' => 'Actuación por Pérdida, Ruptura o Inutilización de Bienes del Estado',
                'icono'        => 'bi-box-seam',
                'color'        => 'warning',
            ],
            'accidente' => [
                'titulo_corto' => 'Accidente / Enfermedad',
                'titulo_largo' => 'Actuación por Accidente / Enfermedad',
                'icono'        => 'bi-heart-pulse',
                'color'        => 'danger',
            ],
            default => [],
        };
    }
}
