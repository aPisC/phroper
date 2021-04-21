<?php

namespace Models;

use Phapi\Model;
use Phapi;
use Phapi\Model\Fields\Email;
use Phapi\Model\Fields\Password;

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
      "required",
      "delete_action" => "RESTRICT"
    ]);
    $this->fields["password"] = new Phapi\Model\Fields\Password(["required"]);
    $this->fields["email"] = new Phapi\Model\Fields\Email(["required"]);
    $this->fields["isAdmin"] = new Phapi\Model\Fields\Boolean(["default" => false]);
  }


  public function init() {
    if (parent::init()) {
      $rMod = Phapi::model("AuthRole");
      $role = $rMod->findOne(["isDefault" => true]);

      $this->fields["password"] = new Password(["regex" => null]);
      $this->fields["email"] = new Email(["regex" => null]);

      $this->create([
        "username" => "admin",
        "password" => "admin",
        "isAdmin" => true,
        "role" => $role,
        "email" => "",
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
