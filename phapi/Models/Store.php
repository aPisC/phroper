<?php

namespace Models;

use Phapi\Model;

class Store extends Model {
  public function __construct() {
    parent::__construct(["table" => "store"]);

    $this->fields->clear();
    $this->fields["key"] = new Model\Fields\TextKey();
    $this->fields["value"] = new Model\Fields\Json();
  }

  public function allowDefaultService() {
    return false;
  }

  public function getPrimaryField() {
    return "key";
  }
}
