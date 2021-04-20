<?php

namespace Models;

use Phapi\Model;
use Phapi;

class AuthUser extends Model {
  public function __construct() {
    parent::__construct([
      "table" => "user",
      "display" => "username"
    ]);

    $this->fields['updated_by'] = null;
    $this->fields["username"] = new Phapi\Model\Fields\Text([
      "required",
      "unique",
      "regex" => "^[a-zA-Z]{4,20}$"
    ]);
    $this->fields["role"] = new Phapi\Model\Fields\RelationToOne("auth-role", [
      "required"
    ]);
    $this->fields["password"] = new Phapi\Model\Fields\Password();
    $this->fields["email"] = new Phapi\Model\Fields\Email(["required"]);
    $this->fields["isAdmin"] = new Phapi\Model\Fields\Boolean(["default" => false]);
  }


  public function init() {
    if (parent::init()) {
      $rMod = Phapi::model("AuthRole");
      $role = $rMod->findOne(["isDefault" => true]);

      $this->create([
        "username" => "admin",
        "password" => "admin",
        "isAdmin" => true,
        "role" => $role["id"],
      ]);

      return true;
    }
    return false;
  }

  public function getPopulateList($populate = null) {
    if (is_array($populate)) return $populate;
    return [];
  }
}
