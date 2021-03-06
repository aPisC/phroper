<?php


namespace Model\Fields\RelationMulti;

use Model;
use Text;

class MultiRelationConnectorModel extends Model {

  private $model;
  private $model2;
  private bool $isReversed = false;

  public function __construct($model, $model2, $type = "default") {
    parent::__construct("relation_multi_connections");

    $this->model = Model::getModel($model);
    $this->model2 = Model::getModel($model2);
    $this->isReversed = strcmp($this->model->getTableName(), $this->model2->getTableName()) > 0;


    $this->fields = [];
    $this->fields["model_1"] = new Model\Fields\Text(
      [
        "field" => $this->isReversed  ? "table_2" : "table_1",
        "default" => $this->model->getTableName(),
        "required" => true
      ]
    );
    $this->fields["model_2"] = new Model\Fields\Text(
      [
        "field" => $this->isReversed  ? "table_1" : "table_2",
        "default" => $this->model2->getTableName(),
        "required" => true
      ]
    );
    $this->fields["item"] = new Model\Fields\Integer([
      "field" => $this->isReversed  ? "item_2" : "item_1",
      "required" => true
    ]);
    $this->fields["other"] = new Model\Fields\RelationToOne($this->model2, [
      "field" => $this->isReversed  ? "item_1" : "item_2",
      "required" => true
    ]);
    $this->fields["type"] = new Model\Fields\Text(
      [
        "field" => "type",
        "default" => $type,
        "required" => true
      ]
    );
  }

  public function getOthers($item, $populates) {
    $pop2 = [];
    foreach ($populates as $i => $p) {
      $pop2[] = "other." . $p;
    }
    $pop2[] = "other";

    $entities = $this->find([
      "item" => $item,
      "model_1" => $this->fields["model_1"]->getDefault(),
      "model_2" => $this->fields["model_2"]->getDefault(),
      "type" => $this->fields["type"]->getDefault(),
    ], $pop2);
    return array_map(function ($e) {
      return isset($e["other"]) ? $e["other"] : $e;
    }, $entities);
  }

  public function setOthers($item, $others) {
    $insert = [];
    foreach ($others as $i => $v) {
      if (is_array($v) && isset($v["id"])) $v = $v["id"];
      $insert[] = [
        "item" => $item,
        "model_1" => $this->fields["model_1"]->getDefault(),
        "model_2" => $this->fields["model_2"]->getDefault(),
        "type" => $this->fields["type"]->getDefault(),
        "other" => $v
      ];
    }

    $this->delete([
      "item" => $item,
      "model_1" => $this->fields["model_1"]->getDefault(),
      "model_2" => $this->fields["model_2"]->getDefault(),
      "type" => $this->fields["type"]->getDefault(),
    ]);

    $this->createMulti($insert);
  }
}
