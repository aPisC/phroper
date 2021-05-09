<?php

namespace Phroper\Fields;

use Exception;

class Enum extends Text {
  private array $allowedValues;

  public function __construct($allowedValues, array $data = null) {
    $this->allowedValues = $allowedValues;
    parent::__construct([
      "type" => "enum",
      "values" => $this->allowedValues,
      "sql_disable_constraint" => false,
    ]);
    $this->updateData($data);
  }

  public function onSave($value) {
    if ($value && !in_array($value, $this->allowedValues))
      throw $this->validationError("enum", "Enum value '" . $value . "' is not allowed");

    return parent::onSave($value);
  }

  public function getSQLConstraint() {
    if ($this->data["sql_disable_constraint"])
      return null;
    return "CONSTRAINT CHECK (`" . $this->data["sql_field"] . "` IN (" .
      implode(", ", array_map(
        function ($v) {
          return "\"" . $v . "\"";
        },
        $this->allowedValues
      )) . "))";
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!is_string($value))
      return null;
    if (!in_array($value, $this->allowedValues))
      return null;
    return $value;
  }
}
