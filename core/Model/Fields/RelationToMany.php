<?php

namespace Model\Fields;

class RelationToMany extends Relation {
  private $via;
  public function __construct($model, $via, array $data = null) {
    parent::__construct($model, $data);
    $this->via = $via;
  }

  public function getVia() {
    return $this->via;
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!in_array($key, $populates)) return null;

    $pop2 = array_filter($populates, function ($value) use ($key) {
      return str_starts_with($value, $key . ".");
    });
    $pop2 = array_map(function ($value) use ($key) {
      return substr($value, strlen($key) + 1);
    }, $pop2);

    return $this->getModel()->find([$this->via => $value], $pop2);
  }
}
