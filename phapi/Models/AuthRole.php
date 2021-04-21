<?php

namespace Models;

use Phapi\Model;
use Phapi;

class AuthRole extends Model {
  public function __construct() {
    parent::__construct(["table" => 'role', "display" => "name"]);

    $this->fields["updated_by"] = null;
    $this->fields["name"] = new Phapi\Model\Fields\Text(["required"]);
    $this->fields["isDefault"] = new Phapi\Model\Fields\Boolean(["field" => "is_default", "default" => false]);
    $this->fields["permissions"] = new Phapi\Model\Fields\ArrayMapper(
      "permission",
      new Phapi\Model\Fields\RelationToMany("AuthPermission", "role")
    );
    $this->fields["users"] = new Phapi\Model\Fields\RelationToMany("AuthUser", "role");
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
