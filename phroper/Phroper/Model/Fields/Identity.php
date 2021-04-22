<?php

namespace Phroper\Model\Fields;

class Identity extends Integer {
  public function __construct(array $data = null) {
    parent::__construct([
      "type" => "identity",
      "readonly" => true,
      "auto" => true,
      "sql_type" => "INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY"
    ]);
    $this->updateData($data);
  }
}
