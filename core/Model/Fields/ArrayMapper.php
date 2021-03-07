<?php

namespace Model\Fields;

use Exception;
use Model\LazyResult;
use Model\Entity;

class ArrayMapper extends FieldExtension {
  protected $mapper;

  public function __construct($mapper, $base) {
    parent::__construct($base);
    $this->mapper = $mapper;
  }

  public function isReadonly() {
    return true;
  }

  public function onSave($value) {
    return new Exception("Saving ArrayMapper is not supported");
  }

  public function onLoad($value, $key, $assoc, $populates) {
    $mapper = $this->mapper;
    return new LazyResult(function () use ($value, $key, $assoc, $populates, $mapper) {
      $val = parent::onLoad($value, $key, $assoc, $populates);

      if ($val instanceof IgnoreField) return $val;
      if ($val instanceof LazyResult)  $val = $val->get();


      if (!is_array($val)) IgnoreField::instance();

      if (is_callable($mapper)) return array_map($mapper, $val);
      if (is_scalar($mapper)) return array_map(function ($e) use ($mapper) {
        if ($e instanceof Entity && $e->offsetExists($mapper))
          return $e[$mapper];
        return null;
      }, $val);
      return null;
    });
  }

  public function getSanitizedValue($value) {
    return $value;
  }
}
