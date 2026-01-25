<?php
class Sondeo
{
    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $db = new DbConection();
        $pdo = $db->openConect();
        $q = "SELECT * FROM " . $db->getTable('tbl_sondeo');
        $params = [];
        if ($id > 0) {
            $q .= " WHERE id = :id";
            $params[':id'] = $id;
        }
        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Informacion de los candidatos a postular
            foreach ($arr as $key => $value) {
                $qSondeoCandidato = "SELECT * FROM " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " 
                WHERE tbl_sondeo_id = :id";

                $qSondeoCandidato = "SELECT 
                    p.id, p.tbl_cargo_publico_id, p.nombre_completo, p.codigo_departamento, p.codigo_municipio, p.dtcreate, p.foto, p.habilitado,
                    cp.nombre AS cargo_publico, cp.sigla AS sigla_cargo,
                    d.departamento AS nombre_departamento,
                    c.municipio AS nombre_municipio,
                    p.habilitado,
                    GROUP_CONCAT(pxp.tbl_partido_politico_id) AS partidoPoliticoIds,
                    GROUP_CONCAT(pp.nombre_partido SEPARATOR ', ') AS nombres_partidos
                FROM " . $db->getTable('tbl_participantes') . " p
                INNER JOIN " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " sxp
                    ON p.id = sxp.tbl_participante_id
                LEFT JOIN " . $db->getTable('tbl_participantes_x_partidos_politicos') . " pxp
                    ON p.id = pxp.tbl_participante_id
                LEFT JOIN " . $db->getTable('tbl_partidos_politicos') . " pp
                    ON pxp.tbl_partido_politico_id = pp.id
                LEFT JOIN " . $db->getTable('tbl_cargos_publicos') . " cp
                    ON p.tbl_cargo_publico_id = cp.id
                LEFT JOIN " . $db->getTable('tbl_departamentos') . " d
                    ON p.codigo_departamento = d.codigo_departamento
                LEFT JOIN " . $db->getTable('tbl_ciudades') . " c
                    ON p.codigo_municipio = c.codigo_muncipio
                WHERE sxp.tbl_sondeo_id = :id
                GROUP BY p.id";
                $params[':id'] = $value['id'];
                $stmtSondeoCandidato = $pdo->prepare($qSondeoCandidato);
                $stmtSondeoCandidato->execute($params);
                $arrSondeoCandidato = $stmtSondeoCandidato->fetchAll(PDO::FETCH_ASSOC);

foreach ($arrSondeoCandidato as &$cand) {

    if (!empty($cand['foto'])) {
        // FOTO NORMAL (archivo)
        $cand['foto_url'] = 'uploads/fotos/' . $cand['foto'];
    } else {
        // FOTO POR DEFECTO
        $cand['foto_url'] = 'img/user_default.png';
    }
}
                $arr[$key]['candidatos'] = $arrSondeoCandidato;

                // Opciones del sondeo
                $qSondeOpciones = "SELECT opcion FROM " . $db->getTable('tbl_sondeo_x_opciones') . " WHERE tbl_sondeo_id = :id";
                $params[':id'] = $value['id'];
                $stmtSondeOpciones = $pdo->prepare($qSondeOpciones);
                $stmtSondeOpciones->execute($params);
                $arrSondeOpciones = $stmtSondeOpciones->fetchAll(PDO::FETCH_ASSOC);
                $arr[$key]['opciones'] = $arrSondeOpciones;
            }

            $arrjson = array('output' => array('valid' => true, 'response' => $arr ? $arr : []));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Al obtener los datos de Sondeo.');
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

