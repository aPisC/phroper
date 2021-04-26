<?php

namespace Phroper\Controllers;

use Phroper\Services\User as ServicesUser;

class User extends DefaultController {
  protected ServicesUser $user_service;
  public function __construct() {
    parent::__construct("User");

    $this->user_service = $this->service;
  }
}
