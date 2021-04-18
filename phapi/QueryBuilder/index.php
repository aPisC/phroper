<?php

use Phapi\Model;

class QueryBuilder {

  private Model $model;

  private string $cmd_type;
  private string $cmd_from = "";
  private string $cmd_join = "";
  private string $cmd_filter = "";
  private string $cmd_order = "";
  private $cmd_limit = "";
  private $cmd_offset = "";

  private QueryBuilder\BindCollector $bindings;

  private array $fields = array();
  private array $tableMap = array();
  private QueryBuilder\QBModificationCollector $values;
  private array $joins = array();

  public string $lastSql = "";


  function __construct($model, $type) {
    $this->cmd_type = strtoupper($type);
    $this->model = Phapi::model($model);

    $this->bindings = new QueryBuilder\BindCollector();
    $this->values = new QueryBuilder\QBModificationCollector();

    $tableName = $model->getTableName();
    $this->tableMap = array(
      "" => "`" . $tableName . "`"
    );
    $this->cmd_from =  "`" . $tableName . "` as " . $this->tableMap[""] . " \n";

    $this->setLimit(100);
    $this->collectFields($model->fields);
  }

  public function nextInsert() {
    $this->values->next();
  }

  public function addQueryObject($filter) {
    $rf = $this->composeRawFilterByObject($filter, true);
    if ($rf) $this->addRawFilter(...$rf);
  }

  public function addOrderBy($field, $isDesc = false) {
    foreach (explode(',', $field) as $f) {
      $id = $isDesc;
      if (str_ends_with(strtolower($f), ":desc")) {
        $f = str_drop_end($f, 5);
        $id = true;
      } else if (str_ends_with(strtolower($f), ":asc")) {
        $f = str_drop_end($f, 4);
        $id = false;
      }
      $key = $this->resolve($f);
      if (!$key) continue;
      if ($this->cmd_order) $this->cmd_order .= ", ";
      $this->cmd_order .=
        $key . ($id ? " DESC" : " ASC");
    }
  }

  public function addOrderByEq($field, $value, $isDesc = true) {
    $key = $this->resolve($field);
    if (!$key) return;
    if ($this->cmd_order) $this->cmd_order =  ", " . $this->cmd_order;
    $this->cmd_order =
      $key . " = " . intval($value) . ($isDesc ? " DESC" : " ASC") . $this->cmd_order;
  }

  public function setLimit($amount, $disableCap = false) {
    $amount = intval($amount);
    if ($amount < 0)
      $this->cmd_limit = 0;
    else if (!$disableCap && $amount > 100)
      $this->cmd_limit = 100;
    else
      $this->cmd_limit = $amount;
  }

