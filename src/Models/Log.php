<?php

namespace Phroper\Models;

use Phroper\Fields\Enum;
use Phroper\Fields\Text;
use Phroper\Model;

class Log extends Model {
  public function __construct() {
    parent::__construct(['table' => "log", "editable" => false]);

    $this->fields['updated_at'] = null;
    $this->fields['type'] = new Enum([
      "debug",
      "info",
      "warn",
      "error",
    ]);
    $this->fields['message'] = new Text();
  }

  public function allowDefaultService() {
    return false;
  }
}
