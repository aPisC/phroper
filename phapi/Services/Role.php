<?php

namespace Services;

use DefaultService;
use Phapi\Model;
use Phapi;

class Role extends DefaultService {
  private Model $permModel;

  public function __construct() {
    parent::__construct("Auth_Role");
    $this->permModel = Phapi::model("Auth_Permission");
  }

  public function grant($role, $perm) {
    $entity = $this->permModel->findOne([
      "role" => $role,
      "permission" => $perm
    ]);

    if (!$entity) {
      $entity = $this->permModel->create([
        "role" => $role,
        "permission" => $perm
      ]);
    }
    return $entity;
  }

  public function revoke($role, $perm) {
    $entity = $this->permModel->delete([
      "role" => $role,
      "permission" => $perm
    ]);
    return $entity;
  }


  public function listControllerPerms($controller) {
    $controller = Phapi::controller($controller);
    if (!$controller) return [];

    return $controller->getAvailablePermissions();
  }
}
