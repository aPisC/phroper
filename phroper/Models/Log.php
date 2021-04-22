<?php

namespace Models;

use Phroper\Model;

class Log extends Model {
  public function __construct() {
    parent::__construct(['table' => "log", "editable" => false]);

    $this->fields['updated_at'] = null;
    $this->fields['type'] = new Model\Fields\Enum([
      "debug",
      "info",
      "warn",
      "error",
    ]);
    $this->fields['message'] = new Model\Fields\Text();
  }

  public function allowDefaultService() {
    return false;
  }
}