  public function setOffset($amount) {
    $this->cmd_offset = intval($amount);
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

      if (!$this->resolve($join)) return;

      if (!($this->fields[$join]["field"] && $this->fields[$join]["field"]->isJoinable()))
        return;

      $model = Phapi::model($this->fields[$join]["field"]->getModel());

      $this->joins[$join] = $model;

      $tableName = $model->getTableName();

      $this->tableMap[$join] = "`" . $tableName . "_" . count($this->tableMap) . "`";

      $this->cmd_join .= "LEFT OUTER JOIN `" . $tableName . "` as " . $this->tableMap[$join] . " ";
      if ($this->fields[$join]["field"]->useDefaultJoin())
        $this->cmd_join .= "ON " . $this->tableMap[$join] . ".`id` = " . $this->fields[$join]["source"] . " \n";
      else $this->cmd_join .= "ON TRUE \n";
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

  public function setValue($key, $value, $rawUpdate = false) {
    $key_resolved = $this->resolve($key);
    if (!$key_resolved) return;
    $field = $this->fields[$key]["field"];
    if (($field->isReadonly() && $this->cmd_type !== "INSERT") || ($field->isAuto() && $this->cmd_type === "INSERT"))
      return;

    $newValue = $rawUpdate ? $value : $field->onSave($value);
    if ($newValue instanceof Phapi\Model\Fields\IgnoreField)
      return;

    if ($field->isRequired() && $newValue == null)
      $newValue = new Exception(
        "Field " . $key . " is required!"
      );
    $this->values->setValue($key_resolved, $newValue);
  }

  public function setAllValue($values, $prefix = "") {
    foreach ($values as $key => $value) {
      $memberName = $prefix == "" ? $key : $prefix . "." . $key;
      $this->setValue($memberName, $value);
    }
  }

  public function execute($mysqli) {
    $this->lastSql = $this->getQuery();
    //var_dump($this->lastSql);
    $stmt = $mysqli->prepare($this->lastSql);

    if ($stmt === false) throw new Exception("Statement could not be prepared \n" . $this->lastSql . "\n" . $mysqli->error);

    $bindValues = array_merge($this->bindings->getBindValues("values"), $this->bindings->getBindValues("filter"));
    if (count($bindValues) > 0)
      $stmt->bind_param(
        $this->bindings->getBindStr("values") . $this->bindings->getBindStr("filter"),
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
        if ($field["field"]->isVirtual()) continue;
        if ($field["hidden"]) continue;
        if ($fieldList) $fieldList .= ", ";
        $fieldList .= $field["source"] . " as '" . $field["alias"] . "'";
      }

      return "SELECT " . $fieldList
        . " \n FROM " . $this->cmd_from
        . $this->cmd_join
        . $this->cmd_filter
        . ($this->cmd_order ? ("\n ORDER BY " . $this->cmd_order) : "")
        . ($this->cmd_limit ? ("\n LIMIT " . $this->cmd_limit) : '')
        . ($this->cmd_offset ? ("\n OFFSET " . $this->cmd_offset) : '');
    }

    if (strtoupper($this->cmd_type) == "COUNT") {
      $query = "INSERT ";
      $this->bindings->reset("values");

      $columnList = "";
      $valueList = "";
      $index = 0;
      foreach ($this->values as $key => $value) {
        if ($index++ !== 0) {
          $columnList .= ", ";
          $valueList .= ", ";
        }
        $columnList .= $key;
        $valueList .= $this->bindings->push($value, "values");
      }

      return "SELECT count(*) FROM " . $this->cmd_from . $this->cmd_join . $this->cmd_filter;
    }

    if (strtoupper($this->cmd_type) == "DELETE") {
      $query = "SELECT ";
      $this->bind_params = new QueryBuilder\BindCollector();

      return "DELETE " . $this->tableMap[""] . " \n FROM " . $this->cmd_from . $this->cmd_join . $this->cmd_filter;
    }

    if (strtoupper($this->cmd_type) == "UPDATE") {
      $this->bindings->reset("values");

      $setList = "";
      foreach ($this->values->getFields() as $index => $key) {
        if ($index++ !== 0) $setList .= ", ";
        $value = $this->values->getValue($key, 0);
        // Exceptions is stored to indicate it has to be overwritten
        if ($value instanceof Exception)
          throw $value;

        $setList .= $key . "=" . $this->bindings->push($value, "values");
      }

      return "UPDATE " . $this->cmd_from . $this->cmd_join . " SET " . $setList . " \n " .  $this->cmd_filter;
    }

    if (strtoupper($this->cmd_type) == "INSERT") {
      $this->bindings->reset("values");

      $columnList = "";
      $valueList = "";
      foreach ($this->values->getFields() as $index => $key) {
        if ($index++ !== 0) $columnList .= ", ";
        $columnList .= $key;
      }
      $entityCount = $this->values->getEntityCount();
      for ($eid = 0; $eid < $entityCount; $eid++) {
        if ($eid !== 0) $valueList .= ", ";
        $valueList .= "(";
        foreach ($this->values->getFields() as $index => $key) {
          if ($index++ !== 0) $valueList .= ", ";
          $value = $this->values->getValue($key, $eid);
          // Exceptions is stored to indicate it has to be overwritten
          if ($value instanceof Exception)
            throw $value;
          $valueList .= $this->bindings->push($value, "values");
        }
        $valueList .= ")";
      }

      return "INSERT INTO " . $this->tableMap[""] . " (" . $columnList . ") \n VALUES " . $valueList . " \n";
    }

    if ($this->cmd_type == "CREATE_TABLE") {
      $fieldList = "";
      foreach ($this->fields as $key => $field) {
        if (!$field["source"]) continue;
        if (strpos($field["alias"], ".") !== strrpos($field["alias"], ".")) continue;
        if ($field["field"]->isVirtual()) continue;
        $fn = $field['field']->getFieldName($key);
        $tp = $field['field']->getSQLType();

        if (!$fn || !$tp) continue;

        if ($fieldList) $fieldList .= ", \n";
        $fieldList .= "`" . $fn . "` " . $tp;
      }
      return "CREATE TABLE " . $this->tableMap[""] . " (" . $fieldList . ")";
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

    if (!$this->resolve($rel)) return;
    if ($this->fields[$rel]["field"] && $this->fields[$rel]["field"]->isJoinable()) {
      if (!isset($this->joins[$rel])) {
        $this->join($rel, false);
      }

      if (isset($this->joins[$rel]->fields[$fn])) {
        $field = $this->joins[$rel]->fields[$fn];

        if ($field->isVirtual()) {
          $this->fields[$key] =  array(
            "source" => false,
            "alias" => $key,
            "field" => $field,
            "hidden" => true,
          );
          return null;
        }

        $fieldName = $field->getFieldName($fn);

        $this->fields[$key] =  array(
          "source" => "" . $this->tableMap[$rel] . ".`" . $fieldName . "`",
          "alias" => $key,
          "field" => $field,
          "hidden" => true,
        );

        return $this->fields[$key]["source"];
      }
    }
    throw new Exception("Field " . $key . " clould not be resolved");
  }

  private function composeRawFilterByKey($key, $value, $isRoot = false) {
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
    } else if ($key === "_sort" && $isRoot) {
      if (is_string($value))
        $this->addOrderBy($value);
      else if (is_array($value)) {
        foreach ($value as $v)
          $this->addOrderBy($v);
      }
    } else if ($key === "_limit" && $isRoot) {
      $this->setLimit($value);
    } else if ($key === "_start" && $isRoot) {
      $this->setOffset($value);
    } else if (str_ends_with($key, "_ne"))
      return ["<>", new QueryBuilder\QB_Ref(str_drop_end($key, 3)), $value];
    else if (str_ends_with($key, "_ge"))
      return [">=", new QueryBuilder\QB_Ref(str_drop_end($key, 3)), $value];
    else if (str_ends_with($key, "_le"))
      return ["<=", new QueryBuilder\QB_Ref(str_drop_end($key, 3)), $value];
    else if (str_ends_with($key, "_gt"))
      return [">", new QueryBuilder\QB_Ref(str_drop_end($key, 3)), $value];
    else if (str_ends_with($key, "_lt"))
      return ["<", new QueryBuilder\QB_Ref(str_drop_end($key, 3)), $value];
    else if (str_ends_with($key, "_in"))
      return ["in", new QueryBuilder\QB_Ref(str_drop_end($key, 3)), ...$value];
    else if (str_ends_with($key, "_notin"))
      return ["notin", new QueryBuilder\QB_Ref(str_drop_end($key, 6)), ...$value];
    else if (str_ends_with($key, "_like"))
      return ["like", new QueryBuilder\QB_Ref(str_drop_end($key, 5)), $value];
    else if (str_ends_with($key, "_notlike"))
      return ["notlike", new QueryBuilder\QB_Ref(str_drop_end($key, 5)), $value];
    else if (str_ends_with($key, "_null"))
      return [$value ? "null" : "notnull", new QueryBuilder\QB_Ref(str_drop_end($key, 5))];
    else if (str_ends_with($key, "_sort"))
      $this->addOrderByEq(str_drop_end($key, 5), $value);
    else return ["=", new QueryBuilder\QB_Ref($key), $value];
    return false;
  }

