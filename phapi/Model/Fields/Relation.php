<?php

namespace Model\Fields;

use Model;

abstract class Relation extends Field {
  protected $model = null;

  public function __construct($model, array $data = null) {
    parent::__construct($data);
    $this->model = $model;
  }

  public function getModel() {
    return Model::getModel($this->model);
  }

  public function isDefaultPopulated() {
    return true;
  }
}
