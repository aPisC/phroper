<?php

namespace Phapi\Model\Fields;

abstract class FieldExtension extends Field {
  protected $base;

  public function __construct($base) {
    $this->base = $base;
  }

  public function getSQLType() {
    return $this->base->getSQLType();
  }

  public function getFieldName($default) {
    return $this->base->getFieldName($default);
  }

  public function isPrivate() {
    return $this->base->isPrivate();
  }

  public function isReadonly() {
    return $this->base->isReadonly();
  }

  public function isRequired() {
    return $this->base->isRequired();
  }

  public function forceUpdate() {
    return $this->base->forceUpdate();
  }

  public function getDefault() {
    return $this->base->getDefault();
  }

  public function onSave($value) {
    return $this->base->onSave($value);
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
    $this->base->getSanitizedValue($value);
  }

  public function isVirtual() {
    return $this->base->isVirtual();
  }

  public function getFilter($fieldName, $prefix, $memberName, $sql_mode) {
    return $this->base->getFilter($fieldName, $prefix, $memberName, $sql_mode);
  }

  public function isJoinable() {
    return $this->base->isJoinable();
  }

  public function getModel() {
    return $this->base->getModel();
  }

  public function isDefaultPopulated() {
    return $this->base->isDefaultPopulated();
  }

  public function useDefaultJoin() {
    return $this->base->useDefaultJoin();
  }
}
