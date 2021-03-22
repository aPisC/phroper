<?php

namespace Routers;

use Controller;
use Router;
use Exception;

class ApiRouter extends Router {
  public function __construct($p = array()) {
    parent::__construct($p);
  }

  function run($parameters, $next = null) {
    $cn = $parameters['controller'];

    try {
      $controller = Controller::getController($cn);
      if (!$controller)
        throw new Exception('Service ' . $cn . ' is not available.');
      $controller->run($parameters, $next);
    } catch (Exception $ex) {
      if ($ex->getCode() != 0)
        http_response_code($ex->getCode());
      else
        http_response_code(500);

      header('Content-Type: application/json');
      echo json_encode(array(
        'status' => 'ERROR',
        'message' => $ex->getMessage()
      ));
    }
  }
}
