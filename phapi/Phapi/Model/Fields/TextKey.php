<?php

namespace Phapi\Model\Fields;

class TextKey extends Text {
  public function __construct(array $data = null) {
    parent::__construct($data);
    $this->updateData(["sql_type" => "VARCHAR(255) NOT NULL PRIMARY KEY"]);
  }
}
