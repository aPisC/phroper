<?php
require_once('utils/functions.php');

// Register auto loaging function
spl_autoload_register(function ($name) {
  $name = str_replace('\\', DS, $name);

  // Load file directly
  if (file_exists($name . '.php')) require_once($name . '.php');
  else if (file_exists($name . DS . 'index.php')) require_once($name . DS . 'index.php');

  // Load core module
  else if (file_exists('phapi' . DS . $name . '.php')) require_once('phapi' . DS . $name . '.php');
  else if (file_exists('phapi' . DS . $name . DS . 'index.php')) require_once('phapi' . DS . $name . DS . 'index.php');
});

$parameters = array();
$parameters['method'] = $_SERVER['REQUEST_METHOD'];
$parameters['url'] = $url;

$router = new Router();
$router->addHandler(function ($p, $n) {
  return JWT::TokenParserMiddleware($p, $n);
});
$router->addNamspace('api/:controller', 'Routers\ApiRouter');

// Serve files from public
$router->addHandler(function ($p, $next) {
  $pf = realpath("public");
  $fn = realpath("public" . DS .  implode(DS, $p["url"]));

  if (is_dir($fn)) {
    if (file_exists($fn . DS . "index.php")) $fn .= DS . "index.php";
    if (file_exists($fn . DS . "index.html")) $fn .= DS . "index.html";
  }

  if ($pf && $fn && str_starts_with($fn, $pf) && file_exists($fn)) {
    if (str_ends_with($fn, ".php")) {
      include($fn);
    } else {
      header('Content-Type: ' . mime_content_type($fn));
      readfile($fn);
    }
  } else {
    $next();
  }
});

// Serve index.html if resource not found
if (Config::$serveFallbackAsIndex) {
  $router->addHandler(function ($p, $next) {
    $fn = "public" . DS . "index.html";
    if (file_exists($fn)) {
      header('Content-Type: ' . mime_content_type($fn));
      readfile($fn);
    } else $next();
  });
}

// 404 error
$router->addHandler(function ($p, $next) {
  http_response_code(404);
});

$router->run($parameters);
