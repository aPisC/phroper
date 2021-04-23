<?php


namespace Phroper\Fields;

use Phroper\Phroper;

class UpdatedBy extends RelationToOne {
  public function __construct() {
    parent::__construct("AuthUser", [
      "sql_field" => "updated_by",
      "forced" => true,
      "readonly" => true,
      "auto" => true,
      "sql_delete_action" => null,
      "sql_disable_constraint" => true
    ]);
  }

  public function onSave($value) {
    $user = Phroper::context('user');
    if ($user) return $user["id"];
    return null;
  }
}
