<?php

namespace Phroper\Model\Fields;

class TextKey extends Text {
  public function __construct(array $data = null) {
    parent::__construct([
      "required",
      "sql_type" => "VARCHAR",
      "sql_length" => 255,
      "sql_primary" => true,
    ]);
    $this->updateData($data);
  }
}
