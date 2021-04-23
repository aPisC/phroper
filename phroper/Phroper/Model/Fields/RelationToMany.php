<?php

namespace Phroper\Model\Fields;

use Exception;
use Phroper;
use Phroper\Model\EntityList;
use Phroper\Model\LazyResult;

class RelationToMany extends Relation {
  public function __construct($model, $via, array $data = null) {
    parent::__construct($model, [
      "virtual" => true,
      "via" => $via,
      "readonly" => true,
      "type" => "relation_many",
    ]);
    $this->updateData($data);
  }

  public function getVia() {
    return $this->data["via"];
  }

  public function onSave($value) {
    throw new Exception("Saving to array relation is not allowed");
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
    if ($value instanceof EntityList) {
      $model = $this->getModel();
      return parent::getSanitizedValue(
        $value->map(function ($entity) use ($model) {
          return $model->sanitizeEntity($entity);
        })
      );
    }
    return IgnoreField::instance();
  }
}
