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
        'field' => 'id',
        'type' => 'int',
        'type_size' => '6',
        'dbparams' => 'UNSIGNED AUTO_INCREMENT PRIMARY KEY'
      )
    );

    public function __construct($tableName) {
      $this->tableName = $tableName;
    }

    protected function getQueryBuilder($isSelect = true, $entry = null){
      $q = new QueryBuilder($this->tableName);
      $values = array();
      foreach ($this->fields as $key => $field) {
        if(!$isSelect && isset($field['virtual']) && $field['virtual']) continue;
        if(is_array($entry) && !isset($entry[$key])) continue;


        $fieldName = isset($field['field']) 
          ? ($field['field']) . ($isSelect ? ' as ' . $key : '')
          : $key;
        $q->addField($fieldName);
        if(is_array($entry)) array_push($values, $entry[$key]);
      }
      $q->setValues($values);

      return $q;
    }

    private static function useFilter($q, $filter){
      if(is_array($filter)){
        $isFirst = true;
        foreach ($filter as $key => $value) {
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

    public function findOne($filter){
      $q = $this->getQueryBuilder();
      $mysqli = Database::instance();

      self::useFilter($q, $filter);
      $sql = $q->getSelectOneQuery();
      $result = $mysqli->query($sql);

      if(!$result) return null;
      if( $result->num_rows == 0){
        $result->free_result();
        return null;
      }

      $entity = $result->fetch_assoc();
      $result->free_result();
      return $entity;
    }

    public function find($filter){
      $q = $this->getQueryBuilder();
      $mysqli = Database::instance();

      self::useFilter($q, $filter);
      $sql = $q->getSelectQuery();
      $result = $mysqli->query($sql);
      

      if(!$result) return null;
      if( $result->num_rows == 0){
        $result->free_result();
        return null;
      }

      $entity = $result->fetch_all(MYSQLI_ASSOC);
      $result->free_result();
      return $entity;
    }    

    public function count($filter){
      $q = $this->getQueryBuilder();
      $mysqli = Database::instance();

      if($filter) self::useFilter($q, $filter);
      $sql = $q->getCountQuery();
      $result = $mysqli->query($sql);
      

      if(!$result) return null;
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

      self::useFilter($q, $filter);
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
      $entry = $this->findOne($filter);

      if($entry != null){
        $q = $this->getQueryBuilder();
        $mysqli = Database::instance();
        
        self::useFilter($q, $filter);
        $sql = $q->getDeleteQuery();

        $result = $mysqli->query($sql);

        if(!$result){
          error_log($sql . '    ' . $mysqli->error);
          throw new Exception('Database error ' . $mysqli->error);
        }
      }
      return $entry;
    }
  }
?>