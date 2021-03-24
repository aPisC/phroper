<?php

namespace Models;

use Phapi\Model;
use Phapi;

class AuthUser extends Model {
  public function __construct() {
    parent::__construct('user');

    $this->fields['updated_by'] = null;

    $this->fields["username"] = new Phapi\Model\Fields\Text();
    $this->fields["role"] = new Phapi\Model\Fields\RelationToOne("AuthRole");
    $this->fields["password"] = new Phapi\Model\Fields\Password();
    $this->fields["email"] = new Phapi\Model\Fields\Email();
    $this->fields["isAdmin"] = new Phapi\Model\Fields\Boolean(["default" => false]);
  }

  public function allowDefaultService() {
    return true;
  }

  public function init() {
    if (parent::init()) {
      $rMod = Phapi::model("AuthRole");
      $rMod->init();
      return true;
    }
    return false;
  }

  public function getPopulateList($populate = null) {
    if (is_array($populate)) return $populate;
    return [];
  }

  public function getUiInfo() {
    $info = parent::getUiInfo();
    $info["display"] = "username";
    return $info;
  }
}
