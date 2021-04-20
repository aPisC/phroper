<?php

use Phapi\Model;
use QueryBuilder\QB_Ref;
use QueryBuilder\Traits\IJoinable;

abstract class QueryBuilder {

  protected array $fields = [];
  protected QueryBuilder\BindCollector $bindings;

  function __construct($model) {
    $this->model = Phapi::model($model);
    $this->bindings = new QueryBuilder\BindCollector();
    $this->collectFields($model->fields);
  }

  abstract protected function getQuery();


  protected function execHasResult() {
    return false;
  }

  public function execute($mysqli) {
    $this->lastSql = $this->getQuery();

    //var_dump($this->lastSql);

    $stmt = $mysqli->prepare($this->lastSql);

    if ($stmt === false) throw new Exception(
      "Statement could not be prepared \n\n" . $this->lastSql . "\n\n" . $mysqli->error
    );

    $bindValues = array_merge(
      $this->bindings->getBindValues("values"),
      $this->bindings->getBindValues("filter")
    );
    if (count($bindValues) > 0)
      $stmt->bind_param(
        $this->bindings->getBindStr("values")
          . $this->bindings->getBindStr("filter"),
        ...$bindValues
      );


    $exec = $stmt->execute();

    if ($this->execHasResult())
      return $stmt->get_result();

    return $exec;
  }



  protected function resolve($key) {
    if ($key instanceof QB_Ref)
      $key = $key->alias;

    if (isset($this->fields[$key]))
      return $this->fields[$key]["source"];

    // Test if the key is single field
    $pos = strrpos($key, ".");
    if ($pos == false)
      throw new Exception(
        "Field " . $key . " clould not be resolved, no such field."
      );

    // Test if join is allowed
    if (!($this instanceof IJoinable))
      throw new Exception(
        "Field " . $key . " clould not be resolved, join is not allowed."
      );

    $rel = substr($key, 0, $pos);
    $fn = substr($key, $pos + 1);

    // Test if join field is resolveable
    if (!$this->resolve($rel))
      throw new Exception(
        "Field " . $key . " clould not be resolved, no join field available."
      );

    // Test if the field is joinable
    if (!$this->fields[$rel]["field"]->isJoinable())
      throw new Exception(
        "Field " . $key . " clould not be resolved, " . $rel . " is not joinable."
      );

    // Join field
    $this->join($rel, [$fn]);

    // Get source of field
    if (isset($this->fields[$key]))
      return $this->fields[$key]["source"];

    throw new Exception("Field " . $key . " clould not be resolved");
  }


  private function collectFields($fields) {
    $tableSource = "`" . $this->model->getTableName() . "`";

    foreach ($fields as $key => $field) {
      if (!$field) continue;

      $fieldName = $field->getFieldName($key);
      $alias = $key;
      $source = $tableSource . ".`" . $fieldName . "`";

      $this->fields[$alias] =  array(
        "source" => $field->isVirtual() ? false : $source,
        "alias" => $alias,
        "field" => $field,
        "hidden" => $field->isVirtual(),
      );
    }
  }
}
