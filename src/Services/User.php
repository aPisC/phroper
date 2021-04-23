<?php

namespace Services;

class User extends DefaultService {

  public function __construct() {
    parent::__construct("AuthUser");
  }
}
