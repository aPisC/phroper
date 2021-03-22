<?php

namespace Model\Fields;

use QueryBuilder\QB_Ref;
use QueryBuilder\QB_Const;

class ConstFilter extends Field {
  private Field $base;

  public function __construct($baseField) {
    $this->base = $baseField;
    $data = [
      "private" => true,
      "readonly" => true,
      "default" => true,
    ];
  }


  public function getSQLType() {
    return $this->base->getSQLType();
  }

  public function getFieldName($default) {
    return $this->base->getFieldName($default);
  }

  public function isPrivate() {
    return true;
  }

  public function isReadonly() {
    return true;
  }

  public function isRequired() {
    return true;
  }

  public function getDefault() {
    return $this->base->getDefault();
  }

  public function onSave($value) {
    return new QB_Const(
      $this->base->onSave(
        $this->getDefault()
      )
    );
  }

  public function onLoad($value, $key, $assoc, $populates) {
    return $this->base->onLoad($value, $key, $assoc, $populates);
  }

  public function preUpdate($value, $key, $entity) {
    return $this->base->preUpdate($value, $key, $entity);
  }

  public function postUpdate($value, $key, $entity) {
    return $this->base->postUpdate($value, $key, $entity);
  }

  public function getSanitizedValue($value) {
    if ($this->isPrivate()) return IgnoreField::instance();
    return $this->base->getSanitizedValue($value);
  }

  public function isVirtual() {
    return false;
  }

  public function getFilter($fieldName, $prefix, $memberName, $sql_mode) {
    return ["=", new QB_Ref($memberName), $this->onSave(null)];
  }
}
