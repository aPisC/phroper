<?php
  class Model {
    public function allowDefaultService(){
      return true;
    }

    public static function getModel($modelName){
      $mcn= 'Models\\'. ucfirst($modelName);
      $model = new $mcn();
      return $model;
    }

    protected string $tableName;
    protected array $fields = array(
      'id' => array(
        'type' => 'int',
        'sqltype' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
      )
    );

    public function __construct($tableName) {
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

    public function populateEntity($entity, $populate = null){
      if($populate == null) {
        if(is_array($populate)) return $entity;
        $populate = array_keys($this->fields);
      }

      foreach ($populate as $key) {
        if(isset($this->fields[$key]) && $this->fields[$key]['type'] == 'relation'){
          $field = $this->fields[$key];
          $model = Model::getModel($field['model']);
          if(isset($field['via'])){
            $entity[$key] = $model->find(array($field['via']=> $entity['id']), []);
          }
          else if (isset($entity[$key]) && is_scalar($entity[$key]))
            $entity[$key] = $model->findOne($entity[$key], []);
        }
      }
      return $entity;
    }

    public static function fieldValueProcessor($value, $type){
      if($type == 'password') return password_hash($value, PASSWORD_BCRYPT);
      return $value;
    }


    //--------------------------
    // Data managing functions
    //--------------------------
    protected function getQueryBuilder($isSelect = true, $entity = null){
      $q = new QueryBuilder($this->tableName);
      $values = array();
      foreach ($this->fields as $key => $field) {
        if($field['type'] == 'relation' && isset($field['via'])) continue;
        if(!$isSelect && isset($field['virtual']) && $field['virtual']) continue;
        if(is_array($entity) && !isset($entity[$key])) continue;


        $fieldName = isset($field['field']) 
          ? ($field['field']) . ($isSelect ? ' as ' . $key : '')
          : $key;
        $q->addField($fieldName);
        if(is_array($entity)) array_push($values, self::fieldValueProcessor($entity[$key], $field['type']));
      }
      $q->setValues($values);

      return $q;
    }

    private function useFilter($q, $filter){
      if(is_array($filter)){
        $isFirst = true;
        foreach ($filter as $key => $value) {
          if(isset($this->fields[$key]['field']))
            $key = $this->fields[$key]['field'];
          if($isFirst) $q->where($key, $value);
          else $q->andWhere($key, $value);
          
          $isFirst = false;
        }
      }
      else if(is_string($filter) || is_numeric($filter)){
        $q->where('id', $filter);
      }
      else if (is_callable($filter)){
        $filter($q);
      }
      else {
        $q->where('id', $filter);
      }
    }

    public function findOne($filter, $populate = null){
      $q = $this->getQueryBuilder();
      $mysqli = Database::instance();

      $this->useFilter($q, $filter);
      $sql = $q->getSelectOneQuery();
      $result = $mysqli->query($sql);
    
      if(!$result){
        error_log($sql . '    ' . $mysqli->error);
        throw new Exception('Database error ' . $mysqli->error);
      }
      if( $result->num_rows == 0){
        $result->free_result();
        return null;
      }

      $entity = $result->fetch_assoc();
      $result->free_result();
      //return $entity;
      return $this->populateEntity($entity, $populate);
    }

    public function find($filter, $populate = null){
      $q = $this->getQueryBuilder();
      $mysqli = Database::instance();

      $this->useFilter($q, $filter);
      $sql = $q->getSelectQuery();
      $result = $mysqli->query($sql);
      if(!$result){
        error_log($sql . '    ' . $mysqli->error);
        throw new Exception('Database error ' . $mysqli->error);
      }
      if( $result->num_rows == 0){
        $result->free_result();
        return null;
      }

      $entity = $result->fetch_all(MYSQLI_ASSOC);
      $result->free_result();
      $model = $this;
      return array_map(function($entity) use ($populate, $model) {return $model->populateEntity($entity, $populate);}, $entity);
    }    

    public function count($filter){
      $q = $this->getQueryBuilder();
      $mysqli = Database::instance();

      if($filter) $this->useFilter($q, $filter);
      $sql = $q->getCountQuery();
      $result = $mysqli->query($sql);
      
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
      $q = $this->getQueryBuilder(false, $entity);
      $mysqli = Database::instance();
      
      $sql = $q->getInsertQuery();
      $result = $mysqli->query($sql);
      

      if(!$result){
        error_log($sql . '    ' . $mysqli->error);
        throw new Exception('Database error ' . $mysqli->error);
      }
      return $this->findOne($mysqli->insert_id);
    }

    public function update($filter, $entity){
      $q = $this->getQueryBuilder(false, $entity);
      $mysqli = Database::instance();

      $this->useFilter($q, $filter);
      $sql = $q->getUpdateQuery();
      if($sql != null){
        $result = $mysqli->query($sql);
 
        if(!$result){
          error_log($sql . '    ' . $mysqli->error);
          throw new Exception('Database error ' . $mysqli->error);
        }
      }
      return $this->findOne($filter);
    }

    public function delete($filter){
      $entity = $this->findOne($filter);

      if($entity != null){
        $q = $this->getQueryBuilder();
        $mysqli = Database::instance();
        
        $this->useFilter($q, $filter);
        $sql = $q->getDeleteQuery();

        $result = $mysqli->query($sql);

        if(!$result){
          error_log($sql . '    ' . $mysqli->error);
          throw new Exception('Database error ' . $mysqli->error);
        }
      }
      return $entity;
    }
  }
?>