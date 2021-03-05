<?php
class QueryBuilder {

  private Model $model;

  private string $cmd_type;
  private string $cmd_from = "";
  private string $cmd_join = "";
  private string $cmd_filter = "";

  private QueryBuilder\BindCollector $bindings_filter;
  private QueryBuilder\BindCollector $bindings_values;

  private array $fields = array();
  private array $tableMap = array();
  private array $values = array();
  private array $joins = array();

  public string $lastSql = "";


  function __construct($model, $type) {
    $this->cmd_type = strtoupper($type);
    $this->model = Model::getModel($model);

    $this->bindings_filter = new QueryBuilder\BindCollector();
    $this->bindings_values = new QueryBuilder\BindCollector();

    $tableName = $model->getTableName();
    $this->tableMap = array(
      "" => $tableName . ""
    );
    $this->cmd_from = $tableName . " as " . $this->tableMap[""] . " \n";

    $this->collectFields($model->fields);
  }

  public function addQueryObject($filter) {
    $rf = $this->composeRawFilterByObject($filter);
    $this->addRawFilter(...$rf);
  }

  public function addRawFilter(...$filter) {
    if (strtoupper($this->cmd_type) == 'INSERT')
      throw new Exception("Filters are disabled in insert mode");

    if ($this->cmd_filter == "")
      $this->cmd_filter .= "WHERE ";
    else
      $this->cmd_filter .= "  AND ";
    $this->cmd_filter .= "(" . $this->composeFilter($filter) . ") \n";
  }

  function join($join, $collFields = true) {
    if (!isset($this->tableMap[$join])) {

      try {
        $this->resolve($join);
      } catch (Exception $ex) {
        return;
      }
      if (!($this->fields[$join]["field"] instanceof Model\Fields\RelationToOne))
        return;

      $model = Model::getModel($this->fields[$join]["field"]->getModel());

      $this->joins[$join] = $model;

      $tableName = $model->getTableName();

      $this->tableMap[$join] = $tableName . "_" . count($this->tableMap);

      $this->cmd_join .= "LEFT OUTER JOIN " . $tableName . " as " . $this->tableMap[$join] . " ";
      $this->cmd_join .= "ON " . $this->tableMap[$join] . ".id = " . $this->fields[$join]["source"] . " \n";
    }

    if ($collFields) {
      $model = $this->joins[$join];
      $this->collectFields($model->fields, $join);
    }
  }

  function populate($populate) {
    foreach ($populate as $p) {
      $this->join($p);
    }
  }

  public function setValue($key, $value) {
    $key_resolved = $this->resolve($key);
    $field = $this->fields[$key]["field"];
    if ($field->isReadonly() && $this->mode !== "INSERT")
      return;

    $this->values[$key_resolved] = $field->savedValue($value);
  }

  public function setAllValue($values, $deepUpdate = false, $prefix = "") {
    foreach ($values as $key => $value) {
      $memberName = $prefix == "" ? $key : $prefix . "." . $key;
      if (is_array($value) && isset($value["id"])) {
        $this->setValue($memberName, $value["id"]);
        if ($deepUpdate) {
          $this->setAllValue($value, $deepUpdate, $memberName);
        }
      } else if (!is_array($value)) {
        $this->setValue($memberName, $value);
      }
    }
  }

  public function execute($mysqli) {
    $this->lastSql = $this->getQuery();
    $stmt = $mysqli->prepare($this->lastSql);

    if ($stmt === false) throw new Exception("Statement could not be prepared \n" . $this->lastSql);

    $bindValues = array_merge($this->bindings_values->getBindValues(), $this->bindings_filter->getBindValues());
    if (count($bindValues) > 0)
      $stmt->bind_param(
        $this->bindings_values->getBindStr() . $this->bindings_filter->getBindStr(),
        ...$bindValues
      );

    $exec = $stmt->execute();
    if (strtoupper($this->cmd_type) == "SELECT" || strtoupper($this->cmd_type) == "COUNT") {
      $result = $stmt->get_result();
      return $result;
    }
    return $exec;
  }

