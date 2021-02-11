<?php
namespace Routers;

use Controller;
use Router;

class ApiRouter extends Router{
  public function __construct($p = array()) {
    parent::__construct($p);
  }

  function run($parameters, $next = null) {
    $cn = $parameters['controller'];

    $controller = Controller::getController($cn);
    $controller->run($parameters, $next);
  }
}

?>