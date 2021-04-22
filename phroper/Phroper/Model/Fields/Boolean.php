<?php

namespace Phroper\Model\Fields;

class Boolean extends Field {
  public function __construct(array $data = null) {
    parent::__construct([
      "type" => "bool",
      "sql_type" => "BOOLEAN",
      "default" => false,
    ]);
    $this->updateData($data);
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
