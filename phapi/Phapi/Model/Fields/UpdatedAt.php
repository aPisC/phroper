<?php

namespace Phapi\Model\Fields;

class UpdatedAt extends Timestamp {
  public function __construct() {
    parent::__construct(["field" => "updated_at"]);
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

  public function onSave($value) {
    return parent::onSave(time());
  }


  public function getSQLType() {
    return 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
  }
}
