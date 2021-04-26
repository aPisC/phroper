<?php

namespace Phroper;

use Exception;

class Service {

  public function allowDefaultController() {
    return true;
  }

  public function getName() {
    $name = explode("\\", get_class($this));
    return str_pc_kebab(end($name));
  }

  public function __construct() {
  }

  function find($filter) {
    throw new Exception('Find is not implemented in service');
  }
  function findOne($filter) {
    throw new Exception('FindOne is not implemented in service');
  }
  function create($entity) {
    throw new Exception('FindOne is not implemented in service');
  }
  function update($filter, $entity) {
    throw new Exception('FindOne is not implemented in service');
  }
  function delete($filter) {
    throw new Exception('Delete is not implemented in service');
  }
  function count($filter) {
    throw new Exception('Delete is not implemented in service');
  }
}
