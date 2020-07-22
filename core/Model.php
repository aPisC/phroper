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
      ),
      'idp' => array(
        'field' => 'id + 3',
        'virtual' => true
      )
    );

    public function __construct($tableName) {
      $this->tableName = $tableName;
    }

    protected function getQueryBuilder($isSelect = true){
      $q = new QueryBuilder($this->tableName);

      foreach ($this->fields as $key => $field) {
        if(!$isSelect && isset($field['virtual']) && $field['virtual']) continue;

        $fieldName = isset($field['field']) 
          ? ($field['field']) . ($isSelect ? ' as ' . $key : '')
          : $key;
        $q->addField($fieldName);
      }

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
      else if (is_callable($filter)){
        $filter($q);
      }
      else{
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

    public function create($entity){
      $q = $this->getQueryBuilder(false);
      $mysqli = Database::instance();

      $values = array();
      foreach ($this->fields as $key => $field) {
        if(isset($field['virtual']) && $field['virtual']) continue;

        $fn = isset($field['field']) ? $field['field'] : $key;
        $values[$fn] = isset($entity[$key]) ? $entity[$key] : null;
      }
      $q->setValues($values);

      $sql = $q->getInsertQuery();
      $result = $mysqli->query($sql);
      

      if(!$result) return null;
      return $this->findOne($mysqli->insert_id);
    }

    public function update($filter, $entity){
      $q = $this->getQueryBuilder();
      $mysqli = Database::instance();

      self::useFilter($q, $filter);
    }
  }
?>