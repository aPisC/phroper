<?php
  class Model {
    public function allowDefaultService(){
      return true;
    }

    public static function getModel($modelName){
      if(is_subclass_of($modelName, 'Model'))
        return $modelName;
      $mcn= 'Models\\'. ucfirst($modelName);
      $model = new $mcn();
      return $model;
    }

    function getTableName() {
      return $this->tableName;
    }

    protected string $tableName;
    public array $fields = array(
      'id' => array(
        'type' => 'int',
        'sqltype' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
      )
    );

    public function __construct($tableName = null) {
      if($tableName == null)
        $tableName = strtolower(end(explode('/', get_class($this))));
      $this->tableName = $tableName;
    }

    public function sanitizeEntity($entity){
      if($entity == null) return $entity;
      $ne = array();
      foreach ($this->fields as $key => $field) {
        if(isset($entity[$key]) && (!isset($field['private']) ||  !$field['private']))
        {
          if(is_array($entity[$key]) && $field['type'] == 'relation' && !isset($field['via'])){
            $model = Model::getModel($field['model']);
            $ne[$key] = $model->sanitizeEntity($entity[$key]);
          }
          else if(is_array($entity[$key]) && $field['type'] == 'relation' && isset($field['via'])){
            $model = Model::getModel($field['model']);
            $ne[$key] = array_map(function($entity) use ($model) {return $model->sanitizeEntity($entity);}, ($entity[$key]));
          }
          else
            $ne[$key] = $entity[$key];
        }
      }
      return $ne;
    }

    public function restoreEntity($assoc, $populate, $prefix=""){
      $entity = array();

      // First of all, restore id, because it may be required for relation loading
      $memberName = $prefix == "" ? "id" : ($prefix . ".id" );
      if(isset($assoc[$memberName]))
        $entity["id"] = $assoc[$memberName];
      else if (isset($assoc[$prefix])) return $assoc[$prefix];
      else return null;

      // Restore other fields
      foreach ($this->fields as $key => $field) {
        $memberName = $prefix == "" ? $key : ($prefix . "." . $key);
        if(isset($assoc[$memberName]))
          $entity[$key] = $assoc[$memberName];
        
        if(!$populate || !in_array($memberName, $populate) || $field["type"] !== "relation") 
          continue;

        $model = Model::getModel($field['model']);

        if(!isset($field["via"])){
          $entity[$key] = $model->restoreEntity($assoc, $populate, $memberName);
        }
        else{
          $entity[$key] = $model->find(array($field['via']=> $entity['id']));
        }
      }
      return $entity;
    }

    public static function fieldValueProcessor($value, $type){
      if($type == 'password') return password_hash($value, PASSWORD_BCRYPT);
      else if($type == 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email format is invalid");
      }
      return $value;
    }


    //--------------------------
    // Data managing functions
    //--------------------------

    private function useFilter($q, $filter){
      if(is_array($filter)){
        foreach ($filter as $key => $value) {
          $q->addFilter("=", $q->ref($key), $value);
        }
      }
      else if(is_string($filter) || is_numeric($filter)){
        $q->addFilter("=", $q->ref('id'), $filter);
      }
    }

    public function findOne($filter, $populate = null){
      $q = new QueryBuilder($this, "select");
      $mysqli = Database::instance();

      if($populate == null && !is_array($populate)) {
        $populate = array_keys($this->fields);
      }
      $q->populate($populate);

      $this->useFilter($q, $filter);
      $result = $q->execute($mysqli);
    
      if(!$result){
        error_log($q->lastSql . '    ' . $mysqli->error);
        throw new Exception('Database error ' . $mysqli->error);
      }
      if($result->num_rows == 0){
        $result->free_result();
        return null;
      }

      $assoc = $result->fetch_assoc();
      $result->free_result();

      return $this->restoreEntity($assoc, $populate);
    }

    public function find($filter, $populate = null){
      $q = new QueryBuilder($this, "select");
      $mysqli = Database::instance();

      if($populate == null && !is_array($populate)) {
        $populate = array_keys($this->fields);
      }
      $q->populate($populate);

      $this->useFilter($q, $filter);
      
      $result = $q->execute($mysqli);
      
      if(!$result){
        error_log($q->lastSql . '    ' . $mysqli->error);
        throw new Exception('Database error ' . $mysqli->error);
      }

      if( $result->num_rows == 0){
        $result->free_result();
        return [];
      }

      $assocs = $result->fetch_all(MYSQLI_ASSOC);
      $result->free_result();
      $model = $this;
      return array_map(function($assoc) use ($populate, $model) {return $model->restoreEntity($assoc, $populate);}, $assocs);
    }    

    public function count($filter){
      $q = new QueryBuilder($this, "count");
      $mysqli = Database::instance();

      $this->useFilter($q, $filter);
      
      $result = $q->execute($mysqli);

      if(!$result){
        error_log($sql . '    ' . $mysqli->error);
        throw new Exception('Database error ' . $mysqli->error);
      }
      if( $result->num_rows == 0){
        $result->free_result();
        return null;
      }

      $entity = $result->fetch_array();
      $result->free_result();
      return $entity[0];
    }

    public function create($entity){
      $q = new QueryBuilder($this, "insert");
      $mysqli = Database::instance();

      $query = $q->setAllValue($entity, false);

      $result = $q->execute($mysqli);
      
      if(!$result){
        error_log($sql . '    ' . $mysqli->error);
        throw new Exception('Database error ' . $mysqli->error);
      }
      return $this->findOne($mysqli->insert_id);
    }

    public function update($filter, $entity){
      $q = new QueryBuilder($this, "update");
      $mysqli = Database::instance();

      $this->useFilter($q, $filter);
      
      $q->setAllValue($entity);

      $result = $q->execute($mysqli);

      if(!$result){
        error_log($sql . '    ' . $mysqli->error);
        throw new Exception('Database error ' . $mysqli->error);
      }

      return $this->findOne($filter);
    }

    public function delete($filter){
      $entity = $this->find($filter);

      if($entity != null){
        $q = new QueryBuilder($this, "delete");
        $mysqli = Database::instance();
        
        $this->useFilter($q, $filter);


        $result = $q->execute($mysqli);

        if(!$result){
          error_log($sql . '    ' . $mysqli->error);
          throw new Exception('Database error ' . $mysqli->error);
        }
      }
      if(count($entity) == 0) return null;
      if(count($entity) == 1) return $entity[0];
      return $entity;
    }
  }
?>