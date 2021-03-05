<?php

namespace Model\Fields;

class RelationToOne extends Relation {
  public function __construct($model, array $data = null) {
    parent::__construct($model, $data);
  }

  public function getSQLType() {
    return 'INT';
  }
}
