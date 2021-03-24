<?php

namespace Phapi\Model\Fields;

class Identity extends Integer {
  public function __construct(array $data = null) {
    parent::__construct($data);
  }

  public function getSQLType() {
    return 'INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY';
  }

  public function getUiInfo() {
    $i = parent::getUiInfo();
    $i["type"] = "identity";
    return $i;
  }

  public function isReadonly() {
    return true;
  }

  public function forceUpdate() {
    return true;
  }

  public function getDefault() {
    return null;
  }
}
