<?php

namespace Models;

use Phapi\Model;
use Phapi;

class Auth_Role extends Model {
  public function __construct() {
    parent::__construct('role');


    $this->fields["name"] = new Phapi\Model\Fields\Text();
    $this->fields["isDefault"] = new Phapi\Model\Fields\Boolean(["field" => "is_default"]);
    $this->fields["permissions"] = new Phapi\Model\Fields\ArrayMapper(
      "permission",
      new Phapi\Model\Fields\RelationToMany("Auth_Permission", "role")
    );
    $this->fields["users"] = new Phapi\Model\Fields\RelationToMany("Auth_User", "role");
  }

  public function init() {
    if (parent::init()) {
      $this->create([
        "name" => "default",
        "isDefault" => true,
      ]);

      Phapi::model("Auth_Permission")->init();
      Phapi::model("Auth_User")->init();

      return true;
    }
    return false;
  }
}
