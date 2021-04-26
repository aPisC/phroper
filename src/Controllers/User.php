<?php

namespace Phroper\Controllers;

use Services\User as UserService;

class User extends DefaultController {
  protected UserService $user_service;
  public function __construct() {
    parent::__construct("User");

    $this->user_service = $this->service;
  }
}
