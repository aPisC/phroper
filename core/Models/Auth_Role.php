<?php

namespace Models;

use Model;

class Auth_Role extends Model {
  public function __construct() {
    parent::__construct('role');


    $this->fields["name"] = new Model\Fields\Text();
    $this->fields["users"] = new Model\Fields\RelationToMany("Auth_User", "role");
    $this->fields["isDefault"] = new Model\Fields\Boolean(["field" => "is_default"]);
    $this->fields["permissions"] = new Model\Fields\ArrayMapper(
      "permission",
      new Model\Fields\RelationToMany("Auth_Permission", "role")
    );
  }

  public function init() {
    if (parent::init()) {
      $this->create([
        "name" => "default",
        "isDefault" => true,
      ]);

      Model::getModel("Auth_Permission")->init();
      Model::getModel("Auth_User")->init();

      return true;
    }
    return false;
  }
}
