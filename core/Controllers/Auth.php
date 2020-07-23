<?php 

namespace Controllers;
use Controller;
use Service;
use Services\Auth as AuthService;
class Auth extends Controller{
  private AuthService $service;
  
  public function __construct() {
    parent::__construct();

    $this->service = Service::getService('Auth');

    $this->registerJsonHandler("me", function($u, $params) {return $params['user'];});
    $this->registerJsonHandler("login", function() {return $this->login();}, 'POST');
  }

  public function login(){
    $data = json_load_body();
    return $this->service->login($data['username'], $data['password']);
  }
}