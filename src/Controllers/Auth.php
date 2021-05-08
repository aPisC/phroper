<?php

namespace Phroper\Controllers;

use Phroper\Controller;
use Phroper\Phroper;
use Phroper\Services\Auth as ServicesAuth;

class Auth extends Controller {
  private ServicesAuth $service;

  public function __construct() {
    parent::__construct();

    $this->service = Phroper::service('Auth');

    $this->registerJsonHandler("/me", "me", 'GET');
    $this->registerJsonHandler("/login", "login", 'POST');
    $this->registerJsonHandler("/register", "register", 'POST');
  }

  public function me() {
    return Phroper::context('user');
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
