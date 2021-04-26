<?php

namespace Phroper\Controllers;

use Phroper\Services\Role as ServicesRole;

class Role extends DefaultController {
  protected ServicesRole $role_service;
  public function __construct() {
    parent::__construct("Role");

    $this->role_service = $this->service;

    $this->registerJsonHandler("grant", "grant", 'POST');
    $this->registerJsonHandler("revoke", "revoke", 'POST');
    $this->registerJsonHandler("perms/:controller", "perms", 'GET');
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
