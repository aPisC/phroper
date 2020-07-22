<?php 
  require_once('utils/functions.php');

  // Register auto loaging function
  spl_autoload_register(function ($name) {
    $name = str_replace('\\', DS, $name);

    // Load file directly
    if(file_exists($name . '.php'))require_once(file_exists($name . '.php'));
    else if(file_exists($name . DS . 'index.php')) require_once($name . DS . 'index.php');

    // Load core module
    else if (file_exists('core' . DS . $name . '.php')) require_once('core' . DS . $name . '.php');
    else if (file_exists('core' . DS . $name . DS . 'index.php')) require_once('core' . DS . $name . DS . 'index.php');
  });
  

  $router = new Router();

  $router->addNamspace('api/:controller', 'Routers\ApiRouter');

  $router->run($url);
?>