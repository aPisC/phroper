<?php

namespace Phapi\Model\Fields;

class TextKey extends Text {
  public function __construct(array $data = null) {
    parent::__construct([
      "required",
      "sql_type" => "VARCHAR(255) NOT NULL PRIMARY KEY",
    ]);
    $this->updateData($data);
  }
}