  private function getQuery() {
    if (strtoupper($this->cmd_type) == "SELECT") {
      // Fields and aliases
      $fieldList = "";
      $index = 0;
      foreach ($this->fields as $field) {
        if ($field["field"] instanceof Model\Fields\RelationToMany) continue;
        if ($field["hidden"]) continue;
        if ($index++ > 0) $fieldList .= ", ";
        $fieldList .= $field["source"] . " as '" . $field["alias"] . "'";
      }

      return "SELECT " . $fieldList . " \n FROM " . $this->cmd_from . $this->cmd_join . $this->cmd_filter;
    }

    if (strtoupper($this->cmd_type) == "COUNT") {
      $query = "INSERT ";
      $this->bindings_values = new QueryBuilder\BindCollector();

      $columnList = "";
      $valueList = "";
      $index = 0;
      foreach ($this->values as $key => $value) {
        if ($index++ !== 0) {
          $columnList .= ", ";
          $valueList .= ", ";
        }
        $columnList .= $key;
        $valueList .= $this->bindings_values->push($value);
      }

      return "SELECT count(*) FROM " . $this->cmd_from . $this->cmd_join . $this->cmd_filter;
    }

    if (strtoupper($this->cmd_type) == "DELETE") {
      $query = "SELECT ";
      $this->bind_params = new QueryBuilder\BindCollector();

      return "DELETE " . $this->tableMap[""] . " \n FROM " . $this->cmd_from . $this->cmd_join . $this->cmd_filter;
    }

    if (strtoupper($this->cmd_type) == "UPDATE") {
      $query = "UPDATE ";
      $this->bindings_values = new QueryBuilder\BindCollector();

      $setList = "";
      $index = 0;
      foreach ($this->values as $key => $value) {
        if ($index++ !== 0) $setList .= ", ";
        $setList .= $key . "=" . $this->bindings_values->push($value);
      }

      return "UPDATE " . $this->cmd_from . $this->cmd_join . " SET " . $setList . " \n " .  $this->cmd_filter;
    }

    if (strtoupper($this->cmd_type) == "INSERT") {
      $query = "INSERT ";
      $this->bindings_values = new QueryBuilder\BindCollector();

      $columnList = "";
      $valueList = "";
      $index = 0;
      foreach ($this->values as $key => $value) {
        if ($index++ !== 0) {
          $columnList .= ", ";
          $valueList .= ", ";
        }
        $columnList .= $key;
        $valueList .= $this->bindings_values->push($value);
      }

      return "INSERT INTO " . $this->tableMap[""] . " (" . $columnList . ") \n VALUES (" . $valueList . ") \n";
    }

    throw new Exception("Invalid query type " . $this->cmd_type);
  }

  private function resolve($key) {
    if (isset($this->fields[$key]))
      return $this->fields[$key]["source"];

    $pos = strrpos($key, ".");
    if ($pos == false) throw new Exception("Field " . $key . " clould not be resolved");

    $rel = substr($key, 0, $pos);
    $fn = substr($key, $pos + 1);

    $this->resolve($rel);
    if ($this->fields[$rel]["field"] instanceof Model\Fields\RelationToOne) {
      if (!isset($this->joins[$rel])) {
        $this->join($rel, false);
      }

      if (isset($this->joins[$rel]->fields[$fn])) {
        $field = $this->joins[$rel]->fields[$fn];

        if ($field instanceof Model\Fields\RelationToMany)
          throw  new Exception("Field " . $key . " clould not be resolved");

        $fieldName = $field->getFieldName($key);

        $this->fields[$key] =  array(
          "source" => $this->tableMap[$rel] . "." . $fieldName,
          "alias" => $key,
          "field" => $field,
          "hidden" => true,
        );

        return $this->fields[$key]["source"];
      }
    }
    throw new Exception("Field " . $key . " clould not be resolved");
  }

