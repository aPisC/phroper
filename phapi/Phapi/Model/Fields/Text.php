<?php

namespace Phapi\Model\Fields;

class Text extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
  }

  public function getSQLType() {
    return 'VARCHAR(255)';
  }
}
