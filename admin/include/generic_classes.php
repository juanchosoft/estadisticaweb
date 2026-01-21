<?php
error_reporting(E_ERROR | E_PARSE);

require 'admin/classes/Util.php';
require 'admin/classes/DbConection.php';
require 'admin/include/generic_validate_session.php';
require 'admin/classes/SessionData.php';


function base_url(){
    $port = $_SERVER["SERVER_PORT"];

    $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'].":".$port: $_SERVER['SERVER_NAME'];

    $url = sprintf(
      "%s://%s%s",
      isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
      $nameServer,
      $_SERVER['REQUEST_URI']
    );

    $url = str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php').".php", "", $url);

    return $url;
  }
