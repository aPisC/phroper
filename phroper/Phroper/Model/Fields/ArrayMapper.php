<?php

namespace Phroper\Model\Fields;

use Exception;
use Phroper\Model\Entity;
use Phroper\Model\EntityList;
use Phroper\Model\LazyResult;

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


      if ($val instanceof EntityList) IgnoreField::instance();

      if (is_callable($mapper)) return $val->map($mapper);
      if (is_scalar($mapper)) return $val->map(function ($e) use ($mapper) {
        if ($e instanceof Entity && $e->offsetExists($mapper))
          return $e[$mapper];
        return null;
      });
      return null;
    });
  }

  public function getSanitizedValue($value) {
    return $value;
  }

  public function getUiInfo() {
    return null;
  }
}
