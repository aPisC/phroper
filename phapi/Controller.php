<?php

class Controller {
  protected Router $router;

  private $registeredHandlerInfos = [];

  public static function getController($controllerName) {
    try {
      if ($controllerName instanceof Controller)
        return $controllerName;
      $ccn = 'Controllers\\' . ucfirst($controllerName);
      if (class_exists($ccn))
        $controller = new $ccn();
      else {
        $controller = new DefaultController($controllerName);
      }
      return $controller;
    } catch (Error $ex) {
      return null;
    }
  }

  public function __construct() {
    $this->router =  new Router();
  }

  public function run($p, $next) {
    $this->router->run($p, $next);
  }

  protected function getName() {
    return strtolower(str_replace('\\', '_', get_class($this)));
  }

  protected function havePermission($action, $throw = false) {
    $user = Context::get('user');
    $auth = Service::getService('Auth');
    $permName = strtolower($this->getName() . '_' . $action);

    $have = $auth->havePermission($user, $permName);
    if (!$have && $throw)
      throw new Exception('User has no permission to use ' . $permName, 403);
    return $have;
  }

  protected function getRoutePermName($name, $method) {
    return strtolower(
      $method . ($name ? ('_' . str_replace('/', '_', $name)) : '')
    );
  }

  protected function registerHandler($name, $fun = null, $method = 'GET') {
    if ($fun == null) $fun = $name;
    if (is_string($fun)) $fun = function ($p, $n) use ($fun) {
      return $this->$fun($p, $n);
    };

    $this->router->add($name, function ($params, $next) use ($fun, $name) {

      $this->havePermission($this->getRoutePermName($name, $params['method']), true);

      $fun($params, $next);
    }, $method);
    $this->registeredHandlerInfos[] = [$name, $method];
  }

  protected function registerJsonHandler($name, $fun = null, $method = 'GET') {
    if ($fun == null) $fun = $name;
    if (is_string($fun)) $fun = function ($p, $n) use ($fun) {
      return $this->$fun($p, $n);
    };

    $this->router->add($name, function ($params, $next) use ($fun, $name) {
      try {
        // Throwing exception when user has no permission
        $this->havePermission($this->getRoutePermName($name, $params['method']), true);

        $nextCalled = false;
        $result = $fun($params, function () use (&$nextCalled, $next) {
          $nextCalled = true;
          $next();
        });
        if ($nextCalled) return;

        if ($result == null) {
          http_response_code(404);
        }
        if ($result instanceof Model\Entity)
          $result = $result->sanitizeEntity();

        header('Content-Type: application/json');
        echo json_encode($result);
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
    }, $method);

    $this->registeredHandlerInfos[] = [$name, $method];
  }

  public function getAvailablePermissions() {
    $perms = [];

    foreach ($this->registeredHandlerInfos as $info) {
      $method = $info[1];
      $name = str_replace('/', '_', $info[0]);
      if ($method === "*") {
        $perms[] = strtolower($this->getName()) . '_' .  $this->getRoutePermName($name, "GET");
        $perms[] = strtolower($this->getName()) . '_' . $this->getRoutePermName($name, "POST");
        $perms[] = strtolower($this->getName()) . '_' . $this->getRoutePermName($name, "PUT");
        $perms[] = strtolower($this->getName()) . '_' . $this->getRoutePermName($name, "DELETE");
      } else if (is_string($method))
        $perms[] = strtolower($this->getName()) . '_' . $this->getRoutePermName($name, $method);
      else if (is_array($method)) {
        foreach ($method as $m)
          $perms[] = strtolower($this->getName()) . '_' . $this->getRoutePermName($name, $m);
      }
    }
    sort($perms);
    return $perms;
  }
}
