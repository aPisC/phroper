<?php

namespace Models;

use Phroper\Model;
use Phroper;

class AuthPermission extends Model {
  public function __construct() {
    parent::__construct([
      "table" => 'permission',
      "display" => "permission",
      "populate" => []
    ]);

    $this->fields["role"] = new Phroper\Model\Fields\RelationToOne("AuthRole", ["required", "delete_action" => "CASCADE"]);
    $this->fields["permission"] = new Phroper\Model\Fields\Text(["required" => true]);
  }

  public function allowDefaultService() {
    return false;
  }

  public function init() {
    if (parent::init()) {
      $rMod = Phroper::model("AuthRole");
      $role = $rMod->findOne(["isDefault" => true]);

      if ($role) {
        $this->create([
          "role" => $role,
          "permission" => "controllers_auth_post_register"
        ]);
        $this->create([
          "role" => $role,
          "permission" => "controllers_auth_post_login"
        ]);
        $this->create([
          "role" => $role,
          "permission" => "controllers_auth_get_me"
        ]);
        $this->create([
          "role" => $role,
          "permission" => "controllers_user_put_:id"
        ]);
      }
      return true;
    }
    return false;
  }
}
