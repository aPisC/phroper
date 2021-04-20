<?php

namespace Phapi\Model\Fields;

use Phapi;
use Phapi\Model\Entity;
use Phapi\Model\LazyResult;

class RelationToOne extends Relation {
  public function __construct($model, array $data = null) {
    parent::__construct($model, $data);

    $this->updateData([
      "sql_type" => "INT",
      "type" => "relation_one",
    ]);
  }

  public function onSave($value) {
    if (is_array($value)) {
      if (isset($value['id'])) return $value['id'];
      return null;
    }
    if (!$value) $value = null;
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
