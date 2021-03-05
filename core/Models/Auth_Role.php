<?php

namespace Models;

use Model;

class Auth_Role extends Model {
  public function __construct() {
    parent::__construct('role');


    $this->fields["name"] = new Model\Fields\Text();
    $this->fields["users"] = new Model\Fields\RelationToMany("Auth_User", "role");
    $this->fields["isDefault"] = new Model\Fields\Boolean(["field" => "is_default"]);
  }

  public function allowDefaultService() {
    return false;
  }
}
