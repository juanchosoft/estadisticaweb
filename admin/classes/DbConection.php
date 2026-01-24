<?php
date_default_timezone_set('America/Bogota');

class DbConection {

  private $host, $user, $pass, $pdo, $dbName, $connection, $server_date;

  /**
  * Constructor que establece los datos de conexion a la base de datos
  */
  public function __construct() {
    /** Local */
    $this->host = "localhost"; 
    $this->user = "root";
    $this->pass = "";
    $this->dbName = "estadisticaok";
    //Este es el timestamp que se debe ingresar, de acuerdo a la hora deseada
    $this->server_date = 'DATE_ADD(NOW(),INTERVAL 1 HOUR)';
    $this->pdo = NULL;
    $this->host = "mysql:host=" . $this->host;
  }

  /**
  * Establece la connexion con la base de datos
  */
  public function openConect() {
    try {
      $this->pdo = new PDO($this->host, $this->user, $this->pass);
      $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
      $this->pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8'");

      return $this->pdo;
    } catch (PDOException $e) {
      throw new Exception("<p>Error: No puede conectarse con la base de datos.</p><p>" . $e->getMessage() . "</p>\n");
    }
  }

  /**
  * Cierra la conexion con la base de datos
  */
  public function closeConect() {
    $this->pdo = NULL;
  }

  public function getServerDate() {
    return $this->server_date;
  }

  public function getDbName() {
    return $this->dbName;
  }

  public function getTable($table) {
    return $this->dbName . "." . $table;
  }
}
