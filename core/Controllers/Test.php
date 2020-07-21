<?php
  namespace Controllers;

  use Router;
  class Test {
    public Router $router;
    public function __construct() {
      $this->router =  new Router();
      
      $this->router->add('count', function(){echo 'count';});
      $this->router->add('multi', function($u,$p){echo 'multi'; var_dump($u,$p);});
      $this->router->add('multi/::link', function($u,$p){echo 'multi'; var_dump($u,$p);});
      $this->router->add(':id', function(){echo 'id';});
      $this->router->add(null, function(){echo 'empty';});
    }
  }

?>