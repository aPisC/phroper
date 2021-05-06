<?php

namespace Phroper\Model;

use ArrayAccess;
use ArrayObject;
use Exception;
use IteratorAggregate;
use Phroper\Model;

class FieldCollection implements ArrayAccess, IteratorAggregate {
    private array $fields = [];
    private Model $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function addInternalField($key, $field) {
        $this->fields[$key] = $field;
        $field->bindModel($this->model, $key);
    }

    public function offsetSet($offset, $field) {
        if (strpos($offset, ".") !== false)
            throw new Exception("Field name " . $offset . " can not contain dots.");

        if (!$field) {
            unset($this->fields[$offset]);
            return;
        }

        $this->fields[$offset] = $field;
        $field->bindModel($this->model, $offset);
    }

    public function offsetExists($offset) {
        return array_key_exists($offset, $this->fields);
    }

    public function offsetUnset($offset) {
        unset($this->fields[$offset]);
    }

    public function offsetGet($offset) {
        if (isset($this->fields[$offset]))
            return $this->fields[$offset];
        return null;
    }

    public function clear() {
        $this->fields = [];
    }

    public function keys() {
        return array_keys($this->fields);
    }


    public function getIterator() {
        return (new ArrayObject($this->fields))->getIterator();
    }
}
