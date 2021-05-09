<?php

namespace Phroper;

use Exception;
use Phroper\Model;
use Phroper\QueryBuilder\BindCollector;
use Phroper\QueryBuilder\QB_Ref;
use Phroper\QueryBuilder\Traits\IJoinable;
use Throwable;

abstract class QueryBuilder {
  private static $execStack = [];
  public static function getExecutedQueries() {
    return self::$execStack;
  }
  public static function resetExecutedQueries() {
    self::$execStack = [];
  }

  protected array $fields = [];
  protected BindCollector $bindings;

  function __construct($model) {
    $this->model = Phroper::model($model);
    $this->bindings = new BindCollector();
    $this->collectFields($model->fields);
  }

  private static array $transactionStack = [];
  public static function withTransaction($mysqli, $function) {
    $id = spl_object_id($mysqli);
    if (!isset(self::$transactionStack[$id])) self::$transactionStack[$id] = null;
    self::$transactionStack[$id] += 1;

    $value = null;

    if (self::$transactionStack[$id] == 1) $mysqli->begin_transaction();
    try {
      $value = $function();
    } catch (Throwable $ex) {
      self::$transactionStack[$id] -= 1;
      if (self::$transactionStack[$id] == 0) $mysqli->rollback();
      throw $ex;
    }
    self::$transactionStack[$id] -= 1;
    if (self::$transactionStack[$id] == 0) $mysqli->commit();
    return $value;
  }

  abstract public function getQuery();


  protected function execHasResult() {
    return false;
  }

  public function execute($mysqli) {
    $this->lastSql = $this->getQuery();

    //var_dump($this->lastSql);

    self::$execStack[] = $this->lastSql;

    $stmt = $mysqli->prepare($this->lastSql);

    if ($stmt === false) {
      error_log("SQL statement could not be prepared.\n\n" . "\n\n" . $mysqli->error . $this->lastSql);
      throw new Exception("SQL statement could not be prepared.");
    }

    $bindValues = array_merge(
      $this->bindings->getBindValues("values"),
      $this->bindings->getBindValues("filter")
    );
    $bindStr =  $this->bindings->getBindStr("values")
      . $this->bindings->getBindStr("filter");
    if (count($bindValues) > 0)
      $stmt->bind_param(
        $bindStr,
        ...$bindValues
      );

    if ($stmt->error) {
      error_log("SQL parameter binding failed\n\n" . $this->lastSql . "\n\n" . $bindStr . "   " . json_encode($bindValues) . "\n\n" . $stmt->error);
      throw new Exception("Parameter binding failed.");
    }

    $exec = $stmt->execute();

    if ($stmt->error) {
      throw new Exception("Database error: " . $stmt->error);
    }


    if ($this->execHasResult())
      return $stmt->get_result();

    return $exec;
  }



  protected function resolve($key) {
    if ($key instanceof QB_Ref)
      $key = $key->alias;

    if (isset($this->fields[$key]))
      return $this->fields[$key];

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
    $resolved_rel = $this->resolve($rel);
    if (!$resolved_rel || !$resolved_rel["source"])
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
      return $this->fields[$key];

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
        "in_relation" => false,
      );
    }
  }
}
