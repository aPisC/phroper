<?php

namespace Phroper;

use Exception;

class Router {

  private $routes = array();
  private $middlewares = array();
  private $handlers = array();

  public function __construct($parameters = array()) {
  }

  private array $expressionCache = [];

  private function getMatcher($expression) {
    $key = $expression;

    if (isset($this->expressionCache[$key]))
      return $this->expressionCache[$key];

    $matches = [];
    $names = [];

    if (preg_match_all("/(\\/:[^\\/]+)|(\\/::.+)/", $expression, $matches, 0,)) {
      foreach ($matches[0] as $m) {
        if (str_starts_with($m, "/::")) {
          $expression = str_replace($m, "/(.+)", $expression);
          $names[] = substr($m, 3);
        } else {
          $expression = str_replace($m, "/([^/]+)", $expression);
          $names[] = substr($m, 2);
        }
      }
    }
    $expression = str_replace("/", "\\/", substr($expression, 1));
    if (str_ends_with($expression, "/")) {
      $expression = str_drop_end($expression, 2) . "(\\/[^?]+)?";
      $names[] = "url";
    }
    $expression = "/^" . $expression . "$/";

    $this->expressionCache[$key] = [$expression, $names];

    return $this->expressionCache[$key];
  }

  public function matchUrl($expression, $url) {
    if ($expression == "//")  return ["url" => $url];

    if (!str_starts_with($expression, "/"))
      throw new Exception("Url expression format is incorrect, must be start with /");

    while (str_ends_with($url, "/")) $url = str_drop_end($url, 1);
    while (str_starts_with($url, "/")) $url = substr($url, 1);

    $matcher = $this->getMatcher($expression);

    if (preg_match($matcher[0], $url, $matches)) {
      $params = ["url" => ""];
      foreach ($matcher[1] as $index => $name) {
        if ($index + 1 < count($matches)) $params[$name] = $matches[$index + 1];
      }
      if (str_starts_with($params["url"], "/"))
        $params["url"] = substr($params["url"], 1);
      return $params;
    }

    return false;
  }

  protected function matchMethod($methodExpression, $method) {
    return $methodExpression == '*' ||
      $methodExpression == $method ||
      (is_array($methodExpression) && in_array($method,  $methodExpression));
  }

  public function add($expression, $handler, $method = '*', $priority = 0) {

    if (!str_starts_with($expression, "/"))
      throw new Exception("Url expression format is incorrect, must be start with /");

    $this->addHandler(function ($parameters, $next) use ($expression, $method, $handler) {
      if (!$this->matchMethod($method, $parameters['method'])) return $next();

      $np = $this->matchUrl($expression, $parameters['url']);
      if ($np === false) return $next();

      $this->runHandler($handler, array_merge($parameters, $np), $next);
    }, $priority);
  }


  protected function runHandler($handler, $parameters, $next) {
    if (is_callable($handler)) {
      return $handler($parameters, $next);
    } else if ($handler instanceof Router)
      return $handler->run($parameters, $next);
    else if (class_exists($handler)) {
      $r = new $handler();
      $r->run($parameters, $next);
    }
  }

  public function addHandler($function, $priority = 0) {
    array_push($this->handlers, [
      "handler" =>  $function,
      "priority" => -$priority
    ]);
  }

  public function addServeFolder($expression, $folder, $priority = -1000) {
    $this->add($expression, function ($p, $next) use ($folder) {

      $pf = realpath($folder);
      $fn = realpath($folder . DIRECTORY_SEPARATOR .  $p["url"]);


      if (is_dir($fn)) {
        if (file_exists($fn . DIRECTORY_SEPARATOR . "index.html"))
          $fn .= DIRECTORY_SEPARATOR . "index.html";
      }


      if ($pf && $fn && str_starts_with($fn, $pf) && file_exists($fn) && !str_ends_with($fn, ".php")) {
        header('Content-Type: ' . mime_content_type($fn));
        readfile($fn);
      } else {
        $next();
      }
    }, "GET", $priority);
  }

  public function addServeFile($expression, $fn, $priority = -1100) {
    $this->add($expression, function ($p, $next) use ($fn) {
      if (file_exists($fn) && !str_ends_with($fn, ".php")) {
        header('Content-Type: ' . mime_content_type($fn));
        readfile($fn);
      } else $next();
    }, "GET", $priority);
  }


  public function addServeCode($expression, $fn, $priority = -1100) {
    $this->add($expression, function ($p, $next) use ($fn) {
      if (file_exists($fn)) {
        include $fn;
      } else $next();
    }, "*", $priority);
  }

  public function run($parameters, $next = null) {
    array_multisort(array_map(function ($v) {
      return $v["priority"];
    }, $this->handlers), $this->handlers);
    $handled = true;
    $handlers = $this->handlers;
    $runner = null;
    $runner = function ($index) use (&$handled, $handlers, $parameters, &$runner) {
      if ($index >= 0 && $index < count($handlers)) {
        $next = function () use (&$runner, $index) {
          $runner($index + 1);
        };

        $this->runHandler($handlers[$index]["handler"], $parameters, $next);
      } else
        $handled = false;
    };


    $runner(0);
    if (!$handled && $next) $next($parameters);
  }
}
