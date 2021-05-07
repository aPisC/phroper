<?php

namespace Phroper\Models;

use Phroper\Fields\Boolean;
use Phroper\Model;
use Phroper\Fields\Email;
use Phroper\Fields\Password;
use Phroper\Fields\RelationToOne;
use Phroper\Fields\Text;
use Phroper\Phroper;

class AuthUser extends Model {
  public function __construct() {
    parent::__construct([
      "sql_table" => "user",
      "display" => "username"
    ]);

    $this->fields['updated_by'] = null;
    $this->fields["username"] = new Text([
      "required",
      "unique",
      "regex" => "^[a-zA-Z]{4,20}$"
    ]);
    $this->fields["role"] = new RelationToOne("auth-role", [
      "required",
      "sql_delete_action" => "RESTRICT"
    ]);
    $this->fields["password"] = new Password(["required"]);
    $this->fields["email"] = new Email(["required"]);
    $this->fields["isAdmin"] = new Boolean(["default" => false]);
  }


  public function init(): bool {
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

  public function getPopulateList($populate = null): ?array {
    if (is_array($populate)) return $populate;
    return [];
  }
}
