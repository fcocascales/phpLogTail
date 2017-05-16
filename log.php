<?php
/*
  log.php — ProInf.net — 2009 — mar/jun-2011, may-2012

  Añade líneas de datos en archivos LOG
  Almacena las sentencias SQL en "log.sql"

*/

// INCLUDE ======================================

require_once('session.php');

define('LOG', true); // ¿Realizar el log?
define('RAW', true); // Modo POST en crudo

define('ROUTE_LOG', '/extranet/temp/');

// LOG ==========================================

class Log {

  public static function route($filename) {
    return $_SERVER["DOCUMENT_ROOT"].ROUTE_LOG.$filename;
  }

  public static function addSQL($sql) {
    if (LOG) {
      $user = Session::getUser();
      $ip = Session::getIP();
      $datetime = date('Y-m-d H:i:s');
      $sql = str_replace("\n", '\n', $sql);
      //self::appendTextToFile('log.sql', "-- $date — $ip — $user\n$sql;\n");
      self::appendTextToFile(Log::route('log.sql'), "$sql; -- $datetime — $ip — $user\n");
    }
  }

  public static function addQuery() {
    if (LOG) {
      if (RAW) {
        self::appendTextToFile(Log::route('query.log'), Log::getRawPost() . "\n");
      }
      else {
        $query = ($_POST)?$_POST:$_GET; // $_QUERY
        $list = array();
        foreach($query as $key=>$value) {
          $list[] = $key.'►'.$value;
        }
        self::appendTextToFile(Log::route('query.log'), '▷'. implode('│',$list) . "\n");
      }
    }
  }

  public static function addShell($command) { // From dirserver.php
    if (LOG) {
      $user = Session::getUser();
      $ip = Session::getIP();
      $datetime = date('Y-m-d H:i:s');
      self::appendTextToFile(Log::route('shell.log'), "$command # -- $datetime — $ip — $user\n");
    }
  }

  public static function addURL($message='') {
    if (LOG) {
      $ip = Session::getIP();
      $datetime = date('Y-m-d H:i:s');
      $url = $_SERVER['REQUEST_URI']; // Include GET params
      $post = self::getPostData(); //empty($_POST)? '': "POST:" . implode("&", $_POST);
      self::appendTextToFileIfNew(Log::route('url.log'), "datetime|ip|url|post\n");
      self::appendTextToFile(Log::route('url.log'), "$datetime|$ip|$url|$post|$message\n");
    }
  }

  private static function getPostData() {
    return file_get_contents("php://input");
  }

  public static function add($title, $text, $output='log.log') {
    if (LOG) {
      self::appendTextToFile(Log::route($output), $title.'━▶'.$text."\n");
    }
  }


  // Utilities ----------------------------------

  static function getGET() {
    $params = array();
    reset($_GET);
    foreach($_GET as $key=>$value) {
      $params[]= "$key=|$value|";
    }
    return implode(' ', $params);
  }

  static function getRawPost() {
    $putdata = fopen( "php://input", "rb"); // Antes conocido como $HTTP_RAW_POST_DATA
    $result = '';
    while(!feof($putdata))
      $result .= fread($putdata, 4096 );
    fclose($putdata);
    return $result;
  }

  static function appendTextToFileIfNew($file, $text) {
    if (!file_exists($file)) {
      self::appendTextToFile ($file, $text);
    }
  }

  static function appendTextToFile ($file, $text)
  {
    if ($f = fopen($file, "a"))  {
      fputs ($f, $text, strlen($text));
      fclose ($f);
      return true;
    }
    else {
      // echo "No se puede abrir el archivo $line";
      return false;
    }
  }

} // class Log

// TEST =========================================

if (isset($_GET['debug']) && $_GET['debug']=='log') {
  $route = Log::route('log.log');
  $exists = file_exists($route)? "Yes":"No";
  echo "<p>Log::route='$route' $exists</p>";
  Log::add("test", date('Y-m-d H:i:s'));
}
