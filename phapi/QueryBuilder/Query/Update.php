<?php

namespace QueryBuilder\Query;

use Exception;
use QueryBuilder;
use QueryBuilder\Traits\Filterable;
use QueryBuilder\Traits\IJoinable;
use QueryBuilder\Traits\Joinable;
use QueryBuilder\Traits\Modifiable;

class Update extends QueryBuilder implements IJoinable {
  use Filterable;
  use Modifiable;
  use Joinable;

  public function __construct(...$p) {
    parent::__construct(...$p);
    $this->__modifiable__init();

    foreach ($this->fields as $key => $field) {
      if (!$field || !$field["source"] || $field["field"]->isVirtual()) continue;

      if ($field["field"]->forceUpdate()) {
        $this->__modifiable__values->setDefaultValue(
          $field["source"],
          $field["field"]->onSave(null)
        );
      }
    }
  }

  function getQuery() {
    $this->bindings->reset("values");

    $setList = "";
    foreach ($this->__modifiable__values->getFields() as $index => $key) {
      if ($index++ !== 0) $setList .= ", ";
      $value = $this->__modifiable__values->getValue($key, 0);
      // Exceptions is stored to indicate it has to be overwritten
      if ($value instanceof Exception)
        throw $value;

      $setList .= $key . "=" . $this->bindings->push($value, "values");
    }

    return
      "UPDATE `" . $this->model->getTableName() . "`\n"
      . ($this->__joinable__sql ? ($this->__joinable__sql . "\n") : "")
      . "SET " . $setList . "\n"
      . ($this->__filterable__filter ? ("WHERE " . $this->__filterable__filter . "\n") : "");


    //return "UPDATE " . $this->cmd_from . $this->cmd_join . " SET " . $setList . " \n " .  $this->cmd_filter;
  }
}