    /**
     * Metodo para registrar un voto para un sondeo, verificando duplicados.
     * @param array $rqst Contiene sondeo_id, pregunta_id, tipo y valor.
     * @return array
     */
   public static function registrarVoto($rqst)
{
    $db = new DbConection();
    $pdo = $db->openConect();

    $votante_id = SessionData::getUserId();
    if ($votante_id <= 0) {
        return [
            'status' => 'error',
            'message' => 'No se encontró ID de votante en sesión.',
            'output' => ['valid' => false]
        ];
    }

    // Departamento y municipio del usuario votante
    $dep = SessionData::getCodigoDepartamentoSessionVotante();
    $mun = SessionData::getCodigoMunicipioSessionVotante();

    $sondeo_id   = isset($rqst['sondeo_id']) ? intval($rqst['sondeo_id']) : 0;
    $tipo        = isset($rqst['tipo']) ? trim($rqst['tipo']) : '';
    $valor       = isset($rqst['valor']) ? $rqst['valor'] : null;

    if ($sondeo_id <= 0 || is_null($valor)) {
        return [
            'status' => 'error',
            'message' => 'Datos incompletos.',
            'output' => ['valid' => false]
        ];
    }

    try {
        $tableName = $db->getTable('tbl_respuestas_sondeos');

        // VALIDAR VOTO ÚNICO
        $qCheck = "SELECT id FROM $tableName 
                   WHERE tbl_votante_id = ? AND tbl_sondeo_id = ?";
        $stmtCheck = $pdo->prepare($qCheck);
        $stmtCheck->execute([$votante_id, $sondeo_id]);

        if ($stmtCheck->rowCount() > 0) {
            return [
                'status' => 'warning',
                'message' => 'Ya has votado en este sondeo.',
                'output' => ['valid' => true]
            ];
        }

        // Definir valores según el tipo
        $candidato_id_insert = null;
        $respuesta_texto_insert = null;

        if ($tipo === 'candidatos') {
            $candidato_id_insert = intval($valor);
        } else {
            $respuesta_texto_insert = trim($valor);
        }

        // INSERTAR VOTO + DEP + MUN
        $qInsert = "INSERT INTO $tableName 
            (tbl_sondeo_id, tbl_sondeo_x_opciones_id, tbl_votante_id,
             codigo_departamento, codigo_municipio,
             tbl_candidato_id, tbl_respuesta_texto, dtcreate)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtInsert = $pdo->prepare($qInsert);
        $ok = $stmtInsert->execute([
            $sondeo_id,
            $valor,
            $votante_id,
            $dep,
            $mun,
            $candidato_id_insert,
            $respuesta_texto_insert,
            Util::date()
        ]);

        return $ok
            ? ['status' => 'success', 'message' => '¡Voto registrado!', 'output' => ['valid' => true]]
            : ['status' => 'error', 'message' => 'Error al guardar el voto.', 'output' => ['valid' => false]];

    } catch (PDOException $e) {
        return [
            'status' => 'error',
            'message' => 'DB Error: ' . $e->getMessage(),
            'output' => ['valid' => false]
        ];
    }
}


    /**
     * Obtiene los IDs de sondeos que ya fueron votados por un usuario
     * @param int $votanteId ID del votante
     * @return array Array con los IDs de sondeos votados
     */
    public static function getSondeosVotadosPorUsuario($votanteId)
    {
        if ($votanteId <= 0) {
            return [];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT DISTINCT tbl_sondeo_id
                FROM " . $db->getTable('tbl_respuestas_sondeos') . "
                WHERE tbl_votante_id = :votante_id";

            $stmt = $pdo->prepare($q);
            $stmt->execute([':votante_id' => $votanteId]);
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $db->closeConect();
            return $result ?: [];

        } catch (PDOException $e) {
            $db->closeConect();
            return [];
        }
    }


