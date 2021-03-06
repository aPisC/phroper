<?php



namespace Model\Fields;

use Model;
use Model\Fields\RelationMulti\MultiRelationConnectorModel;

class RelationMulti extends Relation {

  private $connectionModel;

  public function __construct($model, $model2, $type = "default") {
    $this->model = $model2;
    $this->connectionModel = new MultiRelationConnectorModel($model, $model2, $type);
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!in_array($key, $populates)) return IgnoreField::instance();

    $pop2 = array_filter($populates, function ($value) use ($key) {
      return str_starts_with($value, $key . ".");
    });
    $pop2 = array_map(function ($value) use ($key) {
      return substr($value, strlen($key) + 1);
    }, $pop2);

    return $this->connectionModel->getOthers($value, $pop2);
  }

  public function postUpdate($value, $key, $entity) {
    if (is_array($value)) {
      return $this->connectionModel->setOthers($entity["id"], $value);
    }
  }

  public function isVirtual() {
    return true;
  }

  public function getSanitizedValue($value) {
    if ($this->isPrivate())
      return IgnoreField::instance();
    if (is_array($value)) {
      $model = Model::getModel($this->model);
      return parent::getSanitizedValue(
        array_map(function ($entity) use ($model) {
          return $model->sanitizeEntity($entity);
        }, $value)
      );
    }
    return IgnoreField::instance();
  }
}

namespace Models\Fields\RelationMulti;
