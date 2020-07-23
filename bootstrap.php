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

  $router->addMiddleware(function(&$parameters) {
    // Load bearer token 
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    // HEADER: Get the access token from the header
    $token = null;
    if (!empty($headers)) {
        if (preg_match('/Bearer\s((.*)\.(.*)\.(.*))/', $headers, $matches)) {
            $token = $matches[1];
        }
    }

    $payload = null;
    if($token != null)
      $payload = JWT::validate($token);
    

    if($payload != null){
      $parameters['user'] = Service::getService('Auth')->getUser($payload['userid']);
    }
    else {
      $parameters['user'] = null;
    }
    
  });

  $router->addNamspace('api/:controller', 'Routers\ApiRouter');

  $router->run($url);
?>