    /**
     * Método para obtener sondeos filtrados por ubicación del usuario
     * @param array $rqst
     * @param string $usuarioDepartamento Código del departamento del usuario
     * @param string $usuarioMunicipio Código del municipio del usuario
     * @return array
     */
    public static function getSondeosFiltrados($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $usuarioMunicipio = SessionData::getCodigoMunicipioSessionVotante();
        $usuarioDepartamento = SessionData::getCodigoDepartamentoSessionVotante();

        $db = new DbConection();
        $pdo = $db->openConect();
        
        // Construir consulta base con filtros de ubicación
        $q = "SELECT s.*,
                    cp.nombre as nombre_cargo_publico,
                    d.departamento as nombre_departamento,
                    c.municipio as nombre_municipio
            FROM " . $db->getTable('tbl_sondeo') . " s
            LEFT JOIN " . $db->getTable('tbl_cargos_publicos') . " cp
                ON s.tbl_cargo_publico_id = cp.id
            LEFT JOIN " . $db->getTable('tbl_departamentos') . " d
                ON s.codigo_departamento = d.codigo_departamento
            LEFT JOIN " . $db->getTable('tbl_ciudades') . " c
                ON s.codigo_municipio = c.codigo_muncipio
            WHERE s.habilitado = 'si'";

        $params = [];

        if ($id > 0) {
            $q .= " AND s.id = :id";
            $params[':id'] = $id;
        } else {
            // Aplicar filtros de ubicación solo si no es por ID específico
            $q .= " AND (
                s.aplica_cargos_publicos = 'no'
                OR
                -- Cargos nacionales (Presidente o Senador) no validan ubicación
                (s.aplica_cargos_publicos = 'si' AND s.tbl_cargo_publico_id IN (1, 2))
                OR
                (s.aplica_cargos_publicos = 'si' AND (
                    -- Sondeos nacionales (sin departamento específico)
                    (s.codigo_departamento IS NULL OR s.codigo_departamento = '')
                    OR
                    -- Sondeos departamentales (coincide departamento)
                    (s.codigo_departamento = :departamento AND (s.codigo_municipio IS NULL OR s.codigo_municipio = ''))
                    OR
                    -- Sondeos municipales (coincide municipio)
                    (s.codigo_departamento = :departamento AND s.codigo_municipio = :municipio)
                ))
            )";

            $params[':departamento'] = $usuarioDepartamento;
            $params[':municipio'] = $usuarioMunicipio;
        }
        
        $q .= " ORDER BY s.dtcreate DESC";

        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Filtrar sondeos vigentes usando el método isVigente
            $arr = array_filter($arr, function($sondeo) {
                return self::isVigente($sondeo['fecha_inicio'], $sondeo['fecha_fin']);
            });

            // Reindexar el array después del filtro
            $arr = array_values($arr);

            // Informacion de los candidatos a postular
            foreach ($arr as $key => $value) {
                $qSondeoCandidato = "SELECT 
                    p.id, p.tbl_cargo_publico_id, p.nombre_completo, p.codigo_departamento, p.codigo_municipio, p.dtcreate, p.foto, p.habilitado,
                    cp.nombre AS cargo_publico, cp.sigla AS sigla_cargo,
                    d.departamento AS nombre_departamento,
                    c.municipio AS nombre_municipio,
                    p.habilitado,
                    GROUP_CONCAT(pxp.tbl_partido_politico_id) AS partidoPoliticoIds,
                    GROUP_CONCAT(pp.nombre_partido SEPARATOR ', ') AS nombres_partidos
                FROM " . $db->getTable('tbl_participantes') . " p
                INNER JOIN " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " sxp
                    ON p.id = sxp.tbl_participante_id
                LEFT JOIN " . $db->getTable('tbl_participantes_x_partidos_politicos') . " pxp
                    ON p.id = pxp.tbl_participante_id
                LEFT JOIN " . $db->getTable('tbl_partidos_politicos') . " pp
                    ON pxp.tbl_partido_politico_id = pp.id
                LEFT JOIN " . $db->getTable('tbl_cargos_publicos') . " cp
                    ON p.tbl_cargo_publico_id = cp.id
                LEFT JOIN " . $db->getTable('tbl_departamentos') . " d
                    ON p.codigo_departamento = d.codigo_departamento
                LEFT JOIN " . $db->getTable('tbl_ciudades') . " c
                    ON p.codigo_municipio = c.codigo_muncipio
                WHERE sxp.tbl_sondeo_id = :id
                GROUP BY p.id";
                
                $paramsCandidato = [':id' => $value['id']];
                $stmtSondeoCandidato = $pdo->prepare($qSondeoCandidato);
                $stmtSondeoCandidato->execute($paramsCandidato);
                $arrSondeoCandidato = $stmtSondeoCandidato->fetchAll(PDO::FETCH_ASSOC);

foreach ($arrSondeoCandidato as &$cand) {

    if (!empty($cand['foto'])) {
        // FOTO NORMAL (archivo)
        $cand['foto_url'] = 'uploads/fotos/' . $cand['foto'];
    } else {
        // FOTO POR DEFECTO
        $cand['foto_url'] = 'img/user_default.png';
    }
}
                $arr[$key]['candidatos'] = $arrSondeoCandidato;

                // Opciones del sondeo
$qSondeOpciones = "SELECT id, opcion 
                   FROM " . $db->getTable('tbl_sondeo_x_opciones') . " 
                   WHERE tbl_sondeo_id = :id
                   ORDER BY id ASC";
                $stmtSondeOpciones = $pdo->prepare($qSondeOpciones);
                $stmtSondeOpciones->execute($paramsCandidato);
                $arrSondeOpciones = $stmtSondeOpciones->fetchAll(PDO::FETCH_ASSOC);
                $arr[$key]['opciones'] = $arrSondeOpciones;
            }

