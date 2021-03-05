<?php

namespace Model\Fields;

class Boolean extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
  }

  public function getSQLType() {
    return 'BOOLEAN';
  }

  public function savedValue($value) {
    if ($value == null) return null;
    return !!$value;
  }

  public function readValue($value) {
    if ($value == null) return null;
    return !!$value;
  }
}
