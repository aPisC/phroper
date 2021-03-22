<?php

namespace Models;

use Model;

class Auth_User extends Model {
  public function __construct() {
    parent::__construct('user');

    $this->fields['updated_by'] = null;

    $this->fields["username"] = new Model\Fields\Text();
    $this->fields["role"] = new Model\Fields\RelationToOne("Auth_Role");
    $this->fields["password"] = new Model\Fields\Password();
    $this->fields["email"] = new Model\Fields\Text();
    $this->fields["isAdmin"] = new Model\Fields\Boolean(["default" => false]);
  }

  public function allowDefaultService() {
    return true;
  }

  public function init() {
    if (parent::init()) {
      $rMod = Model::getModel("Auth_Role");
      $rMod->init();
      return true;
    }
    return false;
  }

  public function getPopulateList($populate = null) {
    if (is_array($populate)) return $populate;
    return [];
  }
}
