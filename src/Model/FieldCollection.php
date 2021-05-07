<?php

namespace Phroper\Model;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use Exception;
use IteratorAggregate;
use Phroper\Fields\Field;
use Phroper\Model;

class FieldCollection implements ArrayAccess, IteratorAggregate {
    private array $fields = [];
    private Model $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function addInternalField(string $key, Field $field) {
        $this->fields[$key] = $field;
        $field->bindModel($this->model, $key);
    }

    public function offsetSet($offset, $field): void {
        if (!is_string($offset)) throw new Exception("Field name must be string");
        if ($field && !($field instanceof Field)) throw new Exception("Field must be inherited from Phroper\\Field");
        if (strpos($offset, ".") !== false) throw new Exception("Field name " . $offset . " can not contain dots.");

        if (!$field) {
            unset($this->fields[$offset]);
            return;
        }

        $this->fields[$offset] = $field;
        $field->bindModel($this->model, $offset);
    }

    public function offsetExists($offset): bool {
        return array_key_exists($offset, $this->fields);
    }

    public function offsetUnset($offset): void {
        unset($this->fields[$offset]);
    }

    public function offsetGet($offset): ?Field {
        if (isset($this->fields[$offset]))
            return $this->fields[$offset];
        return null;
    }

    public function clear(): void {
        $this->fields = [];
    }

    public function keys(): array {
        return array_keys($this->fields);
    }


    public function getIterator(): ArrayIterator {
        return (new ArrayObject($this->fields))->getIterator();
    }
}
