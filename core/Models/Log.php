<?php

namespace Models;
use Model;
class Log extends Model {
  public function __construct() {
    parent::__construct('log');

    $this->fields['timestamp'] = array(
      'type' => 'datetime',
      'private' => true,
    );
    $this->fields['type'] = array(
      'type' => 'varchar',
      'type_size' => '100'
    );
    $this->fields['message'] = array(
      'type' => 'varchar',
      'type_size' => '255'
    );
  }
}

?>