<?php

namespace Phapi\Model\Fields;

use Phapi;

abstract class Relation extends Field {
  protected $model = null;

  public function __construct($model, array $data = null) {
    parent::__construct($data);
    $this->model = $model;
  }

  public function getModel() {
    return Phapi::model($this->model);
  }

  public function isDefaultPopulated() {
    return true;
  }
}
