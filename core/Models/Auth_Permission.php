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
}
