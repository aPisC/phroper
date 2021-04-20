<?php

namespace Phapi\Model\Fields;

use Exception;

class Integer extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
    $this->updateData(["type" => "int", "sql_type" => "INT"]);
  }

  public function onSave($value) {
    $value = parent::onSave($value);

    if ($value instanceof Exception) return $value;

    if (isset($this->data["min"]) && $value != null && $value < $this->data["min"])
      return new Exception($this->data["name"] . " must be >= " . $this->data["min"]);
    if (isset($this->data["max"]) && $value != null && $value < $this->data["max"])
      return new Exception($this->data["name"] . " must be <= " . $this->data["max"]);

    return $value;
  }
}
