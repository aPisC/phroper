<?php



namespace Model\Fields;

use Model;
use Models\MultiRelation;
use Model\LazyResult;

class RelationMulti extends Relation {

  private $connectionModel;

  public function __construct($model, $model2, $type = "default") {
    $this->model = $model2;
    $this->connectionModel = new MultiRelation($model, $model2, $type);
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!in_array($key, $populates)) return IgnoreField::instance();

    $model = $this->connectionModel;
    return new LazyResult(function () use ($model, $value, $key, $assoc, $populates) {
      $pop2 = array_filter($populates, function ($value) use ($key) {
        return str_starts_with($value, $key . ".");
      });
      $pop2 = array_map(function ($value) use ($key) {
        return substr($value, strlen($key) + 1);
      }, $pop2);

      return $model->getOthers($value, $pop2);
    });
  }

  public function postUpdate($value, $key, $entity) {
    if (is_array($value)) {
      $this->connectionModel->setOthers($entity["id"], $value);
      return false;
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
