<?php

namespace Phroper\QueryBuilder\Traits;

use Exception;
use Phroper;
use Phroper\Fields\EmbeddedObject;
use Phroper\QueryBuilder;
use Phroper\QueryBuilder\QBFlags;
use Phroper\QueryBuilder\QBModificationCollector;
use Phroper\QueryBuilder\Query\Insert;

trait Modifiable {

    private QBModificationCollector $__modifiable__values;

    private function __modifiable__init() {
        $this->__modifiable__values = new QBModificationCollector();
    }


    public function nextEntity(): void {
        $this->__modifiable__values->next();
    }

    public function setValue(string $key, mixed $value, int $flags = 0): void {
        $key_resolved = $this->resolve($key);
        if (!$key_resolved) return;
        if ($key_resolved["in_relation"])
            throw new Exception("Updating relation value is not supported");

        $field = $this->fields[$key]["field"];
        if ($field->isReadonly() && !($this instanceof Insert)) return;
        if ($field->isAuto()) return;
        if ($field->isHelperField() && !($flags && QBFlags::SET_HELPERS)) return;

        $key_resolved["field"]->handleSetValue($value, $key, $this, $flags);
        if (!$key_resolved["source"]) return;

        $newValue = ($flags & QBFlags::SET_RAW) ? $value : $field->onSave($value);
        if ($newValue instanceof Phroper\Fields\IgnoreField) return;

        $this->__modifiable__values->setValue($key_resolved["source"], $newValue);
    }

    public function setAllValue($values, $prefix = "", $flags = 0): void {
        foreach ($values as $key => $value) {
            $memberName = $prefix == "" ? $key : $prefix . "." . $key;
            $this->setValue($memberName, $value, $flags);
        }
    }
}
