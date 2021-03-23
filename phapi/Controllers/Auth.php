<?php

namespace Controllers;

use Services\Auth as AuthService;
use Phapi;

class Auth extends Phapi\Controller {
  private AuthService $service;

  public function __construct() {
    parent::__construct();

    $this->service = Phapi::service('Auth');

    $this->registerJsonHandler("me", function () {
      return Phapi::context('user');
    }, 'GET');
    $this->registerJsonHandler("login", function () {
      return $this->login();
    }, 'POST');
    $this->registerJsonHandler("register", function () {
      return $this->register();
    }, 'POST');
  }

  public function login() {
    $data = json_load_body();
    return $this->service->login($data['username'], $data['password']);
  }

  public function register() {
    $data = json_load_body();
    return $this->service->register($data['username'], $data['email'], $data['password']);
  }
}