  private function composeRawFilterByObject($query, $isRoot = false) {
    if (count($query) == 0) return false;
    $args = ["and"];
    foreach ($query as $key => $value) {
      $sq = $this->composeRawFilterByKey($key, $value, $isRoot);
      if ($sq) $args[] = $sq;
    }

    if (count($args) == 2) return $args[1];
    if (count($args) == 1) return false;
    return $args;
  }

  private function composeFilterValue($value) {
    if ($value instanceof QueryBuilder\QB_Ref) {
      $resolved = $this->resolve($value->alias);
      if (!$resolved) throw new Exception("Field '" . $value->alias . "' could not be resolved.");
      return $resolved;
    }
    return $this->bindings->push($value, "filter");
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
    $fieldFilters = [];

    foreach ($fields as $key => $field) {
      if (!$field) continue;

      $fieldName = $field->getFieldName($key);
      $alias = $prefix . ($prefix != "" ?  "." : "") . $key;
      $source = $this->tableMap[$prefix] . ".`" . $fieldName . "`";

      if ($prefix == "" && $this->cmd_type !== "INSERT") {
        $filter = $field->getFilter($key, $prefix, $alias, $this->cmd_type);
        if ($filter) $fieldFilters[] = $filter;
      }

      if ($field->isVirtual()) {
        $this->fields[$alias] =  array(
          "source" => false,
          "alias" => $alias,
          "field" => $field,
          "hidden" => true,
        );
        continue;
      }

      $this->fields[$alias] =  array(
        "source" => $source,
        "alias" => $alias,
        "field" => $field,
        "hidden" => false,
      );

      // default and forceUpdated field values
      if ($prefix == "") {
        $def = $this->cmd_type == "INSERT" ?  $field->getDefault() : null;
        if ($this->cmd_type == "INSERT" && !($def instanceof Phapi\Model\Fields\IgnoreField)) {
          $this->values->setDefaultValue($source, $field->onSave($def));
        } else if (($this->cmd_type == "INSERT" || $this->cmd_type == "UPDATE") && $field->forceUpdate()) {
          $this->values->setDefaultValue($source, $field->onSave(null));
        } else if ($this->cmd_type == "INSERT" && $field->isRequired()) {
          $this->values->setDefaultValue($source, new Exception("Field '" . $alias . "' is required."));
        }
      }
    }
    foreach ($fieldFilters as $filter) {
      $this->addRawFilter(...$filter);
    }
  }
}
