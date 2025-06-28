<?php
  /*Se reporteaza toate erorile cu exceptia celor de tip NOTICE si DEPRECATED */
  error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
  //error_reporting(E_ALL);
  ini_set('display_errors', 'on');

  /** DIR_BASE va retine locatia pe disk unde este stocata aplicatia web */
  define('DIR_BASE', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

  /** Datele de conectare la baza de date */
  define('DB_HOST', 'localhost');
  define('DB_NAME', 'Db_Catalog');
  define('DB_USER', 'Admin');
  define('DB_PASS', 'admin');

  $conn = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);

  define('BASE_URL', '/SiteCatalog');
?>
