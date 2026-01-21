<?php
// Obteniendo la fecha actual con hora, minutos y segundos en PHP
$fechaActual = date('d-m-Y H:i:s');
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Main
{
  public function __construct() {}

  /**
   * Metodo para recuperar todos los registros
   * @return array 
   */
  public static function getDataMain($rqst)
  {

    $departamentoCodigo = isset($rqst['codigoDepartamento']) ? ($rqst['codigoDepartamento']) : Util::getDepartamentoPrincipal();

    $db = new DbConection();
    $pdo = $db->openConect();

    // Inicialización de variables
    $lideres = 0;
    $visitas = 0;
    $municipios = 0;
    $veredas = 0;
    $inversionsec = 0;
    $valorproyectos = 0;

   // Informacion del departamento
    $q0 = "SELECT departamento 
      FROM " . $db->getTable('tbl_departamentos') . " 
      WHERE codigo_departamento = :codigo";

    $stmt = $pdo->prepare($q0);
    $stmt->execute([':codigo' => $departamentoCodigo]);
    $nombreDepartamento = $stmt->fetchColumn();


    // Consulta 7: Total de instcritos
    $q7 = "SELECT COUNT(id) AS cuenta_inscritos FROM " . $db->getTable('tbl_lideres') . " WHERE tipo='Colaborador' AND tbl_departamento_id = $departamentoCodigo";
    $inscritos = $pdo->query($q7)->fetchColumn();

    // Consulta 8: Total de reservistas
    $q8 = "SELECT COUNT(id) AS cuenta_reserva FROM " . $db->getTable('tbl_lideres') . " WHERE tipo ='Reserva' AND tbl_departamento_id = $departamentoCodigo";
    $reserva = $pdo->query($q8)->fetchColumn();

    // Consulta 9: Total de civiles
    $q9 = "SELECT COUNT(id) AS cuenta_civiles FROM " . $db->getTable('tbl_lideres') . " WHERE tipo ='Civil' AND tbl_departamento_id = $departamentoCodigo";
    $civiles = $pdo->query($q9)->fetchColumn();

    // Consulta 10: Total de candidatos
    $q10 = "SELECT COUNT(id) AS candidatos FROM " . $db->getTable('candidatos'). " WHERE tbl_departamento_id = $departamentoCodigo";
    $candidatos = $pdo->query($q10)->fetchColumn();

    // Cálculo de porcentajes
    $porcentaje_veredas = ($veredas * 100 / 34792);
    $porcentaje_municipios = ($municipios * 100 / 1103);

    $arrjson = array(
      'output' => array(
        'valid' => true,
        'lideres' => $lideres,
        'inscritos' => $inscritos,
        'visitas' => $visitas,
        'reserva' => $reserva,
        'civiles' => $civiles,
        'candidatos' => $candidatos,
        'inversionsec' => $inversionsec,
        'valorproyectos' => $valorproyectos,
        'municipios' => $municipios,
        'veredas' => $veredas,
        'porcentaje_veredas' => $porcentaje_veredas,
        'porcentaje_municipios' => $porcentaje_municipios,
        'departamento'=> $nombreDepartamento,
      )
    );

    // Cerrar la conexión
    $db->closeConect();

    return $arrjson;
  }

  /**
   * Metodo que obtiene el total de visitas por mes del departamento
   */
  public static function getTotalVisitasPorMesAMunicipios($rqst)
  {
    $departamentoCodigo = Util::getDepartamentoPrincipal();
    $years = [2024, 2025]; // Años deseados

    $db = new DbConection();
    $pdo = $db->openConect();

    // Inicializar estructura base con todos los meses en 0 para cada año
    $dataByYear = [];
    $monthsInSpanish = [
      "January" => "Enero",
      "February" => "Febrero",
      "March" => "Marzo",
      "April" => "Abril",
      "May" => "Mayo",
      "June" => "Junio",
      "July" => "Julio",
      "August" => "Agosto",
      "September" => "Septiembre",
      "October" => "Octubre",
      "November" => "Noviembre",
      "December" => "Diciembre"
    ];

    // Inicializar base de datos para cada mes y año
    foreach ($years as $year) {
      $dataByYear[$year] = [];
      foreach ($monthsInSpanish as $monthEnglish => $monthSpanish) {
        $dataByYear[$year][$monthSpanish] = [
          'mes' => $monthSpanish,
          'total_visitas' => 0
        ];
      }
    }

    // Consultar las visitas por mes y año
    $q = "SELECT 
            DATE_FORMAT(v.date, '%M') AS mes, 
            YEAR(v.date) AS anio, 
            COUNT(v.id) AS total_visitas
          FROM " . $db->getTable('tbl_visitas') . " v
          INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c 
          ON c.codigo_muncipio = v.tbl_municipio_id
          WHERE c.codigo_departamento = :departamentoCodigo 
            AND YEAR(v.date) IN (" . implode(',', $years) . ")
          GROUP BY YEAR(v.date), MONTH(v.date)
          ORDER BY YEAR(v.date), MONTH(v.date) ASC";



    $stmt = $pdo->prepare($q);
    $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
    $stmt->execute();
    $arrTotalVisitasPorMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Actualizar los valores reales en la estructura base
    foreach ($arrTotalVisitasPorMes as $row) {
      $anio = $row['anio'];
      $mesInEnglish = $row['mes'];
      $totalVisitas = (int)$row['total_visitas'];

      // Convertir mes de inglés a español
      $mesEnEspañol = isset($monthsInSpanish[$mesInEnglish]) ? $monthsInSpanish[$mesInEnglish] : $mesInEnglish;

      // Actualizar las visitas para el mes correspondiente
      if (isset($dataByYear[$anio][$mesEnEspañol])) {
        $dataByYear[$anio][$mesEnEspañol]['total_visitas'] = $totalVisitas;
      }
    }

    // Formatear los datos finales para la respuesta
    $response = [];
    foreach ($years as $year) {
      $response[$year] = array_values($dataByYear[$year]); // Convertir a array indexado
    }

    $arrjson = array(
      'output' => array(
        'valid' => true,
        'response' => $response,
      )
    );

    $db->closeConect();
    return $arrjson;
  }


  /**
   * Metodo para sacar el total de visitas por provincia
   */
  public static function getTotalVisitasPorProvincias($rqst)
  {
    $departamentoCodigo = Util::getDepartamentoPrincipal();
    $year = Util::getAnioActual();  // Año actual

    $db = new DbConection();
    $pdo = $db->openConect();

    // Consultas número de visitas por mes del departamento
    $q = "SELECT 
      DATE_FORMAT(v.date, '%M') AS mes, 
      v.provincia, 
      COUNT(v.id) AS total_visitas
      FROM " . $db->getTable('tbl_visitas') . " v
      WHERE v.tbl_departamento_id = :departamentoCodigo 
          AND YEAR(v.date) = :year
      GROUP BY v.provincia
      ORDER BY v.provincia";

    $stmt = $pdo->prepare($q);
    $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    $arrTotalVisitasProvincia = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $arrjson = array(
      'output' => array(
        'valid' => true,
        'response' => $arrTotalVisitasProvincia,
      )
    );

    $db->closeConect();
    return $arrjson;
  }

  /**
   * Metodo para mostrar por años total de visitas por provicias
   * 2024 2025 Actualmente
   */
  public static function getTotalVisitasPorProvinciasPorAnios($rqst)
  {
    $departamentoCodigo = Util::getDepartamentoPrincipal();
    $db = new DbConection();
    $pdo = $db->openConect();

    // Obtener lista de todas las provincias en el departamento
    $qProvincias = "SELECT DISTINCT provincia 
                    FROM " . $db->getTable('tbl_visitas') . " 
                    WHERE tbl_departamento_id = :departamentoCodigo";
    $stmtProvincias = $pdo->prepare($qProvincias);
    $stmtProvincias->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
    $stmtProvincias->execute();
    $provincias = $stmtProvincias->fetchAll(PDO::FETCH_COLUMN);

    // Inicializar estructura base con todas las provincias y años en 0
    $years = [2024, 2025]; // Años deseados
    $dataByYear = [];
    foreach ($years as $year) {
      $dataByYear[$year] = [];
      foreach ($provincias as $provincia) {
        $dataByYear[$year][$provincia] = [
          'provincia' => $provincia,
          'total_visitas' => 0
        ];
      }
    }

    // Consultar las visitas por provincia y año
    $q = "SELECT 
            v.provincia, 
            YEAR(v.date) AS anio,
            COUNT(v.id) AS total_visitas
          FROM " . $db->getTable('tbl_visitas') . " v
          WHERE v.tbl_departamento_id = :departamentoCodigo 
            AND v.provincia IS NOT NULL
            AND YEAR(v.date) IN (" . implode(',', $years) . ")
          GROUP BY v.provincia, YEAR(v.date)
          ORDER BY v.provincia, YEAR(v.date)";

    $stmt = $pdo->prepare($q);
    $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
    $stmt->execute();
    $arrTotalVisitasProvincia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Actualizar los valores reales en la estructura base
    foreach ($arrTotalVisitasProvincia as $row) {
      $provincia = $row['provincia'];
      $anio = $row['anio'];
      if (isset($dataByYear[$anio][$provincia])) { // Validar que la provincia existe en la estructura base
        $dataByYear[$anio][$provincia]['total_visitas'] = (int)$row['total_visitas'];
      }
    }

    // Formatear los datos finales para la respuesta
    $response = [];
    foreach ($years as $year) {
      $response[$year] = array_values($dataByYear[$year]); // Convertir a array indexado
    }

    $arrjson = array(
      'output' => array(
        'valid' => true,
        'response' => $response
      )
    );

    $db->closeConect();
    return $arrjson;
  }
  public static function getPromedioPs2025PorSecretaria($rqst)
  {
      $departamentoCodigo = Util::getDepartamentoPrincipal();
      $db = new DbConection();
      $pdo = $db->openConect();
  
      // Obtener lista de todas las secretarías
      $qSecretarias = "SELECT DISTINCT s.secretaria 
                       FROM " . $db->getTable('tbl_secretarias') . " s
                       JOIN " . $db->getTable('tbl_plandesarrollo') . " p 
                       ON p.tbl_secretaria_id = s.id";
      $stmtSecretarias = $pdo->prepare($qSecretarias);
      $stmtSecretarias->execute();
      $secretarias = $stmtSecretarias->fetchAll(PDO::FETCH_COLUMN);
  
      // Inicializar estructura base con todas las secretarías y valores en 0
      $dataBySecretaria = [];
      foreach ($secretarias as $secretaria) {
          $dataBySecretaria[$secretaria] = [
              'nombre_secretaria' => $secretaria,
              'total_ps2025' => 0,
              'promedio_ps2025' => 0
          ];
      }
  
      // Consultar el total y el promedio de ps2025 por secretaría
      $q = "SELECT 
              s.secretaria AS nombre_secretaria,
              SUM(COALESCE(NULLIF(p.ps2025, ''), 0)) AS total_ps2025,
              ROUND(AVG(COALESCE(NULLIF(p.ps2025, ''), 0)), 2) AS promedio_ps2025
            FROM " . $db->getTable('tbl_plandesarrollo') . " p
            JOIN " . $db->getTable('tbl_secretarias') . " s 
            ON p.tbl_secretaria_id = s.id
            GROUP BY s.secretaria
            ORDER BY promedio_ps2025 DESC";
  
      $stmt = $pdo->prepare($q);
      $stmt->execute();
      $arrPromedioPs2025 = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
      // Actualizar los valores reales en la estructura base
      foreach ($arrPromedioPs2025 as $row) {
          $secretaria = $row['nombre_secretaria'];
          if (isset($dataBySecretaria[$secretaria])) { // Validar que la secretaría existe en la estructura base
              $dataBySecretaria[$secretaria]['total_ps2025'] = (int)$row['total_ps2025'];
              $dataBySecretaria[$secretaria]['promedio_ps2025'] = (float)$row['promedio_ps2025'];
          }
      }
  
      // Convertir la estructura a un array indexado
      $response = array_values($dataBySecretaria);
  
      $arrjson = array(
          'output' => array(
              'valid' => true,
              'response' => $response
          )
      );
  
      $db->closeConect();
      return $arrjson;
  }
}
