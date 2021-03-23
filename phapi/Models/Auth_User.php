<?php

namespace Models;

use Phapi\Model;
use Phapi;

class Auth_User extends Model {
  public function __construct() {
    parent::__construct('user');

    $this->fields['updated_by'] = null;

    $this->fields["username"] = new Phapi\Model\Fields\Text();
    $this->fields["role"] = new Phapi\Model\Fields\RelationToOne("Auth_Role");
    $this->fields["password"] = new Phapi\Model\Fields\Password();
    $this->fields["email"] = new Phapi\Model\Fields\Text();
    $this->fields["isAdmin"] = new Phapi\Model\Fields\Boolean(["default" => false]);
  }

  public function allowDefaultService() {
    return true;
  }

  public function init() {
    if (parent::init()) {
      $rMod = Phapi::model("Auth_Role");
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
