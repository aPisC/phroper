<?php 

class Controller {
  protected Router $router;

  public static function getController($controllerName){
    $ccn = 'Controllers\\'. ucfirst($controllerName);
    if(class_exists($ccn))
      $controller = new $ccn();
    else{
      $controller = new DefaultController($controllerName);
    }
    return $controller;
  }

  public function __construct() {
      $this->router =  new Router();
  }

  public function run($u, $p){
    $this->router->run($u, $p);
  }

  protected function havePermission($action, $throw = false){
    $user = Context::get('user');
    $auth = Service::getService('Auth');
    $permName = strtolower(str_replace('\\', '_', get_class($this) ) . '_' . $action);

    $have = $auth->havePermission($user, $permName);
    if(!$have && $throw)
      throw new Exception('User has no permission to use ' . $permName, 403);
    return $have;
  }

  protected function registerHandler($name,$fun,$method = 'GET'){
    $this->router->add($name, $fun, $method);
  }

  protected function registerJsonHandler($name,$fun,$method = 'GET'){

    $this->router->add($name, function($url, $params) use ($fun, $name){
      header('Content-Type: application/json');
      try{
        // Throwing exception when user has no permission
        var_dump(strtolower($params['method'] . '_' . $name));
        $this->havePermission(strtolower($params['method'] . '_' . $name), true);

        $result = $fun($url, $params);
        if($result == null){
          http_response_code(404);
        }
        echo json_encode($result);
      }
      catch(Exception $ex){
        if($ex->getCode() != 0)
          http_response_code($ex->getCode());
        else
          http_response_code(500);

        echo json_encode(array(
          'status' => 'ERROR',
          'message' => $ex->getMessage()
        ));
      }
    }, $method);
  }
}

?>