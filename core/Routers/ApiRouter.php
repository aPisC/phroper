<?php
namespace Routers;
use Router;

class ApiRouter extends Router{
  public function __construct($p = array()) {
    parent::__construct($p);
  }

  function run($url, $parameters = array()) {
    $cn = $parameters['controller'];
    $ccn = 'Controllers\\'. ucfirst($cn);
    $controller = new $ccn();

    $controller->router->run($url, $parameters);
  }
}

?>