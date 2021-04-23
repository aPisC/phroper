<?php

namespace Phroper\QueryBuilder\Query;

use Exception;
use Phroper\QueryBuilder;
use Phroper\QueryBuilder\Traits\Filterable;
use Phroper\QueryBuilder\Traits\IJoinable;
use Phroper\QueryBuilder\Traits\Joinable;
use Phroper\QueryBuilder\Traits\Modifiable;
use Throwable;

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
        try {

          $this->__modifiable__values->setDefaultValue(
            $field["source"],
            $field["field"]->onSave(null)
          );
        } catch (Throwable $ex) {
          $this->__modifiable__values->setDefaultValue(
            $field["source"],
            $ex
          );
        }
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
