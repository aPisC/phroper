<?php
namespace Routers;

use Controller;
use Router;

class ApiRouter extends Router{
  public function __construct($p = array()) {
    parent::__construct($p);
  }

  function run($url, $parameters = array()) {
    $cn = $parameters['controller'];

    $controller = Controller::getController($cn);
    $controller->run($url, $parameters);
  }
}

?>