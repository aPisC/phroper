<?php


class Model {
  public function allowDefaultService() {
    return true;
  }

  public static function getModel($modelName) {
    try {
      if (is_subclass_of($modelName, 'Model'))
        return $modelName;
      $mcn = 'Models\\' . ucfirst($modelName);
      $model = new $mcn();
      return $model;
    } catch (Error $e) {
      return null;
    }
  }

  public function getName() {
    return strtolower(str_replace('\\', '_', get_class($this)));
  }

  function getTableName() {
    return $this->tableName;
  }

  function getPopulateList($populate = null) {
    if (is_array($populate)) return $populate;

    $populate = [];
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;
      if ($field instanceof Model\Fields\Relation && $field->isDefaultPopulated())
        $populate[] = $key;
    }
    return $populate;
  }

  protected string $tableName;
  public array $fields = array();

  public function __construct($tableName = null) {
    if ($tableName == null)
      $tableName = strtolower(end(explode('/', get_class($this))));
    $this->tableName = $tableName;
    $this->fields["id"] = new Model\Fields\Integer();
    $this->fields["updated_by"] = new Model\Fields\UpdatedBy();
    $this->fields["created_at"] = new Model\Fields\CreatedAt();
    $this->fields["updated_at"] = new Model\Fields\UpdatedAt();
  }

  public function sanitizeEntity($entity) {
    if ($entity == null) return null;
    if (is_scalar($entity)) return $entity;
    $ne = array();
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;
      if (!array_key_exists($key, $entity)) continue;
      $sv = $field->getSanitizedValue($entity[$key]);
      if (!($sv instanceof Model\Fields\IgnoreField))
        $ne[$key] = $sv;
    }
    return $ne;
  }

  public function restoreEntity($assoc, $populate, $prefix = "") {
    $entity = array();

    // First of all, restore id, because it may be required for relation loading
    $memberName = $prefix == "" ? "id" : ($prefix . ".id");
    if (isset($assoc[$memberName]))
      $entity["id"] = $assoc[$memberName];
    else if (isset($assoc[$prefix])) return $assoc[$prefix];
    else if (isset($this->fields["id"])) return null;

    // Restore other fields
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;

      $v = null;
      $memberName = $prefix == "" ? $key : ($prefix . "." . $key);

      if (array_key_exists($memberName, $assoc))
        $v = $field->onLoad($assoc[$memberName], $memberName, $assoc, $populate);
      else if ($field->isVirtual()) {
        $v = $field->onLoad($entity["id"], $memberName, $assoc, $populate);
      }
      if (!($v instanceof Model\Fields\IgnoreField)) $entity[$key] = $v;
    }
    return $entity;
  }

  public static function fieldValueProcessor($value, $type) {
    if ($type == 'password') return password_hash($value, PASSWORD_BCRYPT);
    else if ($type == 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
      throw new Exception("Email format is invalid");
    }
    return $value;
  }


  //--------------------------
  // Data managing functions
  //--------------------------

  protected function preUpdate($entity) {
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;
      if (array_key_exists($key, $entity))
        $field->preUpdate($entity[$key], $key, $entity);
      else if ($field->forceUpdate())
        $field->preUpdate(null, $key, $entity);
    }
  }
  protected function postUpdate($entity, $updated) {
    $hadPostUpdate = false;
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;
      if (array_key_exists($key, $entity))
        $hadPostUpdate = $hadPostUpdate || $field->postUpdate($entity[$key], $key, $updated);
      else if ($field->forceUpdate())
        $hadPostUpdate = $hadPostUpdate || $field->postUpdate(null, $key, $updated);
    }
    return $hadPostUpdate;
  }

  private function useFilter($q, $filter) {
    if (is_array($filter) && count($filter) > 0) {
      $q->addQueryObject($filter);
    } else if (is_string($filter) || is_numeric($filter)) {
      $q->addRawFilter("=", new QueryBuilder\QB_Ref('id'), $filter);
    }
  }

  public function findOne($filter, $populate = null) {
    $populate = $this->getPopulateList($populate);
    $q = new QueryBuilder($this, "select");
    $mysqli = Database::instance();

    $q->populate($populate);

    $this->useFilter($q, $filter);
    $result = $q->execute($mysqli);

    if (!$result) {
      error_log($q->lastSql . '    ' . $mysqli->error);
      throw new Exception('Database error ' . $mysqli->error);
    }
    if ($result->num_rows == 0) {
      $result->free_result();
      return null;
    }

    $assoc = $result->fetch_assoc();
    $result->free_result();

    return $this->restoreEntity($assoc, $populate);
  }

  public function find($filter, $populate = null) {
    $populate = $this->getPopulateList($populate);
    $q = new QueryBuilder($this, "select");
    $mysqli = Database::instance();

    $q->populate($populate);

    $this->useFilter($q, $filter);

    $result = $q->execute($mysqli);

    if (!$result) {
      error_log($q->lastSql . '    ' . $mysqli->error);
      throw new Exception('Database error ' . $mysqli->error);
    }

    if ($result->num_rows == 0) {
      $result->free_result();
      return [];
    }

    $assocs = $result->fetch_all(MYSQLI_ASSOC);
    $result->free_result();
    $model = $this;
    return array_map(function ($assoc) use ($populate, $model) {
      return $model->restoreEntity($assoc, $populate);
    }, $assocs);
  }

  public function count($filter) {
    $q = new QueryBuilder($this, "count");
    $mysqli = Database::instance();

    $this->useFilter($q, $filter);

    $result = $q->execute($mysqli);

    if (!$result) {
      error_log($q->lastSql . '    ' . $mysqli->error);
      throw new Exception('Database error ' . $mysqli->error);
    }
    if ($result->num_rows == 0) {
      $result->free_result();
      return null;
    }

    $entity = $result->fetch_array();
    $result->free_result();
    return $entity[0];
  }

  public function create($entity) {
    return $this->createMulti([$entity])[0];
  }

  public function createMulti($entities) {
    $q = new QueryBuilder($this, "insert");
    $mysqli = Database::instance();

    foreach ($entities as $entity) {
      $this->preUpdate($entity);
    }

    foreach ($entities as $index => $entity) {
      if ($index > 0) $q->nextInsert();
      $q->setAllValue($entity);
    }

    $result = $q->execute($mysqli);

    if (!$result) {
      error_log($q->lastSql . '    ' . $mysqli->error);
      throw new Exception('Database error ' . $mysqli->error);
    }

    $updated = $entities;
    $insid = $mysqli->insert_id;
    if (isset($this->fields["id"])) {
      $updated = $this->find([
        "_limit" => count($entities),
        "id_ge" => $insid
      ]);
    }

    $hadPostUpdate = false;
    foreach ($entities as $i => $entity) {
      $hadPostUpdate = $hadPostUpdate || $this->postUpdate($entity, $updated[$i]);
    }

    if ($hadPostUpdate && isset($this->fields["id"])) {
      $updated = $this->find([
        "_limit" => count($entities),
        "id_ge" => $insid
      ], []);
    }

    return $updated;
  }

  public function update($filter, $entity) {
    $q = new QueryBuilder($this, "update");
    $mysqli = Database::instance();

    $this->preUpdate($entity);

    $this->useFilter($q, $filter);
    $q->setAllValue($entity);

    $result = $q->execute($mysqli);

    if (!$result) {
      error_log($q->lastSql . '    ' . $mysqli->error);
      throw new Exception('Database error ' . $mysqli->error);
    }

    $updated = $this->find($filter);

    $hadPostUpdate = false;
    foreach ($updated as $u) {
      $hadPostUpdate = $hadPostUpdate || $this->postUpdate($entity, $u);
    }

    if ($hadPostUpdate) $updated = $this->find($filter);

    if (count($updated) == 0) return null;
    if (count($updated) == 1) return $updated[0];

    return $updated;
  }

  public function delete($filter, $returnEntities = true) {
    $entity = $returnEntities ?  $this->find($filter) : null;

    if ($entity || !$returnEntities) {
      $q = new QueryBuilder($this, "delete");
      $mysqli = Database::instance();

      $this->useFilter($q, $filter);


      $result = $q->execute($mysqli);

      if (!$result) {
        error_log($q->lastSql . '    ' . $mysqli->error);
        throw new Exception('Database error ' . $mysqli->error);
      }
    }
    if (!$returnEntities || count($entity) == 0) return null;
    if (count($entity) == 1) return $entity[0];
    return $entity;
  }
}
