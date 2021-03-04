<?php

namespace Models;

use Model;

class Auth_Role extends Model {
  public function __construct() {
    parent::__construct('role');

    $this->fields['name'] = array(
      'type' => 'text',
      'sqltype' => 'VARCHAR(100)',
    );
    $this->fields['users'] = array(
      'type' => 'relation',
      'model' => 'Auth_User',
      'via' => 'role',
    );
    $this->fields['isDefault'] = array(
      "type" => 'bool',
      "field" => "is_default",
      "sqltype" => "BOOLEAN",
    );
  }

  public function allowDefaultService() {
    return false;
  }
}
