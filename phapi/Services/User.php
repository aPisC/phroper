<?php

namespace Services;

use DefaultService;

class User extends DefaultService {

  public function __construct() {
    parent::__construct("Auth_User");
  }
}
