<?php

namespace Phapi\Model\Fields;

class Identity extends Integer {
  public function __construct(array $data = null) {
    parent::__construct($data);
    $this->updateData([
      "type" => "identity",
      "readonly" => true,
      "auto" => true,
      "default" => null,
      "sql_type" => "INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY"
    ]);
  }
}
