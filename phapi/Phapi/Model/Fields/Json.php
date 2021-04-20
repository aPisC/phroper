<?php

namespace Phapi\Model\Fields;

class Json extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
    $this->updateData([
      "type" => "json",
      "sql_type" => "TEXT"
    ]);
  }

  public function onLoad($value, $key, $assoc, $populates) {
    return json_decode($value, true);
  }

  public function onSave($value) {
    return json_encode($value);
  }
}
