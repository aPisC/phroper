<?php
define('ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

require_once("phapi/Phapi.php");

$engine = Phapi::instance();

$engine->serveApi("api/");
$engine->serveFolder(ROOT . DS . "public");
$engine->serveFallbackFile(ROOT . DS . "public" . DS . "index.html");

$engine->run();