            $arrjson = array('output' => array('valid' => true, 'response' => $arr ? $arr : []));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Al obtener los datos de Sondeo filtrados: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
        return $arrjson;
    }

        /**
     * Verifica si un sondeo está vigente según sus fechas
     * @param string|null $fecha_inicio Fecha de inicio del sondeo
     * @param string|null $fecha_fin Fecha de fin del sondeo
     * @return bool True si está vigente, false si no
     */
    public static function isVigente($fecha_inicio, $fecha_fin)
    {
        // Si no hay fechas definidas, no se puede determinar vigencia
        if (empty($fecha_inicio) && empty($fecha_fin)) {
            return false;
        }

        $fecha_actual = date('Y-m-d');

        // Si solo hay fecha de inicio
        if (!empty($fecha_inicio) && empty($fecha_fin)) {
            return $fecha_actual >= $fecha_inicio;
        }

        // Si solo hay fecha fin
        if (empty($fecha_inicio) && !empty($fecha_fin)) {
            return $fecha_actual <= $fecha_fin;
        }

        // Si hay ambas fechas, verificar que esté en el rango
        return $fecha_actual >= $fecha_inicio && $fecha_actual <= $fecha_fin;
    }

        /**
     * Método para filtrar sondeos según lugar de usuario y clic
     * @param array $rqst
     * @return array
     */
    public static function filtrarPorLugar($rqst)
    {
        $depClick = isset($rqst['departamento_click']) ? trim($rqst['departamento_click']) : null;
        $depUser  = isset($rqst['departamento_usuario']) ? trim($rqst['departamento_usuario']) : null;

        $db = new DbConection();
        $pdo = $db->openConect();

        $tabla = $db->getTable('tbl_sondeo');

 

            $sql = "
                SELECT *
                FROM $tabla
                WHERE habilitado = 'si'

                ORDER BY dtcreate DESC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        
        
        return [
            "success" => true,
            "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

        /**
     * obtener respuesta de los sondeos
     */
public static function obtenerRespuestas($rqst)
{
    $id = intval($rqst['id_sondeo'] ?? 0);
    $depClick = $rqst['departamento_click'] ?? null;
    // Verificar sin_fotos de forma más robusta
    $sinFotosRaw = $rqst['sin_fotos'] ?? '';
    $sinFotos = !empty($sinFotosRaw) && ($sinFotosRaw === 'true' || $sinFotosRaw === '1' || $sinFotosRaw === true);

    if ($id <= 0) {
        return ["success" => false, "message" => "ID inválido"];
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    $qSondeo = "
        SELECT id, sondeo, descripcion_sondeo, tipo_sondeo, codigo_departamento, codigo_municipio
        FROM " . $db->getTable('tbl_sondeo') . "
        WHERE id = :id
    ";
    $stmt = $pdo->prepare($qSondeo);
    $stmt->execute([":id" => $id]);
    $sondeo = $stmt->fetch(PDO::FETCH_ASSOC);

    $qOpc = "
        SELECT id, opcion
        FROM " . $db->getTable('tbl_sondeo_x_opciones') . "
        WHERE tbl_sondeo_id = :id
        ORDER BY id ASC
    ";
    $stmt = $pdo->prepare($qOpc);
    $stmt->execute([":id" => $id]);
    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $qRespOpc = "
        SELECT 
            o.id AS opcion_id,
            o.opcion,
            COUNT(r.id) AS total
        FROM " . $db->getTable('tbl_sondeo_x_opciones') . " o
        LEFT JOIN " . $db->getTable('tbl_respuestas_sondeos') . " r
            ON r.tbl_sondeo_x_opciones_id = o.id
        WHERE o.tbl_sondeo_id = :id
    ";

    $paramsResp = [":id" => $id];

    if (!empty($depClick)) {
        $qRespOpc .= " AND r.codigo_departamento = :dep ";
        $paramsResp[":dep"] = $depClick;
    }

    $qRespOpc .= "
        GROUP BY o.id, o.opcion
        ORDER BY total DESC
    ";

    $stmt = $pdo->prepare($qRespOpc);
    $stmt->execute($paramsResp);
    $respuestasOpciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Construir query de candidatos - solo incluir foto_blob si se necesita
    $fotoBlobSelect = $sinFotos ? "" : ", p.foto_blob";
    $qCand = "
        SELECT
            p.id,
            p.nombre_completo,
            p.foto
            $fotoBlobSelect
        FROM " . $db->getTable('tbl_participantes') . " p
        INNER JOIN " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " sp
            ON sp.tbl_participante_id = p.id
        WHERE sp.tbl_sondeo_id = :id
        ORDER BY p.nombre_completo ASC
    ";

    $stmt = $pdo->prepare($qCand);
    $stmt->execute([":id" => $id]);
    $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convertir foto_blob a base64 para los candidatos (solo si no es sin_fotos)
    foreach ($candidatos as &$cand) {
        if (!$sinFotos && !empty($cand['foto_blob'])) {
            $cand['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($cand['foto_blob']);
            unset($cand['foto_blob']);
        } else {
            $cand['foto_base64'] = null;
        }
    }

    // Construir query de respuestas por candidatos - solo incluir foto_blob si se necesita
    $fotoBlobSelectResp = $sinFotos ? "" : "p.foto_blob,";
    $qRespCand = "
        SELECT
            p.id AS candidato_id,
            p.nombre_completo,
            p.foto,
            $fotoBlobSelectResp
            COUNT(r.id) AS total
        FROM " . $db->getTable('tbl_participantes') . " p
        INNER JOIN " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " sp
            ON sp.tbl_participante_id = p.id
        LEFT JOIN " . $db->getTable('tbl_respuestas_sondeos') . " r
            ON r.tbl_candidato_id = p.id
        WHERE sp.tbl_sondeo_id = :id
    ";

    $paramsCand = [":id" => $id];

    if (!empty($depClick)) {
        $qRespCand .= " AND r.codigo_departamento = :dep ";
        $paramsCand[":dep"] = $depClick;
    }

    $qRespCand .= "
        GROUP BY p.id, p.nombre_completo, p.foto
        ORDER BY total DESC
    ";

    $stmt = $pdo->prepare($qRespCand);
    $stmt->execute($paramsCand);
    $respuestasCandidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convertir foto_blob a base64 para las respuestas de candidatos (solo si no es sin_fotos)
    foreach ($respuestasCandidatos as &$respCand) {
        if (!$sinFotos && !empty($respCand['foto_blob'])) {
            $respCand['foto_base64'] = 'data:image/jpeg;base64,' . base64_encode($respCand['foto_blob']);
            unset($respCand['foto_blob']);
        } else {
            $respCand['foto_base64'] = null;
        }
    }

    return [
        "success" => true,
        "sondeo" => $sondeo,
        "opciones" => $opciones,
        "respuestas_opciones" => $respuestasOpciones,
        "candidatos" => $candidatos,
        "respuestas_candidatos" => $respuestasCandidatos
    ];
}
public static function obtenerSondeoMapa($rqst)
{
    $depClick = $rqst['departamento_click'] ?? null;

    $db = new DbConection();
    $pdo = $db->openConect();

    // Obtener el primer sondeo habilitado
    $qBuscarSondeo = "
        SELECT id, sondeo, descripcion_sondeo
        FROM " . $db->getTable('tbl_sondeo') . "
        WHERE habilitado = 'si'
        ORDER BY dtcreate DESC
        LIMIT 1
    ";
    $stmt = $pdo->prepare($qBuscarSondeo);
    $stmt->execute();
    $sondeo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sondeo) {
        return ["success" => false, "message" => "No hay sondeos habilitados"];
    }

    $idSondeo = $sondeo['id'];

    // Verificar si el sondeo tiene candidatos (participantes)
    $qCheckCandidatos = "SELECT COUNT(*) as total FROM " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " WHERE tbl_sondeo_id = :id";
    $stmt = $pdo->prepare($qCheckCandidatos);
    $stmt->execute([":id" => $idSondeo]);
    $tieneCandidatos = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

    $params = [":id" => $idSondeo];
    if (!empty($depClick)) {
        $params[":dep"] = $depClick;
    }

    if ($tieneCandidatos) {
        // Sondeo con candidatos (participantes)
        $qVotos = "
            SELECT
                p.id AS candidato_id,
                p.nombre_completo,
                p.foto,
                CONCAT('uploads/fotos/', p.foto) AS foto_url,
                COUNT(r.id) AS total
            FROM " . $db->getTable('tbl_participantes') . " p
            INNER JOIN " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " sp
                ON sp.tbl_participante_id = p.id
            LEFT JOIN " . $db->getTable('tbl_respuestas_sondeos') . " r
                ON r.tbl_candidato_id = p.id
                AND r.tbl_sondeo_id = :id
                " . (!empty($depClick) ? " AND r.codigo_departamento = :dep " : "") . "
            WHERE sp.tbl_sondeo_id = :id
            GROUP BY p.id, p.nombre_completo, p.foto
            ORDER BY total DESC
        ";
    } else {
        // Sondeo con solo opciones (sin candidatos)
        $qVotos = "
            SELECT
                o.id AS candidato_id,
                o.opcion AS nombre_completo,
                '' AS foto,
                'img/option_default.png' AS foto_url,
                COUNT(r.id) AS total
            FROM " . $db->getTable('tbl_sondeo_x_opciones') . " o
            LEFT JOIN " . $db->getTable('tbl_respuestas_sondeos') . " r
                ON r.tbl_sondeo_x_opciones_id = o.id
                AND r.tbl_sondeo_id = :id
                " . (!empty($depClick) ? " AND r.codigo_departamento = :dep " : "") . "
            WHERE o.tbl_sondeo_id = :id
            GROUP BY o.id, o.opcion
            ORDER BY total DESC
        ";
    }

    $stmt = $pdo->prepare($qVotos);
    $stmt->execute($params);
    $votos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "success" => true,
        "sondeo" => $sondeo,
        "votos" => $votos
    ];
}

public static function obtenerSondeoGeneral($rqst)
{
    $db = new DbConection();
    $pdo = $db->openConect();

    // Obtener el primer sondeo habilitado y vigente
    $qBuscarSondeo = "
        SELECT id, sondeo, descripcion_sondeo, fecha_inicio, fecha_fin
        FROM " . $db->getTable('tbl_sondeo') . "
        WHERE habilitado = 'si'
        ORDER BY dtcreate DESC
        LIMIT 1
    ";
    $stmt = $pdo->prepare($qBuscarSondeo);
    $stmt->execute();
    $sondeo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sondeo) {
        return ["success" => false, "message" => "No hay sondeos habilitados"];
    }

    $idSondeo = $sondeo['id'];

    // Verificar si el sondeo tiene candidatos (participantes)
    $qCheckCandidatos = "SELECT COUNT(*) as total FROM " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " WHERE tbl_sondeo_id = :id";
    $stmt = $pdo->prepare($qCheckCandidatos);
    $stmt->execute([":id" => $idSondeo]);
    $tieneCandidatos = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

    if ($tieneCandidatos) {
        // Sondeo con candidatos (participantes)
        $qVotos = "
            SELECT
                p.id AS candidato_id,
                p.nombre_completo,
                p.foto,
                CONCAT('uploads/fotos/', p.foto) AS foto_url,
                COUNT(r.id) AS total
            FROM " . $db->getTable('tbl_participantes') . " p
            INNER JOIN " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " sp
                ON sp.tbl_participante_id = p.id
            LEFT JOIN " . $db->getTable('tbl_respuestas_sondeos') . " r
                ON r.tbl_candidato_id = p.id
                AND r.tbl_sondeo_id = :id
            WHERE sp.tbl_sondeo_id = :id
            GROUP BY p.id, p.nombre_completo, p.foto
            ORDER BY total DESC
        ";
        $stmt = $pdo->prepare($qVotos);
        $stmt->execute([":id" => $idSondeo]);
        $votos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Sondeo con solo opciones (sin candidatos)
        $qVotos = "
            SELECT
                o.id AS candidato_id,
                o.opcion AS nombre_completo,
                '' AS foto,
                'img/option_default.png' AS foto_url,
                COUNT(r.id) AS total
            FROM " . $db->getTable('tbl_sondeo_x_opciones') . " o
            LEFT JOIN " . $db->getTable('tbl_respuestas_sondeos') . " r
                ON r.tbl_sondeo_x_opciones_id = o.id
                AND r.tbl_sondeo_id = :id
            WHERE o.tbl_sondeo_id = :id
            GROUP BY o.id, o.opcion
            ORDER BY total DESC
        ";
        $stmt = $pdo->prepare($qVotos);
        $stmt->execute([":id" => $idSondeo]);
        $votos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return [
        "success" => true,
        "sondeo" => $sondeo,
        "votos" => $votos
    ];
}

public static function ganadorPorDepartamento($codigoDep)
{
    $db = new DbConection();
    $pdo = $db->openConect();

    // Obtener el primer sondeo habilitado
    $qSondeo = "SELECT id FROM " . $db->getTable('tbl_sondeo') . " WHERE habilitado = 'si' ORDER BY dtcreate DESC LIMIT 1";
    $stmt = $pdo->prepare($qSondeo);
    $stmt->execute();
    $sondeo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sondeo) return null;
    $idSondeo = $sondeo['id'];

    // Verificar si el sondeo tiene candidatos
    $qCheckCandidatos = "SELECT COUNT(*) as total FROM " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " WHERE tbl_sondeo_id = :id";
    $stmt = $pdo->prepare($qCheckCandidatos);
    $stmt->execute([":id" => $idSondeo]);
    $tieneCandidatos = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

    if ($tieneCandidatos) {
        // Sondeo con candidatos
        $q = "
            SELECT
                r.codigo_departamento,
                r.tbl_candidato_id AS ganador_id,
                COUNT(*) as total
            FROM " . $db->getTable('tbl_respuestas_sondeos') . " r
            WHERE r.tbl_sondeo_id = :id
            AND r.codigo_departamento = :dep
            AND r.tbl_candidato_id IS NOT NULL
            GROUP BY r.tbl_candidato_id
        ";
    } else {
        // Sondeo con solo opciones
        $q = "
            SELECT
                r.codigo_departamento,
                r.tbl_sondeo_x_opciones_id AS ganador_id,
                COUNT(*) as total
            FROM " . $db->getTable('tbl_respuestas_sondeos') . " r
            WHERE r.tbl_sondeo_id = :id
            AND r.codigo_departamento = :dep
            AND r.tbl_sondeo_x_opciones_id IS NOT NULL
            GROUP BY r.tbl_sondeo_x_opciones_id
        ";
    }

    $stmt = $pdo->prepare($q);
    $stmt->execute([":id" => $idSondeo, ":dep" => $codigoDep]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) return null;

    $max = max(array_column($rows, 'total'));

    // candidatos/opciones empatados
    $empatados = array_filter($rows, fn($r) => $r['total'] == $max);

    return [
        "empate" => count($empatados) > 1,
        "ganador" => count($empatados) > 1 ? null : $empatados[array_key_first($empatados)]['ganador_id']
    ];
}

/**
 * Método para obtener el ganador por cada departamento
 * @return array Array asociativo con código de departamento como clave y datos del ganador
 */
public static function ganadorPorTodosLosDepartamentos()
{
    $db = new DbConection();
    $pdo = $db->openConect();

    try {
        // Obtener el primer sondeo habilitado
        $qSondeo = "SELECT id FROM " . $db->getTable('tbl_sondeo') . " WHERE habilitado = 'si' ORDER BY dtcreate DESC LIMIT 1";
        $stmt = $pdo->prepare($qSondeo);
        $stmt->execute();
        $sondeo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sondeo) return [];
        $idSondeo = $sondeo['id'];

        // Verificar si el sondeo tiene candidatos
        $qCheckCandidatos = "SELECT COUNT(*) as total FROM " . $db->getTable('tbl_sondeo_x_tbl_participantes') . " WHERE tbl_sondeo_id = :id";
        $stmt = $pdo->prepare($qCheckCandidatos);
        $stmt->execute([":id" => $idSondeo]);
        $tieneCandidatos = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

        if ($tieneCandidatos) {
            // Sondeo con candidatos
            $sql = "
                SELECT
                    r.codigo_departamento,
                    r.tbl_candidato_id AS ganador_id,
                    COUNT(*) AS total
                FROM " . $db->getTable('tbl_respuestas_sondeos') . " r
                WHERE r.tbl_sondeo_id = :id
                AND r.tbl_candidato_id IS NOT NULL
                GROUP BY r.codigo_departamento, r.tbl_candidato_id
                ORDER BY r.codigo_departamento, total DESC
            ";
        } else {
            // Sondeo con solo opciones
            $sql = "
                SELECT
                    r.codigo_departamento,
                    r.tbl_sondeo_x_opciones_id AS ganador_id,
                    COUNT(*) AS total
                FROM " . $db->getTable('tbl_respuestas_sondeos') . " r
                WHERE r.tbl_sondeo_id = :id
                AND r.tbl_sondeo_x_opciones_id IS NOT NULL
                GROUP BY r.codigo_departamento, r.tbl_sondeo_x_opciones_id
                ORDER BY r.codigo_departamento, total DESC
            ";

            // DEBUG: Log para ver qué pasa
            error_log("=== DEBUG ganadorPorTodosLosDepartamentos ===");
            error_log("Sondeo ID: " . $idSondeo);
            error_log("SQL: " . $sql);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $idSondeo]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // DEBUG: Log de resultados
        error_log("Rows encontradas: " . count($rows));
        error_log("Rows data: " . json_encode($rows));

        $resultado = [];

        foreach ($rows as $row) {

            $dep = $row['codigo_departamento'];

            if (!isset($resultado[$dep])) {

                $resultado[$dep] = [
                    "empate" => false,
                    "ganador" => $row['ganador_id'],
                    "max_votos" => $row['total']
                ];

            } else {

                if ($row['total'] == $resultado[$dep]['max_votos']) {
                    $resultado[$dep]['empate'] = true;
                    $resultado[$dep]['ganador'] = null;
                }
            }
        }

        return $resultado;

    } catch (PDOException $e) {
        return [];
    } finally {
        $db->closeConect();
    }
}

}