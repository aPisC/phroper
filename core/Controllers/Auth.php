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

    $this->registerJsonHandler(null, function() {return $this->login();});
  }

  public function login(){
    $data = json_load_body();
    return $this->service->login($data['username'], $data['password']);
  }
}