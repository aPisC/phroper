<?php

namespace Phroper\Model\Fields;

use Exception;

class Enum extends Text {
  private array $allowedValues;

  public function __construct($allowedValues, array $data = null) {
    $this->allowedValues = $allowedValues;
    parent::__construct([
      "type" => "enum",
      "values" => $this->allowedValues
    ]);
    $this->updateData($data);
  }

  public function onSave($value) {
    $value = parent::onSave($value);
    if ($value instanceof Exception) return $value;
    if ($value == null) return null;
    if (!in_array($value, $this->allowedValues))
      return new Exception("Enum value '" . $value . "' is not allowed");

    return $value;
  }

  public function getSQLConstraint() {
    return "CONSTRAINT CHECK (`" . $this->data["sql_field"] . "` IN (" .
      implode(", ", array_map(
        function ($v) {
          return "\"" . $v . "\"";
        },
        $this->allowedValues
      )) . "))";
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!in_array($value, $this->allowedValues))
      return null;
    return $value;
  }
}
