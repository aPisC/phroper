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

  protected function registerHandler($name,$fun,$method = 'GET'){
    $this->router->add($name, $fun, $method);
  }

  protected function registerJsonHandler($name,$fun,$method = 'GET'){

    $this->router->add($name, function($url, $params) use ($fun){
      header('Content-Type: application/json');
      try{
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