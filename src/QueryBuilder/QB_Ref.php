<?php

namespace Phroper\QueryBuilder;

class QB_Ref {
  public $alias;

  function __construct($fieldname) {
    $this->alias = $fieldname;
  }
}
