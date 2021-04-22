<?php

namespace Phroper\Model\Fields;

use Exception;

class Text extends Field {
  public function __construct(array $data = null) {
    parent::__construct(["sql_type" => "VARCHAR(255)"]);
    $this->updateData($data);
  }

  public function onSave($value) {
    if ($value && isset($this->data["regex"]) && !preg_match("/" . $this->data["regex"] . "/", $value))
      return new Exception($this->data["name"] . " does not match /" . $this->data["regex"] . "/");

    return parent::onSave($value);
  }
}
