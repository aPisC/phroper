<?php

namespace Phroper\Models;

use Phroper\Fields\Json;
use Phroper\Fields\TextKey;
use Phroper\Model;

class Store extends Model {
  public function __construct() {
    parent::__construct(["table" => "store", "visible" => false]);

    $this->fields->clear();
    $this->fields["key"] = new TextKey();
    $this->fields["value"] = new Json();
  }

  public function allowDefaultService(): bool {
    return false;
  }

  public function getPrimaryField() {
    return "key";
  }
}
