<?php


namespace Model\Fields\RelationMulti;

use Model;
use QueryBuilder\QB_Const;

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
    $this->fields["type"] = new Model\Fields\ConstFilter(
      new Model\Fields\Text(["default" => $type])
    );
    $this->fields["table_1"] = new Model\Fields\Text(
      [
        "default" => $this->model->getTableName(),
        "required" => true
      ]
    );
    $this->fields["table_2"] = new Model\Fields\Text(
      [
        "default" => $this->model2->getTableName(),
        "required" => true
      ]
    );
    $this->fields["item_1"] = new Model\Fields\Integer([
      "field" => "item_1",
      "required" => true
    ]);
    $this->fields["item_2"] = new Model\Fields\Integer([
      "field" => "item_2",
      "required" => true
    ]);
    $this->fields["other"] = new MultiConnectionRelation($this->model2, [
      "field" => "item_2",
    ]);
  }

  public function getOthers($item, $populates) {
    $pop2 = [];
    foreach ($populates as $i => $p) {
      $pop2[] = "other." . $p;
    }
    $pop2[] = "other";

    $entities = $this->find(["_or" => [
      [
        "table_1" => new QB_Const($this->fields["table_1"]->getDefault()),
        "item_1" => $item,
      ],
      [
        "table_2" => new QB_Const($this->fields["table_1"]->getDefault()),
        "item_2" => $item,
      ]
    ]], $pop2);
    return array_filter(
      array_map(function ($e) {
        return isset($e["other"]) ? $e["other"] : $e;
      }, $entities),
      function ($e) {
        return is_array($e);
      }
    );
  }

  public function setOthers($item, $others) {
    $insert = [];
    foreach ($others as $v) {
      if (is_array($v) && isset($v["id"])) $v = $v["id"];
      $insert[] = [
        "item_1" => $item,
        "item_2" => $v,
        "table_1" => new QB_Const($this->fields["table_1"]->getDefault()),
        "table_2" => new QB_Const($this->fields["table_2"]->getDefault()),
      ];
    }

    $this->delete(["_or" => [
      [
        "table_1" => new QB_Const($this->fields["table_1"]->getDefault()),
        "item_1" => $item,
      ],
      [
        "table_2" => new QB_Const($this->fields["table_1"]->getDefault()),
        "item_2" => $item,
      ]
    ]], false);

    $this->createMulti($insert);
  }
}
