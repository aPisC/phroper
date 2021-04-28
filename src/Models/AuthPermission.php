<?php

namespace Phroper\Models;

use Phroper\Fields\RelationToOne;
use Phroper\Fields\Text;
use Phroper\Model;
use Phroper\Phroper;

class AuthPermission extends Model {
  public function __construct() {
    parent::__construct([
      "sql_table" => 'permission',
      "display" => "permission",
      "populate" => [],
      "default_service" => false
    ]);

    $this->fields["role"] = new RelationToOne("AuthRole", ["required", "sql_delete_action" => "CASCADE"]);
    $this->fields["permission"] = new Text(["required" => true]);
  }

  public function init() {
    if (parent::init()) {
      $rMod = Phroper::model("AuthRole");
      $role = $rMod->findOne(["isDefault" => true]);

      if ($role) {
        $this->create([
          "role" => $role,
          "permission" => "post.auth.login"
        ]);
        $this->create([
          "role" => $role,
          "permission" => "post.auth.register"
        ]);
        $this->create([
          "role" => $role,
          "permission" => "get.auth.me"
        ]);
      }
      return true;
    }
    return false;
  }
}
