<?php

namespace Model\Fields;

class RelationToMany extends Relation {
  private $via;
  public function __construct($model, $via, array $data = null) {
    parent::__construct($model, $data);
    $this->via = $via;
  }

  public function getVia() {
    return $this->via;
  }
}
