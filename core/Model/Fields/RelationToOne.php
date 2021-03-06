<?php

namespace Model\Fields;

class RelationToOne extends Relation {
  public function __construct($model, array $data = null) {
    parent::__construct($model, $data);
  }

  public function getSQLType() {
    return 'INT';
  }

  public function onSave($value) {
    if (is_array($value)) {
      if (isset($value['id'])) return $value['id'];
      return null;
    }
    return $value;
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!in_array($key, $populates) || $value == null) return $value;
    return $this->getModel()->restoreEntity($assoc, $populates, $key);
  }
}
