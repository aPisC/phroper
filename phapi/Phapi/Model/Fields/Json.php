<?php

namespace Phapi\Model\Fields;

class Json extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
  }

  public function getSQLType() {
    return 'TEXT';
  }

  public function onLoad($value, $key, $assoc, $populates) {
    return json_decode($value, true);
  }

  public function onSave($value) {
    return json_encode($value);
  }

  public function getUiInfo() {
    $i = parent::getUiInfo();
    $i["type"] = "json";
    return $i;
  }
}
