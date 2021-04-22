<?php

namespace Models;

use Phroper\Model;
use Phroper;
use Phroper\Model\Fields\Email;
use Phroper\Model\Fields\Password;

class AuthUser extends Model {
  public function __construct() {
    parent::__construct([
      "table" => "user",
      "display" => "username"
    ]);

    $this->fields['updated_by'] = null;
    $this->fields["username"] = new Phroper\Model\Fields\Text([
      "required",
      "unique",
      "regex" => "^[a-zA-Z]{4,20}$"
    ]);
    $this->fields["role"] = new Phroper\Model\Fields\RelationToOne("auth-role", [
      "required",
      "delete_action" => "RESTRICT"
    ]);
    $this->fields["password"] = new Phroper\Model\Fields\Password(["required"]);
    $this->fields["email"] = new Phroper\Model\Fields\Email(["required"]);
    $this->fields["isAdmin"] = new Phroper\Model\Fields\Boolean(["default" => false]);
  }


  public function init() {
    if (parent::init()) {
      $rMod = Phroper::model("AuthRole");
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
