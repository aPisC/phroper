<?php


namespace Phapi\Model\Fields;

use Phapi;

class UpdatedBy extends RelationToOne {
  public function __construct() {
    parent::__construct("AuthUser", ["field" => "updated_by"]);
  }

  public function onSave($value) {
    $user = Phapi::context('user');
    if ($user) return $user["id"];
    return null;
  }

  public function isDefaultPopulated() {
    return false;
  }

  public function forceUpdate() {
    return true;
  }

  public function isReadonly() {
    return true;
  }

  public function isAuto() {
    return true;
  }
}
