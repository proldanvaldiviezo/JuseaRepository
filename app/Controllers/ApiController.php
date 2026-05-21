<?php
namespace App\Controllers;

use App\Models\PersonaModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ApiController extends Controller
{
    /**
     * Buscar persona por DNI. Retorna JSON.
     * GET /api/persona/buscar-dni?dni=12345678&tipo=cuadro
     */
    public function buscarPersonaPorDni(): ResponseInterface
    {
        $dni = trim($this->request->getGet('dni') ?? '');

        if (strlen($dni) < 7) {
            return $this->response->setJSON(['found' => false, 'message' => 'DNI inválido']);
        }

        $model   = new PersonaModel();
        $persona = $model->buscarPorDni($dni);

        if (!$persona) {
            return $this->response->setJSON(['found' => false, 'message' => 'No encontrado']);
        }

        return $this->response->setJSON([
            'found' => true,
            'data'  => $this->personaToArray($persona),
        ]);
    }

    /**
     * Buscar persona por apellido (autocompletar).
     * GET /api/persona/buscar-apellido?q=GONZA&tipo=cuadro
     */
    public function buscarPersonaPorApellido(): ResponseInterface
    {
        $q = trim($this->request->getGet('q') ?? '');

        if (strlen($q) < 2) {
            return $this->response->setJSON([]);
        }

        $model    = new PersonaModel();
        $personas = $model->buscarPorApellido($q);

        return $this->response->setJSON(array_map([$this, 'personaToArray'], $personas));
    }

    /**
     * Buscar persona por nombre (autocompletar).
     * GET /api/persona/buscar-nombre?q=JUAN&tipo=cuadro
     */
    public function buscarPersonaPorNombre(): ResponseInterface
    {
        $q = trim($this->request->getGet('q') ?? '');

        if (strlen($q) < 2) {
            return $this->response->setJSON([]);
        }

        $model    = new PersonaModel();
        $personas = $model->buscarPorNombre($q);

        return $this->response->setJSON(array_map([$this, 'personaToArray'], $personas));
    }

    /**
     * Buscar usuario en AspNetUsers para asignar acceso JUSEA.
     * Devuelve apellido, nombre, grado, DNI y si ya tiene rol en JUSEA.
     * GET /api/usuarios/buscar?q=TEXTO
     */
    public function buscarUsuarioParaAcceso(): ResponseInterface
    {
        $q = trim($this->request->getGet('q') ?? '');

        if (strlen($q) < 2) {
            return $this->response->setJSON([]);
        }

        $db   = \Config\Database::connect();
        $rows = $db->table('AspNetUsers')
            ->select('AspNetUsers.Id, AspNetUsers.DNI, AspNetUsers.Apellido,
                      AspNetUsers.Nombre, AspNetUsers.idGrado AS GRADO, AspNetUsers.ARMA,
                      JUSEA_UsuarioRol.rol, JUSEA_UsuarioRol.activo AS activo_jusea')
            ->join('JUSEA_UsuarioRol', 'JUSEA_UsuarioRol.user_id = AspNetUsers.Id', 'left')
            ->groupStart()
                ->like('AspNetUsers.Apellido', $q, 'both')
                ->orLike('AspNetUsers.Nombre',  $q, 'both')
                ->orLike('AspNetUsers.DNI',     $q, 'both')
            ->groupEnd()
            ->where('(AspNetUsers.bajaUnidad = 0 OR AspNetUsers.bajaUnidad IS NULL)', null, false)
            ->orderBy('AspNetUsers.Apellido', 'ASC')
            ->limit(12)
            ->get()->getResultObject();

        $result = array_map(fn($r) => [
            'id'           => $r->Id,
            'dni'          => $r->DNI          ?? '',
            'apellido'     => $r->Apellido     ?? '',
            'nombre'       => $r->Nombre       ?? '',
            'grado'        => $r->GRADO        ?? '',
            'arma'         => $r->ARMA         ?? '',
            'rol_jusea'    => $r->rol          ?? null,
            'activo_jusea' => (bool)($r->activo_jusea ?? false),
        ], $rows);

        return $this->response->setJSON($result);
    }

    private function personaToArray(object $p): array
    {
        return [
            'id'               => $p->id,
            'dni'              => $p->dni,
            'apellido'         => $p->apellido,
            'nombre'           => $p->nombre           ?? '',
            'grado'            => $p->grado            ?? '',
            'arma_especialidad'=> $p->arma_especialidad ?? '',
            'destino_interno'  => '',
            'cargo'            => '',
            'tipo'             => '',
        ];
    }

    // =========================================================================
    // ENDPOINT IA — Redactar / mejorar texto de MOTIVOS con Claude API
    // POST /api/ia/redactar-motivos
    // Body JSON: { tipo, grado, nombre, fecha, articulo, inciso, dias, borrador }
    // =========================================================================
    public function redactarMotivos(): ResponseInterface
    {
        $apiKey = env('ANTHROPIC_API_KEY', '');
        if (empty($apiKey)) {
            return $this->response->setJSON([
                'error' => 'API key de Anthropic no configurada. Completá la variable ANTHROPIC_API_KEY en el archivo .env'
            ]);
        }

        $body = $this->request->getJSON(true) ?? [];

        $tipo     = strtoupper(trim($body['tipo']     ?? ''));
        $grado    = trim($body['grado']    ?? '');
        $nombre   = trim($body['nombre']   ?? '');
        $fecha    = trim($body['fecha']    ?? '');
        $articulo = trim($body['articulo'] ?? '');
        $inciso   = trim($body['inciso']   ?? '');
        $dias     = trim($body['dias']     ?? '');
        $borrador = trim($body['borrador'] ?? '');

        if (empty($borrador)) {
            return $this->response->setJSON(['error' => 'El borrador del motivo no puede estar vacío.']);
        }

        $esCadete = ($tipo === 'CADETE');
        $tipoPersonal = $esCadete
            ? 'Cadete del Colegio Militar de la Nación'
            : 'Personal Militar de Cuadros (Oficial / Suboficial / Soldado)';

        $normativaCadetes =
            "RÉGIMEN DISCIPLINARIO APLICABLE — CADETES DEL CMN:\n" .
            "- Reglamento Disciplinario Formativo (RDF) del Colegio Militar de la Nación\n" .
            "- Las faltas se clasifican en leves, graves y muy graves conforme al RDF\n" .
            "- Sanciones formativas: amonestación, consignación, arresto simple y arresto riguroso\n" .
            "- Bien jurídico tutelado principal: disciplina formativa, subordinación y honor militar\n" .
            "- Criterios de proporcionalidad: art. 4 RDF (gravedad, reincidencia, circunstancias)\n" .
            "- El infractor reviste como cadete en formación; las faltas afectan su proceso educativo-militar";

        $normativaCuadros =
            "RÉGIMEN DISCIPLINARIO APLICABLE — PERSONAL DE CUADROS (Oficiales, Suboficiales y Soldados):\n" .
            "- Código de Disciplina de las Fuerzas Armadas (CDFFAA): Ley 26.394, Anexo IV\n" .
            "- Decreto Reglamentario 2666/12 del CDFFAA\n" .
            "- Art. 9 CDFFAA: faltas disciplinarias leves — arresto simple de 1 a 15 días\n" .
            "- Art. 10 CDFFAA: faltas disciplinarias graves — arresto riguroso de 16 a 30 días\n" .
            "- Art. 19 CDFFAA: criterios de graduación (gravedad del hecho, antecedentes, jerarquía)\n" .
            "- Art. 20 CDFFAA: circunstancias agravantes\n" .
            "- Art. 21 CDFFAA: circunstancias atenuantes\n" .
            "- Decreto 2666/12, art. 7: deber de fundamentación circunstanciada del acto sancionatorio\n" .
            "- Bienes jurídicos tutelados: disciplina militar, subordinación, decoro, servicio, honor institucional";

        $normativaAplicable = $esCadete ? $normativaCadetes : $normativaCuadros;
        $regimen = $esCadete ? 'RDF para Cadetes' : 'CDFFAA / Ley 26.394 y Decreto 2666/12';

        $prompt =
            "Sos un abogado especialista en derecho disciplinario militar argentino, redactor experto de órdenes de sanción del Ejército Argentino y del Colegio Militar de la Nación.\n\n" .

            "════════════════════════════════════\n" .
            "CONTEXTO DE LA SANCIÓN\n" .
            "════════════════════════════════════\n" .
            "Tipo de personal : {$tipoPersonal}\n" .
            "Grado y apellido : {$grado} {$nombre}\n" .
            "Fecha de la falta: " . ($fecha ?: '_______') . "\n" .
            "Artículo imputado: " . ($articulo ?: 'no especificado') . "\n" .
            "Inciso imputado  : " . ($inciso   ?: 'no especificado') . "\n" .
            "Días de arresto  : " . ($dias     ?: 'no especificado') . "\n\n" .

            "════════════════════════════════════\n" .
            "{$normativaAplicable}\n\n" .

            "════════════════════════════════════\n" .
            "PAUTAS DE ESTILO INSTITUCIONAL (orientativas — no limitantes)\n" .
            "════════════════════════════════════\n" .
            "— Conducta activa/agresiva:\n" .
            "\"Haber proferido expresiones agraviantes al personal subordinado, en las instalaciones de la Unidad, provocando un grave menoscabo a la disciplina militar.\"\n\n" .
            "— Conducta omisiva:\n" .
            "\"No haber concurrido al servicio de guardia en la fecha y horario establecidos, omitiendo dar aviso a la superioridad, ocasionando perjuicio al normal desarrollo del servicio.\"\n\n" .
            "— Incumplimiento de deber funcional:\n" .
            "\"Dejar de observar las normas de conducta y compostura militar exigidas a su jerarquía, en el ejercicio de sus funciones, con quebranto del decoro y la imagen institucional.\"\n\n" .
            "— Insubordinación:\n" .
            "\"Haber desobedecido la orden impartida por su superior jerárquico en acto de servicio, afectando el orden jerárquico y la subordinación debida.\"\n\n" .

            "CARACTERÍSTICAS ESTILÍSTICAS:\n" .
            "1. Comenzar con verbo en infinitivo según la naturaleza de la conducta:\n" .
            "   — Activas:  \"Haber proferido...\", \"Haber agredido...\", \"Haber desobedecido...\"\n" .
            "   — Omisivas: \"No haber concurrido...\", \"Omitir el cumplimiento de...\", \"Dejar de observar...\"\n" .
            "2. Encadenar conductas múltiples con: \"y, seguidamente,\", \"así como\", \"omitiendo además\".\n" .
            "3. Identificar terceros: grado + nombre (APELLIDO en mayúsculas) + \"DNI N.º XX.XXX.XXX\".\n" .
            "4. Referenciar ámbito funcional: \"en su puesto de servicio\", \"en las instalaciones de la Unidad/Instituto\".\n" .
            "5. Cerrar con bien jurídico afectado: \"menoscabo a la disciplina militar\", \"afectación al orden jerárquico\", etc.\n" .
            "6. Sin punto final. Una o dos oraciones como máximo.\n\n" .

            "════════════════════════════════════\n" .
            "DESCRIPCIÓN DE LA FALTA (ingresada por el operador)\n" .
            "════════════════════════════════════\n" .
            "{$borrador}\n\n" .

            "════════════════════════════════════\n" .
            "TAREA\n" .
            "════════════════════════════════════\n" .
            "Realizá las siguientes acciones sobre el texto del operador:\n\n" .
            "1. CORRECCIÓN ORTOGRÁFICA Y GRAMATICAL: corregí todos los errores de ortografía, acentuación, puntuación y gramática.\n\n" .
            "2. CORRECCIÓN DE REDACCIÓN: mejorá claridad, coherencia y precisión sin alterar los hechos. Eliminá repeticiones, ambigüedades y expresiones coloquiales.\n\n" .
            "3. ADECUACIÓN AL ESTILO INSTITUCIONAL: adaptá el texto al estilo formal del Ejército Argentino usando las pautas de referencia como orientación, no como molde rígido.\n\n" .
            "4. CONTEXTUALIZACIÓN JURÍDICA: vinculá la conducta con el régimen disciplinario aplicable ({$regimen}), el artículo e inciso imputado, el deber funcional incumplido y el bien jurídico afectado.\n\n" .
            "5. TEXTO GUÍA: el resultado debe servir de base para que el operador complete las circunstancias concretas. No inventés hechos, personas ni datos ausentes en el borrador.\n\n" .
            "REGLAS FINALES:\n" .
            "- Estilo formal, técnico, en tercera persona\n" .
            "- Extensión: 80 a 160 palabras\n" .
            "- Sin punto final\n" .
            "- Sin encabezados, títulos ni aclaraciones\n" .
            "- Devolvé únicamente el texto redactado, sin ningún agregado";

        $payload = json_encode([
            'model'      => 'claude-opus-4-6',
            'max_tokens' => 1200,
            'messages'   => [['role' => 'user', 'content' => $prompt]]
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $raw    = curl_exec($ch);
        $errno  = curl_errno($ch);
        curl_close($ch);

        if ($errno) {
            return $this->response->setJSON(['error' => 'Error de conexión con la API de Anthropic (cURL errno: ' . $errno . ')']);
        }

        $result = json_decode($raw, true);

        if (!empty($result['error'])) {
            return $this->response->setJSON(['error' => $result['error']['message'] ?? 'Error desconocido de la API']);
        }

        $texto = $result['content'][0]['text'] ?? '';
        if (empty($texto)) {
            return $this->response->setJSON(['error' => 'La API no devolvió texto. Respuesta: ' . $raw]);
        }

        return $this->response->setJSON(['texto' => trim($texto)]);
    }

    // =========================================================================
    // ENDPOINT IA — Sugerir Artículo e Inciso según la falta descripta
    // POST /api/ia/sugerir-normativa
    // Body JSON: { tipo, borrador }
    // Retorna: { articulo, inciso, tipo_falta, justificacion }
    // =========================================================================
    public function sugerirNormativa(): ResponseInterface
    {
        $apiKey = env('ANTHROPIC_API_KEY', '');
        if (empty($apiKey)) {
            return $this->response->setJSON([
                'error' => 'API key de Anthropic no configurada. Completá la variable ANTHROPIC_API_KEY en el archivo .env'
            ]);
        }

        $body    = $this->request->getJSON(true) ?? [];
        $tipo    = strtoupper(trim($body['tipo']    ?? ''));
        $borrador = trim($body['borrador'] ?? '');

        if (empty($borrador)) {
            return $this->response->setJSON(['error' => 'Describí la falta antes de solicitar sugerencia normativa.']);
        }

        $esCadete = ($tipo === 'CADETE');

        // ── NORMATIVA CDFFAA — CUADROS (Arts. 9 y 10) ────────────────────────
        $normativaCuadros = <<<'NORM'
CÓDIGO DE DISCIPLINA DE LAS FUERZAS ARMADAS (CDFFAA) — Ley 26.394, Anexo IV
Decreto Reglamentario 2666/12

ART. 9 — FALTAS LEVES (Arresto simple de 1 a 15 días):
Inc 1: Tutear al superior o tratarlo de forma irrespetuosa.
Inc 2: No saludar o devolver el saludo a quien corresponda.
Inc 3: Presentarse o conducirse con negligencia o desaseo en actos del servicio.
Inc 4: Fumar, comer o beber en lugares o actos en que esté prohibido.
Inc 5: Dormir en actos de servicio.
Inc 6: Leer o ejecutar actividades ajenas al servicio durante actos del mismo.
Inc 7: Ausentarse injustificadamente del lugar de sus funciones, sin perjuicio para el servicio.
Inc 8: No entregar en término trabajos encomendados, sin perjuicio para el servicio.
Inc 9: Tratar al subalterno o civil en términos descorteses o injuriosos no constitutivos de falta grave.
Inc 10: Descuidar el aseo, conservación o correcta utilización del material a su cargo.
Inc 11: No cumplir oportunamente una orden del superior, sin perjuicio para el servicio.
Inc 12: No dar cumplimiento a normas reglamentarias de orden y disciplina, sin perjuicio para el servicio.
Inc 13: Presentar en términos irrespetuosos peticiones, recursos o reclamaciones.
Inc 14: Concurrir a lugares no adecuados a la dignidad del estado militar o comportarse de modo indecoroso en público.
Inc 15: Emitir expresiones inconvenientes sobre el servicio o actos del mismo.
Inc 16: Ejecutar en horas de servicio trabajos ajenos al mismo, sin perjuicio para el servicio.
Inc 17: No dar cumplimiento a obligaciones económicas contraídas, sin perjuicio real para terceros.
Inc 18: Descuidar la conservación, revisión o mantenimiento del armamento u otro material de guerra.

ART. 10 — FALTAS GRAVES (Arresto riguroso de 16 a 30 días):
Inc 1: Embriagarse, cualquiera fuera el lugar o la ocasión.
Inc 2: Jugar por dinero u otros valores en actos o lugares del servicio o donde esté prohibido.
Inc 3: Adquirir mediante engaño bienes o servicios del Estado o de miembros de las FFAA.
Inc 4: Comprometer la honorabilidad del estado militar con conductas deshonrosas.
Inc 5: Amenazar o injuriar al superior, par o subalterno.
Inc 6: Encubrir una falta disciplinaria.
Inc 7: Faltar a la verdad en un acto del servicio.
Inc 8: No dar cumplimiento a una orden cuando de ello se derive perjuicio para el servicio.
Inc 9: Ausentarse injustificadamente del servicio cuando de ello se derive perjuicio para el servicio.
Inc 10: No cumplir en tiempo y forma las obligaciones económicas que emanen del servicio.
Inc 11: Ejercer actividades ajenas al servicio en horas de servicio con perjuicio para el mismo.
Inc 12: Formular o propalar noticias o comentarios falsos o tendenciosos sobre la institución.
Inc 13: Participar en actividades o manifestaciones políticas partidistas.
Inc 14: Tratar al subalterno de forma indecorosa u ofensiva causándole humillación o sufrimiento innecesarios, o ejecutar sobre él actos que importen abuso de autoridad.
Inc 15: Agredir físicamente al superior, par o subalterno sin llegar a vías de hecho.
Inc 16: Incurrir en tercera falta leve de la misma naturaleza en el mismo año.
NORM;

        // ── NORMATIVA RDF — CADETES (Puntos 25, 26 y 27) ─────────────────────
        $normativaCadetes = <<<'NORM'
RÉGIMEN DISCIPLINARIO FORMATIVO (RDF) — COLEGIO MILITAR DE LA NACIÓN (2022)

PUNTO 25 — FALTAS INSTITUCIONALES LEVES (hasta 29 días de arresto, observación o apercibimiento):
Nro 1: Tutearse en actos del servicio.
Nro 2: Dormitar o estar desatento durante el desarrollo de la clase/instrucción.
Nro 3: Fumar en presencia de un superior sin autorización o en lugar no autorizado.
Nro 4: Ejecutar cualquier actividad luego de retreta sin autorización.
Nro 5: Concurrir a la peluquería fuera de los horarios autorizados.
Nro 6: Moverse en formación sin autorización.
Nro 7: No demostrar la energía necesaria en una actividad ordenada.
Nro 8: No entregar en término un trabajo ordenado.
Nro 9: Negligente en su desempeño como Encargado de Curso.
Nro 10: No ejecutar de manera correcta un movimiento de orden cerrado.
Nro 11: No cumplir con las obligaciones vinculadas al orden cerrado.
Nro 12: No tener un corte de cabello reglamentario.
Nro 13: Presentarse en locales u oficinas militares vestido de civil sin autorización.
Nro 14: No cumplir con las normas de conducta en la mesa.
Nro 15: No concurrir con presteza al llamado de un superior.
Nro 16: No saludar a un superior.
Nro 17: Alterar el espíritu de cuerpo.
Nro 18: Retirarse del Pabellón de Estudios sin autorización del Servicio de Vigilancia.
Nro 19: Negligente en el cuidado de su salud.
Nro 20: Negligente en el cuidado del material de estudios/instrucción.
Nro 21: Modificar en parte o todo el uniforme.
Nro 22: Mantener una conducta no reglamentaria en presencia de un superior.
Nro 23: No dar cumplimiento a la orden de recibir visitas dentro de los sectores controlados.
Nro 24: Ejecutar actividad no relacionada con el estudio durante la hora de preparación.
Nro 25: No mantener la debida disciplina en la fracción a su mando.
Nro 26: Impartir órdenes del servicio sin la energía y modulación apropiada.
Nro 27: Tener familiaridad en grado vergonzoso con los subalternos, menoscabando la disciplina.
Nro 28: Dejar de informar una solicitud o no darle curso, sin causar perjuicio grave.
Nro 29: Demostrar negligencia en la confección de documentación y cumplimiento de órdenes impartidas.
Nro 30: Negligente en el cuidado y mantenimiento de su equipo.
Nro 31: Tomarse a golpes sin generar lesiones.
Nro 32: Participar en actos sociales que no condigan con su Estado Militar.
Nro 33: Negligente en su desempeño durante un servicio de la subunidad.
Nro 34: Sentarse mientras se desempeña como Imaginaria.
Nro 35: No ejercer control individual para evitar pérdidas de efectos del personal a su cargo.
Nro 36: No encontrarse en su puesto para actos del servicio.
Nro 37: No informar a la Cadena de Comando faltas disciplinarias de personal subalterno.
Nro 38: No conducirse de manera reglamentaria durante actos sociales ordenados por la superioridad.
Nro 39: Efectuar observaciones no autorizadas a un superior.
Nro 40: Extraviar de manera negligente la cédula militar.
Nro 41: Tomar parte en juegos de azar sin autorización.
Nro 42: Demostrar negligencia en su desempeño durante el Servicio de Armas.
Nro 43: Perjudicar al subalterno sin llegar a incurrir en delito.
Nro 44: Encontrándose licenciado, ser llamado a cumplir servicio y no hacerlo con la oportunidad debida.
Nro 45: Faltar a la verdad sobre cuestiones que afecten el normal cumplimiento de tareas y objetivos formativos.
Nro 46: No cumplir una orden general o consigna.
Nro 47: Faltarle el respeto a un superior o subalterno sin cometer falta grave.
Nro 48: Retirarse antes de cualquier actividad sin la debida autorización.
Nro 49: Publicar sin autorización en redes sociales fotos, videos o audios vinculados con la instrucción formativa militar.
Nro 50: No cancelar en tiempo y forma una deuda o compromiso económico cuyo acreedor sea el Instituto.
Nro 51: Alistarse antes de diana sin autorización.
Nro 52: Intentar sorprender la buena fe de un superior.
Nro 53: Retirarse de la Subunidad sin la debida autorización.
Nro 54: No dar cumplimiento a las normas del Régimen Funcional del Pabellón de Estudios.
Nro 55: Tomarse atribuciones que no corresponden a su grado o cargo.
Nro 56: No devolver en término un efecto solicitado (cargos, libros, MAPE, etc.).
Nro 57: Concluido el trámite de baja voluntaria, arrepentirse y solicitar reincorporación en el mismo momento.
Nro 58: Usar el uniforme en forma incorrecta.
Nro 59: No tener aseo o prolijidad en ocasión de actividades relacionadas con su formación académica.
Nro 60: Actos u omisiones análogos, sin consecuencias mayores para la formación militar.

PUNTO 26 — FALTAS INSTITUCIONALES GRAVES (arresto hasta la baja):
Nro 1: Presentar recurso, reclamo o peticiones en términos descorteses o inmorales.
Nro 2: Actos de desobediencia que causen grave daño a la disciplina o al cumplimiento de tareas formativas.
Nro 3: Reprender al subalterno en términos indecorosos u ofensivos, vejarlo u ordenarle actividades no autorizadas.
Nro 4: Efectuar críticas a un superior en presencia de subalternos o en voz alta.
Nro 5: Sustraerse al servicio por falsa enfermedad, males supuestos o sin justa causa.
Nro 6: Efectuar manifestaciones públicas que cuestionen planes, directivas u órdenes de cualquier nivel de comando de las FFAA o del gobierno.
Nro 7: No concurrir a un servicio de armas sin causa justificada.
Nro 8: Ser reincidente por tercera vez en una misma falta institucional leve.
Nro 9: Realizar actos o manifestaciones que agravien o injurien a otro militar.
Nro 10: Realizar manifestaciones irrespetuosas contra el culto religioso.
Nro 11: Ingresar o retirarse del Instituto sin debida autorización, afectando el desarrollo de la instrucción.
Nro 12: Ejercer actividad política partidaria.
Nro 13: No informar a la Cadena de Comando hechos relacionados con su estado de salud que afecten el desarrollo del curso.
Nro 14: Apoderarse indebidamente de efectos de otro para beneficiarse o perjudicar a un tercero, sin incurrir en delito.
Nro 15: Discriminación por raza, condición social, sexo o religión.
Nro 16: Faltar a la verdad sin incurrir en delito.
Nro 17: Cometer fraude, o intentarlo o favorecerlo, en exámenes, trabajos o actividades de calificación.
Nro 18: Cumplir tareas bajo los efectos de sustancias estimulantes/estupefacientes o en estado de embriaguez (sin llegar a expulsión).
Nro 19: Dejar de informar una solicitud o no darle curso cuando ello cause un perjuicio grave.
Nro 20: Encontrarse en el Instituto o cumplir tareas bajo efectos de sustancias estimulantes/estupefacientes o embriaguez (sin llegar a expulsión).
Nro 21: Dar resultado positivo en prueba de detección de sustancias estimulantes, estupefacientes o estado de embriaguez.
Nro 22: Actos u omisiones análogos que conlleven grave menoscabo a la disciplina formativo-militar.

PUNTO 27 — FALTAS INSTITUCIONALES PASIBLES DE EXPULSIÓN:
Nro 1 (Agresión): Agredir y causar lesiones graves, gravísimas o muerte en actividades del Instituto.
Nro 2 (Coacción al Superior): Con violencia física o intimidación obligar al superior a ejecutar u omitir una obligación propia de su estado.
Nro 3 (Insubordinación): Resistencia ostensible o rechazo expreso a obedecer una orden de su Cadena de Comando.
Nro 4 (Motín): Reclamar tumultuosamente en número superior a cuatro, provocando daños o desorden.
Nro 5 (Instigación al motín): Instigar, proponer o incitar a provocar un motín.
Nro 6 (Instigación a la desobediencia): Incitar a otro al incumplimiento de una orden directa o debilitar la disciplina.
Nro 7 (Abuso de autoridad): Abusar de su autoridad para obligar a otro cadete a realizar actos ajenos a la formación militar.
Nro 8 (Ejercicios arriesgados): Iniciar o emprender actividades que pongan en riesgo integridad física propia o ajena, sin autorización.
Nro 9 (Omisión de auxilio): En actividades de campaña, omitir prestar auxilio a un camarada pudiendo hacerlo, causando daños graves o muerte.
Nro 10 (Acoso sexual): Requerimiento sexual bajo amenaza de causar daño a la víctima.
Nro 11 (Abuso sexual): Practicar o inducir conductas abusivas, denigrantes o deshonrosas que atenten contra la libertad o integridad sexual.
Nro 12 (Ausencia del Instituto): No regresar de franco o licencia por más de 48 horas sin autorización o justa causa.
Nro 13 (Apoderamiento gravísimo): Apoderarse de efectos de otro con propósito de perjuicio gravísimo, sin incurrir en delito.
NORM;

        $normativaAplicable = $esCadete ? $normativaCadetes : $normativaCuadros;
        $regimen = $esCadete
            ? 'RDF CMN 2022 (Puntos 25, 26 y 27)'
            : 'CDFFAA Ley 26.394 Anexo IV (Arts. 9 y 10)';

        $instruccionArticulo = $esCadete
            ? '"articulo": "Punto 25" o "Punto 26" o "Punto 27"'
            : '"articulo": "Art. 9" o "Art. 10"';

        $instruccionInciso = $esCadete
            ? '"inciso": número de la falta (Nro 1 a Nro 60 para Pto 25; Nro 1 a Nro 22 para Pto 26; Nro 1 a Nro 13 para Pto 27)'
            : '"inciso": número del inciso (Inc 1 a Inc 18 para Art. 9; Inc 1 a Inc 16 para Art. 10)';

        $prompt =
            "Sos un abogado especialista en derecho disciplinario militar argentino con dominio del {$regimen}.\n\n" .
            "════════════════════════════════════\n" .
            "NORMATIVA APLICABLE\n" .
            "════════════════════════════════════\n" .
            "{$normativaAplicable}\n\n" .
            "════════════════════════════════════\n" .
            "DESCRIPCIÓN DE LA FALTA (ingresada por el operador)\n" .
            "════════════════════════════════════\n" .
            "{$borrador}\n\n" .
            "════════════════════════════════════\n" .
            "TAREA\n" .
            "════════════════════════════════════\n" .
            "Analizá la descripción de la falta y determiná cuál es el artículo e inciso que mejor encuadra la conducta.\n\n" .
            "Si la conducta puede encuadrarse en más de un inciso, elegí el más específico y el que mayor gravedad refleje.\n\n" .
            "DEVOLVÉ ÚNICAMENTE un JSON válido con esta estructura, sin ningún texto adicional antes ni después:\n" .
            "{\n" .
            "  {$instruccionArticulo},\n" .
            "  {$instruccionInciso},\n" .
            "  \"tipo_falta\": \"leve\" o \"grave\" o \"expulsion\",\n" .
            "  \"justificacion\": \"Una oración breve explicando por qué esa norma encuadra la conducta descripta.\"\n" .
            "}";

        $payload = json_encode([
            'model'      => 'claude-opus-4-6',
            'max_tokens' => 400,
            'messages'   => [['role' => 'user', 'content' => $prompt]]
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_TIMEOUT => 45,
        ]);

        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno) {
            return $this->response->setJSON(['error' => 'Error de conexión cURL: ' . $errno]);
        }

        $result = json_decode($raw, true);
        if (!empty($result['error'])) {
            return $this->response->setJSON(['error' => $result['error']['message'] ?? 'Error de API']);
        }

        $rawText = trim($result['content'][0]['text'] ?? '');
        if (empty($rawText)) {
            return $this->response->setJSON(['error' => 'La API no devolvió texto.']);
        }

        // Extraer JSON de la respuesta (por si viene con markdown ```json ... ```)
        if (preg_match('/\{.*\}/s', $rawText, $m)) {
            $rawText = $m[0];
        }

        $sugerencia = json_decode($rawText, true);
        if (!$sugerencia || empty($sugerencia['articulo'])) {
            return $this->response->setJSON(['error' => 'No se pudo interpretar la respuesta de la IA: ' . $rawText]);
        }

        return $this->response->setJSON([
            'articulo'      => $sugerencia['articulo']      ?? '',
            'inciso'        => $sugerencia['inciso']        ?? '',
            'tipo_falta'    => $sugerencia['tipo_falta']    ?? '',
            'justificacion' => $sugerencia['justificacion'] ?? '',
        ]);
    }
}
