<?php

namespace Models;

use Model;

class Auth_Permission extends Model {
  public function __construct() {
    parent::__construct('permission');

    $this->fields["role"] = new Model\Fields\RelationToOne("Auth_Role", ["required" => true]);
    $this->fields["permission"] = new Model\Fields\Text(["required" => true]);
  }

  public function getPopulateList($populate = null) {
    if (is_array($populate)) return $populate;
    return [];
  }

  public function allowDefaultService() {
    return false;
  }

  public function init() {
    if (parent::init()) {
      $rMod = Model::getModel("Auth_Role");

      $rMod->init();

      $role = $rMod->findOne(["isDefault" => true]);

      if ($role) {
        $this->create([
          "role" => $role,
          "permission" => "controllers_auth_post_grant"
        ]);
      }
      return true;
    }
    return false;
  }
}
