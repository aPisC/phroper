<?php

namespace Controllers;

use Error;
use Exception;
use Phroper;

class Init extends Phroper\Controller {
  public function __construct() {
    parent::__construct();

    $this->router->add("all", function ($params, $next) {
      $dl = is_dir("phroper" . DS . "Models") ? scandir("phroper" . DS . "Models") : [];
      $dl = is_dir("Models") ? array_merge($dl, scandir("Models")) : $dl;
      if ($dl) foreach ($dl as $d) {
        try {
          if (str_starts_with($d, ".")) continue;
          if (str_ends_with($d, ".php"))
            $d = str_drop_end($d, 4);
          else if (str_ends_with($d, ".json"))
            $d = str_drop_end($d, 5);
          $model = Phroper::model($d);
          if (!$model) continue;
          echo "Initializing " . $d . "\n";
          if ($model->init()) echo $d . ": done\n\n";
          else echo $d . ": already initialized \n\n";
        } catch (Error $ex) {
          echo $d . ": error " . $ex . "\n";
        } catch (Exception $ex) {
          echo $d . ": error " . $ex . "\n";
        }
      }
    }, "GET");

    $this->router->add(":model", function ($params, $next) {
      try {
        $model = Phroper::model($params["model"]);
        if (!$model) return $next();
        echo "Initializing \n";
        if ($model->init()) echo "done\n";
        else echo "already initialized \n";
      } catch (Error $ex) {
        echo "error \n";
      } catch (Exception $ex) {
        echo "error \n";
      }
    }, "GET", -1);
  }
}
