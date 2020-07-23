<?php 

namespace Controllers;
use Controller;
use Service;
use Services\Auth as AuthService;
use Context;
class Auth extends Controller{
  private AuthService $service;
  
  public function __construct() {
    parent::__construct();

    $this->service = Service::getService('Auth');

    $this->registerJsonHandler("me", function($u, $params) {return Context::get('user'); });
    $this->registerJsonHandler("login", function() {return $this->login();}, 'POST');
    $this->registerJsonHandler("register", function() {return $this->register();}, 'POST');
  }

  public function login(){
    $data = json_load_body();
    return $this->service->login($data['username'], $data['password']);
  }

  public function register(){
    $data = json_load_body();
    return $this->service->register($data['username'], $data['email'], $data['password']);
  }
}