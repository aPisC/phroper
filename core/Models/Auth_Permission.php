<?php

namespace Models;

use Model;

class Auth_Permission extends Model {
  public function __construct() {
    parent::__construct('permission');

    $this->fields["role"] = new Model\Fields\RelationToOne("Auth_Role");
    $this->fields["name"] = new Model\Fields\Text();
  }

  public function allowDefaultService() {
    return true;
  }
}
