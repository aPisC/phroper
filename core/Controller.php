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

  public function run($u, $p) {
    $this->router->run($u, $p);
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

  protected function registerHandler($name, $fun, $method = 'GET') {
    $this->router->add($name, function ($url, $params) use ($fun, $name) {

      $this->havePermission($this->getRoutePermName($name, $params['method']), true);

      $fun($url, $params);
    }, $method);
    $this->registeredHandlerInfos[] = [$name, $method];
  }

  protected function registerJsonHandler($name, $fun, $method = 'GET') {
    $this->router->add($name, function ($params, $next) use ($fun, $name) {
      header('Content-Type: application/json');
      try {
        // Throwing exception when user has no permission
        $this->havePermission($this->getRoutePermName($name, $params['method']), true);

        $result = $fun($params);
        if ($result == null) {
          http_response_code(404);
        }
        echo json_encode($result);
      } catch (Exception $ex) {
        if ($ex->getCode() != 0)
          http_response_code($ex->getCode());
        else
          http_response_code(500);

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
