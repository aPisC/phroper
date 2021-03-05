<?php
class Model {
  public function allowDefaultService() {
    return true;
  }

  public static function getModel($modelName) {
    if (is_subclass_of($modelName, 'Model'))
      return $modelName;
    $mcn = 'Models\\' . ucfirst($modelName);
    $model = new $mcn();
    return $model;
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
  }

  public function sanitizeEntity($entity) {
    if ($entity == null) return $entity;
    $ne = array();
    foreach ($this->fields as $key => $field) {
      if (array_key_exists($key, $entity) && !$field->isPrivate()) {
        if (is_array($entity[$key]) && $field instanceof Model\Fields\RelationToOne) {
          $model = $field->getModel();
          $ne[$key] = $model->sanitizeEntity($entity[$key]);
        } else if (is_array($entity[$key]) && $field instanceof Model\Fields\RelationToMany) {
          $model = $field->getModel();
          $ne[$key] = array_map(function ($entity) use ($model) {
            return $model->sanitizeEntity($entity);
          }, ($entity[$key]));
        } else
          $ne[$key] = $entity[$key];
      }
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
    else return null;

    // Restore other fields
    foreach ($this->fields as $key => $field) {
      $memberName = $prefix == "" ? $key : ($prefix . "." . $key);
      if (isset($assoc[$memberName]))
        $entity[$key] = $field->onLoad($assoc[$memberName]);
      else if (!($field instanceof Model\Fields\RelationToMany)) $entity[$key] = null;

      if (!$populate || !in_array($memberName, $populate) || !($field instanceof Model\Fields\Relation))
        continue;

      $model = $field->getModel();

      if ($field instanceof Model\Fields\RelationToOne && isset($entity[$key]) && $entity[$key] != null) {
        $entity[$key] = $model->restoreEntity($assoc, $populate, $memberName);
      } else if ($field instanceof Model\Fields\RelationToMany) {
        $pop2 = array_filter($populate, function ($value) use ($memberName) {
          return str_starts_with($value, $memberName . ".");
        });
        $pop2 = array_map(function ($value) use ($memberName) {
          return substr($value, strlen($memberName) + 1);
        }, $pop2);
        $entity[$key] = $model->find([$field->getVia() => $entity['id']], $pop2);
      }
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


  private function useFilter($q, $filter) {
    if (is_array($filter) && count($filter) > 0) {
      $q->addQueryObject($filter);
    } else if (is_string($filter) || is_numeric($filter)) {
      $q->addRawFilter("=", new QB_Ref('id'), $filter);
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
    $q = new QueryBuilder($this, "insert");
    $mysqli = Database::instance();

    $query = $q->setAllValue($entity);

    $result = $q->execute($mysqli);

    if (!$result) {
      error_log($q->lastSql . '    ' . $mysqli->error);
      throw new Exception('Database error ' . $mysqli->error);
    }
    return $this->findOne($mysqli->insert_id);
  }

  public function update($filter, $entity) {
    $q = new QueryBuilder($this, "update");
    $mysqli = Database::instance();

    $this->useFilter($q, $filter);

    $q->setAllValue($entity);

    $result = $q->execute($mysqli);

    if (!$result) {
      error_log($q->lastSql . '    ' . $mysqli->error);
      throw new Exception('Database error ' . $mysqli->error);
    }

    return $this->findOne($filter);
  }

  public function delete($filter) {
    $entity = $this->find($filter);

    if ($entity != null) {
      $q = new QueryBuilder($this, "delete");
      $mysqli = Database::instance();

      $this->useFilter($q, $filter);


      $result = $q->execute($mysqli);

      if (!$result) {
        error_log($q->lastSql . '    ' . $mysqli->error);
        throw new Exception('Database error ' . $mysqli->error);
      }
    }
    if (count($entity) == 0) return null;
    if (count($entity) == 1) return $entity[0];
    return $entity;
  }
}
