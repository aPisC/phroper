<?php

namespace Phapi\Model\Fields;

use Exception;

class Text extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
    $this->updateData(["sql_type" => "VARCHAR(255)"]);
  }

  public function onSave($value) {
    if (isset($this->data["regex"]) && !preg_match("/" . $this->data["regex"] . "/", $value))
      return new Exception($this->data["name"] . " does not match /" . $this->data["regex"] . "/");

    return parent::onSave($value);
  }
}
