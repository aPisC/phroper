<?php


namespace Phroper\Fields;

use Phroper\Phroper;

class UpdatedBy extends RelationToOne {
  public function __construct($data = null) {
    parent::__construct("AuthUser", [
      "sql_field" => "updated_by",
      "forced" => true,
      "readonly" => true,
      "auto" => true,
      "sql_delete_action" => null,
      "sql_disable_constraint" => true,
      "listed" => false,
    ]);
    $this->updateData($data);
  }

  public function onSave($value) {
    $user = Phroper::instance()->context->get('user');
    if ($user) return $user["id"];
    return null;
  }
}
