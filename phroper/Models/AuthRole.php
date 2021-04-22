<?php

namespace Models;

use Phroper\Model;
use Phroper;

class AuthRole extends Model {
  public function __construct() {
    parent::__construct(["table" => 'role', "display" => "name"]);

    $this->fields["updated_by"] = null;
    $this->fields["name"] = new Phroper\Model\Fields\Text(["required"]);
    $this->fields["isDefault"] = new Phroper\Model\Fields\Boolean(["field" => "is_default", "default" => false]);
    $this->fields["permissions"] = new Phroper\Model\Fields\ArrayMapper(
      "permission",
      new Phroper\Model\Fields\RelationToMany("AuthPermission", "role")
    );
    $this->fields["users"] = new Phroper\Model\Fields\RelationToMany("AuthUser", "role");
  }

  public function init() {
    if (parent::init()) {
      $this->create([
        "name" => "default",
        "isDefault" => true,
      ]);

      return true;
    }
    return false;
  }
}
