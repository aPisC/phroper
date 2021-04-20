<?php

namespace Phapi\Model\Fields;

use Exception;
use Phapi;
use Phapi\Model\LazyResult;

class RelationToMany extends Relation {
  public function __construct($model, $via, array $data = null) {
    parent::__construct($model, $data);
    $this->updateData([
      "virtual" => true,
      "via" => $via,
      "readonly" => true,
      "type" => "relation_many",
    ]);
  }

  public function getVia() {
    return $this->data["via"];
  }

  public function onSave($value) {
    return new Exception("Saving to array relation is not allowed");
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!in_array($key, $populates)) return IgnoreField::instance();

    $model = $this->getModel();
    return new LazyResult(function () use ($model, $value, $key, $assoc, $populates) {
      $pop2 = array_filter($populates, function ($value) use ($key) {
        return str_starts_with($value, $key . ".");
      });
      $pop2 = array_map(function ($value) use ($key) {
        return substr($value, strlen($key) + 1);
      }, $pop2);

      return $model->find([$this->getVia() => $value], $pop2);
    });
  }

  public function getSanitizedValue($value) {
    if ($this->isPrivate())
      return IgnoreField::instance();
    if (is_array($value)) {
      $model = $this->getModel();
      return parent::getSanitizedValue(
        array_map(function ($entity) use ($model) {
          return $model->sanitizeEntity($entity);
        }, $value)
      );
    }
    return IgnoreField::instance();
  }
}
