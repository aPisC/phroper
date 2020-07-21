<?php 

  define('ROOT', dirname(__FILE__));
  define('DS', DIRECTORY_SEPARATOR);
  
  $url = isset($_GET['url']) ? explode('/', trim($_GET['url'], '/')) : [];

  require_once(ROOT . DS . 'bootstrap.php');
?>