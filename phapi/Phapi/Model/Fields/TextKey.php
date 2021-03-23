<?php

namespace Phapi\Model\Fields;

class TextKey extends Text {
  public function __construct(array $data = null) {
    parent::__construct($data);
  }

  public function getSQLType() {
    return 'VARCHAR(255) NOT NULL PRIMARY KEY';
  }
}
