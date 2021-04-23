<?php

namespace Phroper\Fields;

use Exception;

class Text extends Field {
  public function __construct(array $data = null) {
    parent::__construct([
      "sql_type" => "VARCHAR",
      "sql_length" => 255,
      "sql_truncate" => false,
      "min" => null,
      "max" => null,
    ]);
    $this->updateData($data);
  }

  public function onSave($value) {
    if ($value && isset($this->data["min"]) && strlen($value) < $this->data["min"])
      throw $this->validationError("min", $this->data["name"] . " is too short, minimum " . $this->data["min"] . " character is required.");

    if ($value && isset($this->data["max"]) && strlen($value) < $this->data["max"])
      throw $this->validationError("max", $this->data["name"] . " is too long, maximum " . $this->data["max"] . " character allowed.");

    if ($value && isset($this->data["regex"]) && !preg_match("/" . $this->data["regex"] . "/", $value))
      throw $this->validationError("regex", $this->data["name"] . " does not match the given pattern (/" . $this->data["regex"] . "/)");

    if ($value && $this->data["sql_truncate"] && strlen($value) > $this->data["sql_length"])
      $value = substr($value, 0, $this->data["sql_length"]);

    return parent::onSave($value);
  }
}
