<?php

// Defines
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
define('PHAPI_VERSION', '1.1.0');

// Register auto loading function
spl_autoload_register(function ($name) {
    $name = str_replace('\\', DS, $name);

    // Load file directly
    if (file_exists($name . '.php')) require_once($name . '.php');
    else if (file_exists($name . DS . 'index.php')) require_once($name . DS . 'index.php');

    // Load core module
    else if (file_exists('phapi' . DS . $name . '.php')) require_once('phapi' . DS . $name . '.php');
    else if (file_exists('phapi' . DS . $name . DS . 'index.php')) require_once('phapi' . DS . $name . DS . 'index.php');
});

// Global imports
require_once(ROOT . DS . 'phapi' . DS . 'Utils' . DS . 'functions.php');
require_once(ROOT . DS . 'phapi' . DS . 'Phapi' . DS . 'index.php');
