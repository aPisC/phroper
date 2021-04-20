<?php

namespace Phapi\Model\Fields;

class Text extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
    $this->updateData(["sql_type" => "VARCHAR(255)"]);
  }
}
