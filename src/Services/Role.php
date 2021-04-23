<?php

namespace Services;

use Phroper\Model;
use Phroper\Phroper;

class Role extends DefaultService {
  private Model $permModel;

  public function __construct() {
    parent::__construct("Role");
    $this->permModel = Phroper::model("AuthPermission");
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
    $controller = Phroper::controller($controller);
    if (!$controller) return [];

    return $controller->getAvailablePermissions();
  }
}
