<?php

namespace Controllers;

use Controller;
use Error;
use Exception;
use Model;

class Init extends Controller {
  public function __construct() {
    parent::__construct();

    $this->router->add("all", function ($params, $next) {
      $dl = is_dir("phapi" . DS . "Models") ? scandir("phapi" . DS . "Models") : [];
      $dl = is_dir("Models") ? array_merge($dl, scandir("Models")) : $dl;
      if ($dl) foreach ($dl as $d) {
        try {
          if (str_starts_with($d, ".")) continue;
          if (str_ends_with($d, ".php"))
            $d = str_drop_end($d, 4);
          $model = Model::getModel($d);
          if (!$model) continue;
          if ($model->init()) echo $d . ": done\n";
          else echo $d . ": already initialized \n";
        } catch (Error $ex) {
          echo $d . ": error " . $ex->getMessage() . "\n";
        } catch (Exception $ex) {
          echo $d . ": error " . $ex->getMessage() . "\n";
        }
      }
    }, "GET");

    $this->router->add(":model", function ($params, $next) {
      try {
        $model = Model::getModel($params["model"]);
        if (!$model) return $next();
        if ($model->init()) echo "done\n";
        else echo "already initialized \n";
      } catch (Error $ex) {
        echo "error \n";
      } catch (Exception $ex) {
        echo "error \n";
      }
    }, "GET");
  }
}
