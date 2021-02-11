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
  
  $parameters = array();
  $parameters['method'] = $_SERVER['REQUEST_METHOD'];
  $parameters['url'] = $url;

  $router = new Router();
  $router->addHandler(function($p, $n){ return JWT::TokenParserMiddleware($p, $n); });
  $router->addNamspace('api/:controller', 'Routers\ApiRouter');
  $router->addHandler(function() {http_response_code(404);});

  $router->run($parameters);
  
?>