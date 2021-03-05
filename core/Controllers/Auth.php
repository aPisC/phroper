<?php

namespace Controllers;

use Controller;
use Service;
use Services\Auth as AuthService;
use Context;

class Auth extends Controller {
  private AuthService $service;

  public function __construct() {
    parent::__construct();

    $this->service = Service::getService('Auth');

    $this->registerJsonHandler("me", function () {
      return Context::get('user');
    }, 'GET');
    $this->registerJsonHandler("login", function () {
      return $this->login();
    }, 'POST');
    $this->registerJsonHandler("register", function () {
      return $this->register();
    }, 'POST');
    $this->registerJsonHandler("grant", function () {
      return $this->grant();
    }, 'POST');
    $this->registerJsonHandler("revoke", function () {
      return $this->revoke();
    }, 'POST');
    $this->registerJsonHandler("perms/:controller", function ($p) {
      return $this->perms($p);
    }, 'GET');
  }

  public function perms($p) {
    return $this->service->listControllerPerms($p['controller']);
  }


  public function grant() {
    $data = json_load_body();
    return $this->service->grant($data['role'], $data['permission']);
  }

  public function revoke() {
    $data = json_load_body();
    return $this->service->revoke($data['role'], $data['permission']);
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
