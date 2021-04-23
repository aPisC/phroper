<?php

namespace Phroper\Model\Fields;

use Phroper;
use Phroper\Model\Entity;
use Phroper\Model\LazyResult;

class RelationToOne extends Relation {
  public function __construct($model, array $data = null) {
    parent::__construct($model, [
      "sql_type" => "INTEGER UNSIGNED",
      "type" => "relation_one",
      "sql_delete_action" => "RESTRICT",
      "virtual" => false,
    ]);

    $this->updateData($data);
  }

  public function getSQLConstraint() {
    return "FOREIGN KEY (`" . $this->data["field"] . "`) REFERENCES `" . $this->getModel()->getTableName() . "`(id) ON DELETE " . $this->data["sql_delete_action"];
  }

  public function onSave($value) {
    if ($value instanceof Entity) {
      if (isset($value['id'])) $value = $value['id'];
      else $value = null;
    } else if (is_array($value)) {
      if (isset($value['id'])) $value = $value['id'];
      else $value = null;
    }

    return parent::onSave($value);
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!in_array($key, $populates) || $value == null) return $value;
    $model = $this->getModel();
    return new LazyResult(function () use ($model, $value, $key, $assoc, $populates) {
      return $model->restoreEntity($assoc, $populates, $key);
    });
  }

  public function getSanitizedValue($value) {
    if ($this->isPrivate())
      return IgnoreField::instance();
    if ($value instanceof Entity) {
      $model = $this->getModel();
      $value = $model->sanitizeEntity($value);
    }
    return parent::getSanitizedValue($value);
  }

  public function useDefaultJoin() {
    return true;
  }

  public function isJoinable() {
    return true;
  }
}
