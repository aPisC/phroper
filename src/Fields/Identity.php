<?php

namespace Phroper\Fields;

class Identity extends Field {
  public function __construct(array $data = null) {
    parent::__construct([
      "type" => "identity",
      "readonly" => true,
      "auto" => true,
      "required" => true,

      "sql_type" => "INTEGER",
      "sql_unsigned" => true,
      "sql_autoincrement" => true,
      "sql_primary" => true,
    ]);
    $this->updateData($data);
  }
}