  private function composeRawFilterByKey($key, $value) {
    if ($key === "_or") {
      $args = ["or"];
      foreach ($value as $key => $part) {
        $sq = is_numeric($key)
          ? $this->composeRawFilterByObject($part)
          : $this->composeRawFilterByKey($key, $part);
        $args[] = $sq;
      }
      return $args;
    } else if ($key === "_not") {
      $args = ["not"];
      foreach ($value as $key => $part) {
        $sq = is_numeric($key)
          ? $this->composeRawFilterByObject($part)
          : $this->composeRawFilterByKey($key, $part);
        $args[] = $sq;
      }
      return $args;
    } else if ($key === "_and") {
      $args = ["and"];
      foreach ($value as $part) {
        $sq = $this->composeRawFilterByObject($part);
        $args[] = $sq;
      }
      return $args;
    } else if (str_ends_with($key, "_ne"))
      return ["<>", new QB_Ref(str_drop_end($key, 3)), $value];
    else if (str_ends_with($key, "_ge"))
      return [">=", new QB_Ref(str_drop_end($key, 3)), $value];
    else if (str_ends_with($key, "_le"))
      return ["<=", new QB_Ref(str_drop_end($key, 3)), $value];
    else if (str_ends_with($key, "_gt"))
      return [">", new QB_Ref(str_drop_end($key, 3)), $value];
    else if (str_ends_with($key, "_lt"))
      return ["<", new QB_Ref(str_drop_end($key, 3)), $value];
    else if (str_ends_with($key, "_in"))
      return ["in", new QB_Ref(str_drop_end($key, 3)), ...$value];
    else if (str_ends_with($key, "_like"))
      return ["like", new QB_Ref(str_drop_end($key, 5)), $value];
    else if (str_ends_with($key, "_notlike"))
      return ["notlike", new QB_Ref(str_drop_end($key, 5)), $value];
    else if (str_ends_with($key, "_null"))
      return [$value ? "null" : "notnull", new QB_Ref(str_drop_end($key, 5))];
    return ["=", new QB_Ref($key), $value];
  }

  private function composeRawFilterByObject($query) {
    if (count($query) == 0) return false;
    $args = ["and"];
    foreach ($query as $key => $value) {
      $sq = $this->composeRawFilterByKey($key, $value);
      if (count($query) == 1)
        $args = $sq;
      else
        $args[] = $sq;
    }
    return $args;
  }

  private function composeFilterValue($value) {
    if ($value instanceof QB_Ref) {
      return $this->resolve($value->alias);
    }
    return $this->bindings_filter->push($value);
  }

