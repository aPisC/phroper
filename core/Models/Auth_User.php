<?php

namespace Models;

use Model;

class Auth_User extends Model {
  public function __construct() {
    parent::__construct('user');

    $this->fields["username"] = new Model\Fields\Text();
    $this->fields["role"] = new Model\Fields\RelationToOne("Auth_Role", ["field" => "role_id"]);
    $this->fields["password"] = new Model\Fields\Password();
    $this->fields["email"] = new Model\Fields\Text();
  }

  public function allowDefaultService() {
    return false;
  }
}
