<?php

namespace Phapi\Model\Fields;

use Exception;

class Enum extends Text {
  private array $allowedValues;

  public function __construct($allowedValues, array $data = null) {
    parent::__construct($data);
    $this->allowedValues = $allowedValues;
  }

  public function onSave($value) {
    if ($value == null) return null;

    if (!in_array($value, $this->allowedValues))
      return new Exception("Enum value '" . $value . "' is not allowed");

    return $value;
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!in_array($value, $this->allowedValues))
      return null;
    return $value;
  }

  public function getUiInfo() {
    $i = parent::getUiInfo();
    $i["type"] = "enum";
    $i["values"] = $this->allowedValues;
    return $i;
  }
}
