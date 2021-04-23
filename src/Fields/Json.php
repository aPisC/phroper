<?php

namespace Phroper\Fields;

class Json extends Field {
  public function __construct(array $data = null) {
    parent::__construct([
      "type" => "json",
      "sql_type" => "TEXT"
    ]);
    $this->updateData($data);
  }

  public function onLoad($value, $key, $assoc, $populates) {
    return json_decode($value, true);
  }

  public function onSave($value) {
    $value = json_encode($value);
    return $value;
  }
}
