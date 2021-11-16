<?php

namespace Phroper\Controllers;

use Error;
use Exception;
use Phroper\Controller;
use Phroper\Phroper;
use Phroper\QueryBuilder;
use Phroper\View;
use Throwable;


class ItemView extends View
{
  public function render(mixed $data)
  {
    return strtr("
    <div style='margin-top: 16px;'>
      <p>{$data['name']}</p>
      <pre>{$data['sql']}</pre>
      <pre>{$data['result']}</pre>
    </div>
    ", $data);
  }
}

class Init extends Controller
{
  private function initModel($modelName)
  {
    $data = [
      "name" => "",
      "sql" => "",
      "result" => "",
    ];

    try {
      $data["name"] = $modelName;
      $model = Phroper::model($modelName);
      $success = $model->init();
      foreach (QueryBuilder::getExecutedQueries() as $sql)
        if (!str_starts_with($sql, "SELECT")) $data["sql"] .= $sql . "\n";
      QueryBuilder::resetExecutedQueries();
      $data['result'] = $success ? "Done" : "Already initialized";
    } catch (Throwable $ex) {
      foreach (QueryBuilder::getExecutedQueries() as $sql)
        if (!str_starts_with($sql, "SELECT")) $data["sql"] .= $sql . "\n";
      QueryBuilder::resetExecutedQueries();
      $data["result"] = $ex;
    }

    echo (new ItemView())->render($data);
  }

  public function __construct()
  {
    parent::__construct();

    $this->router->add("/all", function ($params, $next) {
      $list = Phroper::instance()->injector->listTypes();
      foreach ($list as $model) {
        if (!str_starts_with($model, "Models\\")) continue;
        $this->initModel(substr($model, 7));
      }
      if (is_dir(Phroper::dir("Models"))) {
        foreach (scandir(Phroper::dir("Models")) as $file) {
          if (substr($file, 0, 1) == ".") continue;
          if (is_dir($file)) continue;
          if (str_ends_with($file, ".php"))
            $this->initModel(basename($file, ".php"));
          if (str_ends_with($file, ".json"))
            $this->initModel(basename($file, ".json"));
        }
      }
    }, "GET");

    $this->router->add("/:model", function ($params, $next) {
      $this->initModel($params["model"]);
    }, "GET", -1);
  }
}
