<?php

namespace Phapi\Model\Fields;

class Boolean extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
    $this->updateData([
      "type" => "bool",
      "sql_type" => "BOOLEAN",
      "default" => false,
    ]);
  }

  public function onSave($value) {
    if ($value === null) return null;
    return !!$value;
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if ($value === null) return null;
    return !!$value;
  }
}
