<?php

namespace Controllers;

use Controller;
use Model;

class Init extends Controller {
  public function __construct() {
    parent::__construct();
    $this->router->add(":model", function ($params, $next) {
      $model = Model::getModel($params["model"]);
      if (!$model) return $next();
      $model->init();
    }, "GET");
  }
}
