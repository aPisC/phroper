<?php

// Defines
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
define('PHROPER_VERSION', '1.1.0');

// Register auto loading function
spl_autoload_register(function ($name) {
    $name = str_replace('\\', DS, $name);

    // Load file directly
    if (file_exists($name . '.php')) require_once($name . '.php');
    else if (file_exists($name . DS . 'index.php')) require_once($name . DS . 'index.php');

    // Load core module
    else if (file_exists('phroper' . DS . $name . '.php')) require_once('phroper' . DS . $name . '.php');
    else if (file_exists('phroper' . DS . $name . DS . 'index.php')) require_once('phroper' . DS . $name . DS . 'index.php');
});

// Global imports
require_once(ROOT . DS . 'phroper' . DS . 'Utils' . DS . 'functions.php');
require_once(ROOT . DS . 'phroper' . DS . 'Phroper' . DS . 'index.php');
