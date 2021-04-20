<?php

namespace Phapi\Model\Fields;

class Integer extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
    $this->updateData(["type" => "int", "sql_type" => "INT"]);
  }
}
