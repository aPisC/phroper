<?php

namespace Phroper\QueryBuilder\Traits;

use Exception;
use Phroper;
use Phroper\Fields\EmbeddedObject;
use Phroper\QueryBuilder\QBModificationCollector;
use Phroper\QueryBuilder\Query\Insert;

trait Modifiable {
    private QBModificationCollector $__modifiable__values;

    private function __modifiable__init() {
        $this->__modifiable__values = new QBModificationCollector();
    }


    public function nextEntity() {
        $this->__modifiable__values->next();
    }

    public function setValue($key, $value, $rawUpdate = false) {

        $key_resolved = $this->resolve($key);
        if (!$key_resolved) return;
        if ($key_resolved["in_relation"])
            throw new Exception("Updating relation value is not supported");
        if (!$key_resolved["source"] && $key_resolved["field"]->is(EmbeddedObject::class)) {
            $key_resolved["field"]->handleQuerySet($value, $key, $this, $rawUpdate);
        }


        $field = $this->fields[$key]["field"];
        if (($field->isReadonly() && !($this instanceof Insert)) || ($field->isAuto()))
            return;

        $newValue = $rawUpdate ? $value : $field->onSave($value);
        if ($newValue instanceof Phroper\Fields\IgnoreField)
            return;

        $this->__modifiable__values->setValue($key_resolved["source"], $newValue);
    }

    public function setAllValue($values, $prefix = "") {
        foreach ($values as $key => $value) {
            $memberName = $prefix == "" ? $key : $prefix . "." . $key;
            $this->setValue($memberName, $value);
        }
    }
}
