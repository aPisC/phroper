<?php

namespace Phapi\Model\Fields;

use Phapi;

abstract class Relation extends Field {
  protected $relationModel = null;

  public function __construct($model, array $data = null) {
    $this->relationModel = $model;
    parent::__construct([
      "model" => $model ? $this->getModel()->getName() : "",
      "model_display" => $model ? $this->getModel()->getDisplayField() : "",
      "populate" => true,
      "virtual" => true,
    ]);
    $this->updateData($data);
  }

  public function getModel() {
    return Phapi::model($this->relationModel);
  }
}
