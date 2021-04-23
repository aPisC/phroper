<?php

namespace Phroper\Models;

use Phroper\Model;
use Phroper;

class AuthRole extends Model {
  public function __construct() {
    parent::__construct(["sql_table" => 'role', "display" => "name"]);

    $this->fields["updated_by"] = null;
    $this->fields["name"] = new Phroper\Fields\Text(["required"]);
    $this->fields["isDefault"] = new Phroper\Fields\Boolean(["sql_field" => "is_default", "default" => false]);
    $this->fields["permissions"] = new Phroper\Fields\ArrayMapper(
      "permission",
      new Phroper\Fields\RelationToMany("AuthPermission", "role")
    );
    $this->fields["users"] = new Phroper\Fields\RelationToMany("AuthUser", "role");
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
