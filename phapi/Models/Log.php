<?php

namespace Models;

use Model;

class Log extends Model {
  public function __construct() {
    parent::__construct('log');

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
