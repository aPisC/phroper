<?php



namespace Phroper\Fields;

use Exception;
use Phroper\Model;
use Phroper\Model\LazyResult;
use Phroper\Phroper;

class RelationMulti_Model extends Model {
  public function __construct($model = null, $model2 = null, $relKey = "default") {
    $this->model = $model;
    $this->model2 = $model2;

    if (strcmp($this->model->getTableName(), $this->model2->getTableName()) == 0)
      throw new Exception("Relation is only allowed on different tables.");

    if (strcmp($this->model->getTableName(), $this->model2->getTableName()) > 0) {
      $m = $this->model;
      $this->model = $this->model2;
      $this->model2 = $m;
    }

    parent::__construct(["sql_table" => "mr_" . $this->model->getTableName() . "_" . $this->model2->getTableName() . "_" . $relKey]);

    $this->fields->clear();
    $this->fields[$this->model->getTableName()] = new RelationToOne($this->model, ["required", "sql_delete_action" => "CASCADE"]);
    $this->fields[$this->model2->getTableName()] = new RelationToOne($this->model2, ["required", "sql_delete_action" => "CASCADE"]);
  }
}

class RelationMulti extends Relation {
  private $otherModel;
  private $relKey;

  public function __construct($model2, $type = "default", $data = null) {
    parent::__construct(null, [
      "min" => null,
      "max" => null,
      "default" => []
    ]);
    $this->updateData($data);
    $this->otherModel = Phroper::model($model2);
    $this->relKey = $type;
  }

  public function bindModel($model, $fieldName) {
    parent::bindModel($model, $fieldName);
    $model = new RelationMulti_Model($model, $this->otherModel, $this->relKey);
    $this->relationModel = $model;
    $this->updateData([
      "model" => $model ? $this->getModel()->getName() : "",
      "display" => $this->data["display"] ? $this->data["display"] : ($model ? $this->getModel()->getDisplayField() : ""),
    ]);
  }

  public function onLoad($value, $key, $assoc, $populates) {
    if (!in_array($key, $populates)) return IgnoreField::instance();

    $relationModel = $this->relationModel;
    $model = $this->model;
    $otherKey = $this->otherModel->getTableName();

    return new LazyResult(function () use ($model, $relationModel, $otherKey, $value, $key, $assoc, $populates) {
      $pop2 = array_filter(
        $populates,
        function ($value) use ($key) {
          return str_starts_with($value, $key . ".") || $value == $key;
        }
      );
      $pop2 = array_map(
        function ($value) use ($key, $otherKey) {
          $key = substr($value, strlen($key) + 1);
          return $otherKey . ($key ? "." . $key . "." : "");
        },
        $pop2
      );

      return $relationModel->find([$model->getTableName() => $value], $pop2)->map(
        function ($mr) use ($otherKey) {
          return !is_scalar($mr[$otherKey]) ? $mr[$otherKey] : null;
        }
      );
    });
  }

  public function postUpdate($value, $key, $entity) {
    if (!$value) $value = [];

    if (is_array($value)) {
      if (isset($this->data["min"]) && count($value) < $this->data["min"])
        throw $this->validationError("min", $this->data["name"] . " requires at least " . $this->data["min"] . " entry.");
      if (isset($this->data["max"]) && count($value) > $this->data["max"])
        throw $this->validationError("max", $this->data["name"] . " can have " . $this->data["max"] . " entry.");

      $modelKey = $this->model->getTableName();
      $otherKey = $this->otherModel->getTableName();
      $id = $entity["id"];

      $this->relationModel->delete([$this->model->getTableName() => $id], false);

      if (count($value) == 0) return true;
      $this->relationModel->createMulti(
        array_map(
          function ($value) use ($modelKey, $otherKey, $id) {
            return [$modelKey => $id, $otherKey => $value];
          },
          $value
        ),
        false
      );
      return true;
    }
    return false;
  }


  public function getSanitizedValue($value) {
    if ($this->isPrivate())
      return IgnoreField::instance();
    if (is_array($value)) {
      $model = $this->otherModel;
      return parent::getSanitizedValue(
        array_map(function ($entity) use ($model) {
          return $model->sanitizeEntity($entity);
        }, $value)
      );
    }
    return IgnoreField::instance();
  }
}
