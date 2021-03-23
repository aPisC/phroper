<?php


namespace Phapi\Model\Fields;

use Phapi;

class UpdatedBy extends RelationToOne {
  public function __construct() {
    parent::__construct("Auth_User", ["field" => "updated_by"]);
  }

  public function forceUpdate() {
    return true;
  }

  public function onSave($value) {
    $user = Phapi::context('user');
    if ($user) return $user["id"];
    return null;
  }

  public function isDefaultPopulated() {
    return false;
  }

  public function getUiInfo() {
    return null;
  }
}
