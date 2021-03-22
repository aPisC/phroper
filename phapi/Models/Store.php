<?php

namespace Models;

use Model;

class Store extends Model {
  public function __construct() {
    parent::__construct('store');

    $this->fields["name"] = new Model\Fields\Text(["required" => true]);
    $this->fields["admins"] = new Model\Fields\RelationMulti($this, "Auth_User", "admin");
    $this->fields["isDefault"] = new Model\Fields\Boolean(["field" => "is_default"]);
  }

  public function allowDefaultService() {
    return true;
  }

  public function getPopulateList($populate = null) {
    if (is_array($populate)) return $populate;
    return ["admins"];
  }
}
