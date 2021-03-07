<?php
class Router {

  private $routes = array();
  private $parameters;
  private $middlewares = array();
  private $handlers = array();

  public function __construct($parameters = array()) {
    $this->parameters = $parameters;
  }

  protected function matchUrl($expression, $url, $isNamespace = false) {
    // returns matched parameters if mathes, false otherwise

    if (count($url) == 0 and $expression == null)
      return array();

    $routeParts = explode('/', $expression);
    $parameters = array();
    $isMatching = true;

    $i = 0;
    for (; $i < count($routeParts); $i++) {
      if ($i >= count($url)) {
        return false;
      }
      // Remaining mathcing parameter
      if (startsWith($routeParts[$i], '::')) {
        // concatenate remaining route parameters
        $pname = substr($routeParts[$i], 2);
        $parameters[$pname] = join(
          '/',
          array_filter($url, function ($v, $k) use ($i) {
            return $k >= $i;
          }, ARRAY_FILTER_USE_BOTH)
        );
        $i = count($url);
        break;
      }
      // One matching parameter
      else if (startsWith($routeParts[$i], ':')) {
        $pname = substr($routeParts[$i], 1);
        $parameters[$pname] = $url[$i];
      }
      // part is not parameter
      else if ($url[$i] != $routeParts[$i]) {
        return false;
      }
    }

    // Reject if not a namespace route and not he whole url processed
    if (!$isNamespace && $i != count($url)) {
      return false;
    }

    $parameters['url'] = array_values(
      array_filter($url, function ($v, $k) use ($i) {
        return $k >= $i;
      }, ARRAY_FILTER_USE_BOTH)
    );

    return $parameters;
  }

  protected function matchMethod($methodExpression, $method) {
    return $methodExpression == '*' ||
      $methodExpression == $method ||
      (is_array($methodExpression) && in_array($method,  $methodExpression));
  }

  public function add($expression, $handler, $method = '*') {
    $this->addHandler(function ($parameters, $next) use ($expression, $method, $handler) {
      if (!$this->matchMethod($method, $parameters['method'])) return $next();

      $np = $this->matchUrl($expression, $parameters['url'], false);
      var_dump(!!$np, $expression, $parameters["url"]);
      if ($np === false) return $next();

      $this->runHandler($handler, array_merge($parameters, $np), $next);
    });
  }
  public function addNamspace($expression, $handler) {
    $this->addHandler(function ($parameters, $next) use ($expression, $handler) {
      $np = $this->matchUrl($expression, $parameters['url'], true);
      if ($np === false) return $next();

      $this->runHandler($handler, array_merge($parameters, $np), $next);
    });
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

  public function addHandler($function) {
    array_push($this->handlers, $function);
  }

  public function run($parameters, $next = null) {
    $handled = true;
    $handlers = $this->handlers;
    $runner = null;
    $runner = function ($index) use (&$handled, $handlers, $parameters, &$runner) {
      if ($index >= 0 && $index < count($handlers)) {
        $next = function () use (&$runner, $index) {
          $runner($index + 1);
        };

        $this->runHandler($handlers[$index], $parameters, $next);
      } else
        $handled = false;
    };


    $runner(0);
    if (!$handled && $next) $next();
  }
}
