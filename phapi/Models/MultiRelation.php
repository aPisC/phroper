<?php


namespace Models;

use Exception;
use Phapi\Model;
use QueryBuilder\QB_Const;
use Phapi;
use Phapi\Model\Fields\RelationMulti\MultiConnectionRelation;

class MultiRelation extends Model {

  private $model;
  private $model2;
  private bool $isReversed = false;

  public function __construct($model = null, $model2 = null, $type = "default") {
    parent::__construct("relation_multi_connections");

    try {
      $this->model = Phapi::model($model);
      $this->model2 = Phapi::model($model2);
    } catch (Exception $e) {
    }

    $this->fields->clear();
    $this->fields["type"] = new Phapi\Model\Fields\ConstFilter(
      new Phapi\Model\Fields\Text(["default" => $type])
    );
    $this->fields["table_1"] = new Phapi\Model\Fields\Text(
      [
        "default" => $this->model ? $this->model->getTableName() : null,
        "required" => true
      ]
    );
    $this->fields["table_2"] = new Phapi\Model\Fields\Text(
      [
        "default" => $this->model2 ? $this->model2->getTableName() : null,
        "required" => true
      ]
    );
    $this->fields["item_1"] = new Phapi\Model\Fields\Integer([
      "field" => "item_1",
      "required" => true
    ]);
    $this->fields["item_2"] = new Phapi\Model\Fields\Integer([
      "field" => "item_2",
      "required" => true
    ]);
    if ($this->model2) $this->fields["other"] = new MultiConnectionRelation($this->model2, [
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
      $entities->map(function ($e) {
        return isset($e["other"]) ? $e["other"] : $e;
      }),
      function ($e) {
        return !is_scalar($e) && $e;
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

  public function allowDefaultService() {
    return false;
  }

  public function getPrimaryField() {
    return null;
  }

  public function getUiInfo() {
    $info = parent::getUiInfo();
    $info["visible"] = false;
    return null;
  }
}
