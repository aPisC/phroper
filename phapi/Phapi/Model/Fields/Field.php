<?php

namespace Phapi\Model\Fields;

use Exception;
use Phapi\Model;

abstract class Field {
  protected ?Model $model = null;
  protected ?array $data = [];


  public function __construct(array $data = null) {
    $this->updateData([
      "auto" => false,
      "default" => IgnoreField::instance(),
      "field" => null,
      "forced" => false,
      "private" => false,
      "populate" => false,
      "readonly" => false,
      "required" => false,
      "sql_type" => null,
      "type" => "text",
      "unique" => false,
      "virtual" => false,
    ]);

    $this->updateData($data);
  }

  public function getSQLType() {
    return $this->data["sql_type"]
      + ($this->data["unique"] ? " UNIQUE" : "")
      + ($this->data["required"] ? " NOT NULL" : "");
  }

  public function getFieldName() {
    return $this->data["field"];
  }

  public function isPrivate() {
    return $this->data["private"];
  }

  public function isAuto() {
    return $this->data["auto"];
  }

  public function isReadonly() {
    return $this->data["readonly"];
  }

  public function isRequired() {
    return $this->data["required"];
  }

  public function forceUpdate() {
    return $this->data["forced"];
  }

  public function getDefault() {
    $def = $this->data["default"];
    if (is_callable($def)) return $def();
    return $def;
  }

  public function isVirtual() {
    return $this->data["virtual"];
  }

  public function onSave($value) {
    return $value;
  }

  public function onLoad($value, $key, $assoc, $populates) {
    return $value;
  }

  public function postUpdate($value, $key, $entity) {
  }

  public function getSanitizedValue($value) {
    if ($this->isPrivate()) return IgnoreField::instance();
    return $value;
  }

  public function isJoinable() {
    return false;
  }

  public function isDefaultPopulated() {
    return $this->data["populate"];
  }

  public function getUiInfo() {
    $data = [];

    foreach ($this->data as $key => $value) {
      if ($value instanceof IgnoreField) continue;
      if (!is_scalar($value) && !is_array(($value))) continue;
      $data[$key] = $value;
    }

    return $data;
  }

  public function bindModel($model, $fieldName) {
    if ($this->model) throw new Exception("This field is already bound to a model");

    $this->data["key"] = $fieldName;
    $this->data["name"] = str_pc_text($fieldName);
    $this->data["field"] = $this->data["field"] ?  $this->data["field"] : $fieldName;

    $this->model = $model;
  }

  protected function updateData($data) {
    if (!$data) return;

    foreach ($data as $key => $value) {
      if (is_int($key) && is_string($value))
        $this->data[$value] = true;
      else
        $this->data[$key] = $value;
    }
  }
}
