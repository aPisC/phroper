<?php 
class QueryBuilder {
  private ?mysqli $mysqli;
  private array $filters = array();
  private array $fields = array();
  private array $values = array();

  private string $tableName;

  function __construct($tableName,$mysqli = null) {
    if($mysqli == null) $mysqli = Database::instance();
    
    $this->mysqli = $mysqli;
    $this->tableName = $tableName;
  }

  public function addField($f){
    array_push($this->fields, $f);
    return $this;
  }
  public function getSelectQuery(){
    $query = 'SELECT ' . join(' ,', $this->fields) . ' FROM ' . $this->tableName;
    if(count($this->filters) > 0)
      $query = $query . ' WHERE ' . $this->getFiltersString();

    return $query;
  }    
  public function getCountQuery(){
    $query = 'SELECT count(*) FROM ' . $this->tableName;
    if(count($this->filters) > 0)
      $query = $query . ' WHERE ' . $this->getFiltersString();
    return $query;
  }    
  public function getSelectOneQuery(){
    $query = 'SELECT ' . join(' ,', $this->fields) . ' FROM ' . $this->tableName;
    if(count($this->filters) > 0)
      $query = $query . ' WHERE ' . $this->getFiltersString();
    $query = $query . ' LIMIT 1';

    return $query;
  }  

  public function getDeleteQuery(){
    $query = 'DELETE FROM ' . $this->tableName;
    if(count($this->filters) > 0)
      $query = $query . ' WHERE ' . $this->getFiltersString();

    return $query;
  }

  public function getInsertQuery(){
    $query = 'INSERT INTO ' . $this->tableName . '( ' . join(' ,', $this->fields) . ' ) VALUES ( ' . $this->getInsertValuesString() . ' );';
    return $query;
  }

  public function getUpdateQuery(){
    if(count($this->values) == 0)
      return null;

    $query = 'UPDATE ' . $this->tableName;
    $query = $query . ' SET ' . $this->getUpdateFieldsString();
    if(count($this->filters) > 0)
      $query = $query . ' WHERE ' . $this->getFiltersString();

    return $query;
  }

  public function setValues($values){
    $this->values = $values;
    return $this;
  }


  private function getUpdateFieldsString(){
      $mysqli = $this->mysqli;
      $v2 = array_map(function($v, $f) use ($mysqli){
        if(is_string($v))
          $v = "'". mysqli_real_escape_string($mysqli, $v). "'";
        return $f . ' = ' . $v;
      }, $this->values, $this->fields);
      return join(', ', $v2);
  }


  private function getInsertValuesString(){
    $mysqli = $this->mysqli;
    $v2 = array_map(function($v) use ($mysqli){
      if($v == null)
       return 'NULL';
      if(is_string($v))
        $v = "'". mysqli_real_escape_string($mysqli, $v). "'";
      return $v;
    }, $this->values);
    return join(', ', $v2);
  }

  private function getFiltersString(){
    return join(' ', $this->filters);
  }

  public function clearQuery(){
    $this->filters = array();
    $this->values = array();
    return $this;
  }

  public function where($field, $check = null, $value = null){
    // Grouped filters
    if(is_callable($field)){
      array_push($this->filters, '(');
      $field($this);
      array_push($this->filters, ')');
    }
    else{
      if($check == null && $value == null){
        array_push($this->filters, $field);
      }
      else if($value == null){
        $value = $check;
        if(is_string($value))
          $value = "'" . mysqli_real_escape_string($this->mysqli, $value) . "'";
        array_push($this->filters, $field . ' = ' . $value);
      }
      else{
        if(is_string($value))
          $value = "'" . mysqli_real_escape_string($this->mysqli, $value) . "'";
        array_push($this->filters, $field . ' ' .$check . ' ' . $value);
      }
    }
    return $this;
  }

  public function andWhere($field, $check = null, $value = null){
    array_push($this->filters, 'AND');
    return $this->where($field, $check, $value);
  }  
  public function orWhere($field, $check = null, $value = null){
    array_push($this->filters, 'OR');
    return $this->where($field, $check, $value);
  }


}

?>