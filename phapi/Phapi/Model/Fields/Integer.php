<?php

namespace Phapi\Model\Fields;

class Integer extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
  }

  public function getSQLType() {
    return 'INT';
  }



  public function getUiInfo() {
    $i = parent::getUiInfo();
    $i["type"] = "int";
    return $i;
  }
}
