<?php

namespace Phapi\Model\Fields;

use Exception;
use Phapi\Model\Entity;
use Phapi\Model\LazyResult;

class ObjectMapper extends FieldExtension {
  protected $mapper;

  public function __construct($mapper, $base) {
    parent::__construct($base);
    $this->mapper = $mapper;
  }

  public function isReadonly() {
    return true;
  }

  public function onSave($value) {
    return new Exception("Saving ObjectMapper is not supported");
  }

  public function onLoad($value, $key, $assoc, $populates) {
    $mapper = $this->mapper;
    return new LazyResult(function () use ($value, $key, $assoc, $populates, $mapper) {
      $val = parent::onLoad($value, $key, $assoc, $populates);

      if ($val instanceof IgnoreField) return $val;
      if ($val instanceof LazyResult)  $val = $val->get();

      if (!($val instanceof Entity)) return null;

      if (is_callable($mapper)) return $mapper($val);
      if (is_scalar($mapper) && $val->offsetExists($mapper)) {
        return $val[$mapper];
      }
      return null;
    });
  }

  public function getSanitizedValue($value) {
    return $value;
  }
}
