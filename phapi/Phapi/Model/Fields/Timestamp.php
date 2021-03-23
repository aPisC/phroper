<?php


namespace Phapi\Model\Fields;

class Timestamp extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
  }

  public function getSQLType() {
    return 'TIMESTAMP NULL DEFAULT NULL';
  }

  public function onSave($value) {
    if (is_numeric($value))
      return date("Y-m-d H:i:s", $value);
    return $value;
  }

  public function getUiInfo() {
    $i = parent::getUiInfo();
    $i["type"] = "timestamp";
    return $i;
  }
}
