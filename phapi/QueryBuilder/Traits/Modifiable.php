<?php

namespace QueryBuilder\Traits;

use Exception;
use Phapi;
use QueryBuilder\QBModificationCollector;
use QueryBuilder\Query\Insert;

trait Modifiable {
    private QBModificationCollector $__modifiable__values;

    private function __modifiable__init() {
        $this->__modifiable__values = new QBModificationCollector();
    }


    public function nextEntity() {
        $this->__modifiable__values->next();
    }

    public function setValue($key, $value, $rawUpdate = false) {
        $pos = strrpos($key, ".");
        if ($pos != false) throw new Exception("Updating relation value is not supported");

        $key_resolved = $this->resolve($key);
        if (!$key_resolved) return;

        $field = $this->fields[$key]["field"];
        if (($field->isReadonly() && !($this instanceof Insert)) || ($field->isAuto()))
            return;

        $newValue = $rawUpdate ? $value : $field->onSave($value);
        if ($newValue instanceof Phapi\Model\Fields\IgnoreField)
            return;

        if ($field->isRequired() && $newValue == null)
            $newValue = new Exception(
                "Field " . $key . " is required!"
            );
        $this->__modifiable__values->setValue($key_resolved, $newValue);
    }

    public function setAllValue($values, $prefix = "") {
        foreach ($values as $key => $value) {
            $memberName = $prefix == "" ? $key : $prefix . "." . $key;
            $this->setValue($memberName, $value);
        }
    }
}
