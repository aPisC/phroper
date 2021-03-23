<?php

namespace Controllers;

use Services\Role as RoleService;

class Role extends DefaultController {
  protected RoleService $role_service;
  public function __construct() {
    parent::__construct("Role");

    $this->role_service = $this->service;

    $this->registerJsonHandler("grant", function () {
      return $this->grant();
    }, 'POST');
    $this->registerJsonHandler("revoke", function () {
      return $this->revoke();
    }, 'POST');
    $this->registerJsonHandler("perms/:controller", function ($p) {
      return $this->perms($p);
    }, 'GET');
  }


  public function perms($p) {
    return $this->role_service->listControllerPerms($p['controller']);
  }

  public function grant() {
    $data = json_load_body();
    return $this->role_service->grant($data['role'], $data['permission']);
  }

  public function revoke() {
    $data = json_load_body();
    return $this->role_service->revoke($data['role'], $data['permission']);
  }
}
