<?php

namespace Phroper\Fields;

abstract class FieldExtension extends Field {
  protected $base;

  public function __construct($base) {
    $this->base = $base;
  }

  public function getSQLType() {
    return $this->base->getSQLType();
  }

  public function getFieldName() {
    return $this->base->getFieldName();
  }

  public function isPrivate() {
    return $this->base->isPrivate();
  }

  public function isAuto() {
    return $this->base->isAuto();
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

  public function isVirtual() {
    return $this->base->isVirtual();
  }

  public function onSave($value) {
    return $this->base->onSave($value);
  }

  public function onLoad($value, $key, $assoc, $populates) {
    return $this->base->onLoad($value, $key, $assoc, $populates);
  }


  public function postUpdate($value, $key, $entity) {
    return $this->base->postUpdate($value, $key, $entity);
  }

  public function getSanitizedValue($value) {
    $this->base->getSanitizedValue($value);
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

  public function getUiInfo() {
    return parent::getUiInfo();
  }

  public function bindModel($model, $fieldName) {
    $this->base->bindModel($model, $fieldName);
  }

  protected function updateData($data) {
    $this->base->updateData($data);
  }

  public function is($type) {
    return $this->base->is($type);
  }

  public function handleQuerySet($value, $key, $query, $rawUpdate) {
    return $this->base->handleQuerySet($value, $key, $query, $rawUpdate);
  }
}
