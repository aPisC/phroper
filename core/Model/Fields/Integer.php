<?php

namespace Model\Fields;

class Integer extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
  }

  public function getSQLType() {
    return 'INT';
  }
}
