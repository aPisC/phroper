<?php

abstract class ModelBase {
  abstract public function getName();
  public function getTableName() {
    return $this->getName();
  }

  public function find($qbfn){

  }


}

?>