  private function composeFilter($filter) {
    $operator = strtolower($filter[0]);
    $resolved = "";
    switch ($operator) {
      case "and":
        foreach ($filter as $index => $arg) {
          if ($index < 1) continue;
          if ($index > 1 && $index < count($filter)) $resolved .= " AND ";
          if (is_array($arg)) $resolved .= "(" . $this->composeFilter($arg) . ")";
          else $resolved .= $this->composeFilterValue($arg);
        }
        break;
      case "or":
        foreach ($filter as $index => $arg) {
          if ($index < 1) continue;
          if ($index > 1 && $index < count($filter)) $resolved .= " OR ";
          if (is_array($arg)) $resolved .= "(" . $this->composeFilter($arg) . ")";
          else $resolved .= $this->composeFilterValue($arg);
        }
        break;
      case "not":
        $resolved .= "NOT (";
        foreach ($filter as $index => $arg) {
          if ($index < 1) continue;
          if ($index > 1 && $index < count($filter)) $resolved .= " AND ";
          if (is_array($arg)) $resolved .= "(" . $this->composeFilter($arg) . ")";
          else $resolved .= $this->composeFilterValue($arg);
        }
        $resolved .= ")";
        break;
      case "in":
        $resolved .= $this->composeFilterValue($filter[1]) . " IN (";
        foreach ($filter as $index => $arg) {
          if ($index < 2) continue;
          if ($index > 2 && $index < count($filter)) $resolved .= ", ";
          $resolved .= $this->composeFilterValue($arg);
        }
        $resolved .= ")";
        break;
      case "notin":
        $resolved .= $this->composeFilterValue($filter[1]) . "NOT IN (";
        foreach ($filter as $index => $arg) {
          if ($index < 2) continue;
          if ($index > 2 && $index < count($filter)) $resolved .= ", ";
          $resolved .= $this->composeFilterValue($arg);
        }
        $resolved .= ")";
        break;
      case "=":
        $resolved .= $this->composeFilterValue($filter[1]) . " = " . $this->composeFilterValue($filter[2]);
        break;
      case "<":
        $resolved .= $this->composeFilterValue($filter[1]) . " < " . $this->composeFilterValue($filter[2]);
        break;
      case ">":
        $resolved .= $this->composeFilterValue($filter[1]) . " > " . $this->composeFilterValue($filter[2]);
        break;
      case "<=":
        $resolved .= $this->composeFilterValue($filter[1]) . " <= " . $this->composeFilterValue($filter[2]);
        break;
      case ">=":
        $resolved .= $this->composeFilterValue($filter[1]) . " >= " . $this->composeFilterValue($filter[2]);
        break;
      case "<>":
        $resolved .= $this->composeFilterValue($filter[1]) . " <> " . $this->composeFilterValue($filter[2]);
        break;
      case "not":
        $resolved .= "NOT " . $this->composeFilterValue($filter[1]);
        break;
      case "null":
        $resolved .= $this->composeFilterValue($filter[1]) . " IS NULL";
        break;
      case "notnull":
        $resolved .= $this->composeFilterValue($filter[1]) . " IS NOT NULL";
        break;
      case "like":
        $resolved .= $this->composeFilterValue($filter[1]) . " LIKE " . $this->composeFilterValue($filter[2]);
        break;
      case "notlike":
        $resolved .= $this->composeFilterValue($filter[1]) . " NOT LIKE " . $this->composeFilterValue($filter[2]);
        break;
    }

    return $resolved;
  }

  private function collectFields($fields, $prefix = "") {
    foreach ($fields as $key => $field) {
      if ($field instanceof Model\Fields\RelationToMany) continue;

      $fieldName = $field->getFieldName($key);
      $alias = $prefix . ($prefix != "" ?  "." : "") . $key;

      $this->fields[$alias] =  array(
        "source" => $this->tableMap[$prefix] . "." . $fieldName,
        "alias" => $alias,
        "field" => $field,
        "hidden" => false,
      );

      // default and forceUpdated field values
      if ($prefix == "") {
        $value = null;
        if ($this->cmd_type == "INSERT" && $field->hasDefault()) {
          $value = $field->getDefault();
          $this->setValue($alias, $value);
        } else if (($this->cmd_type == "INSERT" || $this->cmd_type == "UPDATE") && $field->forceUpdate()) {
          $this->setValue($alias, $value);
        }
      }
    }
  }
}

namespace QueryBuilder;

class BindCollector {
  private $bindStr = "";
  private $bindValues = array();

  function push($value) {
    if ($value === true) return "TRUE";
    if ($value === false) return "FALSE";
    if ($value === null) return "NULL";

    array_push($this->bindValues, $value);

    if (is_string($value)) $this->bindStr .= "s";
    if (is_double($value)) $this->bindStr .= "d";
    if (is_integer($value)) $this->bindStr .= "i";

    return "?";
  }

  function getBindStr() {
    return $this->bindStr;
  }
  function getBindValues() {
    return $this->bindValues;
  }
}
