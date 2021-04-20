<?php

namespace Phapi\Model\Fields;

use Phapi;

abstract class Relation extends Field {
  protected $relationModel = null;

  public function __construct($model, array $data = null) {
    parent::__construct($data);
    $this->relationModel = $model;
    $this->updateData([
      "model" => $this->getModel()->getName(),
      "model_display" => $this->getModel()->getDisplayField(),
      "populate" => true,
    ]);
  }

  public function getModel() {
    return Phapi::model($this->relationModel);
  }
}
