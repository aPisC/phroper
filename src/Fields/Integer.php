<?php

namespace Phroper\Fields;

use Exception;

class Integer extends Field {
  public function __construct(array $data = null) {
    parent::__construct(["type" => "int", "sql_type" => "INT"]);
    $this->updateData($data);
  }

  public function onSave($value) {
    if (isset($this->data["min"]) && $value != null && $value < $this->data["min"])
      throw $this->validationError("min", $this->data["name"] . " must be >= " . $this->data["min"]);
    if (isset($this->data["max"]) && $value != null && $value < $this->data["max"])
      throw $this->validationError("max", $this->data["name"] . " must be <= " . $this->data["max"]);

    return parent::onSave($value);
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!is_int($value)) return null;
    return $value;
  }
}
