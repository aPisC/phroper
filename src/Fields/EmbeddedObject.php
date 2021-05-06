<?php

namespace Phroper\Fields;

use Phroper\Model;
use Phroper\Model\Entity;
use Phroper\Model\LazyResult;

class EmbeddedObjectVirtualField extends FieldExtension {
    public function __construct($field) {
        parent::__construct($field);
    }
    public function onLoad($value, $key, $assoc, $populates) {
        return IgnoreField::instance();
    }
    public function onSave($value) {
        return;
    }
}

class EmbeddedObject extends Field {
    private array $fields;

    public function __construct($fields, $data = null) {
        parent::__construct([
            "populate" => true,
            "virtual" => true,
        ]);
        $this->updateData($data);

        $this->fields = $fields;
    }

    public function bindModel($model, $fieldName) {
        parent::bindModel($model, $fieldName);

        foreach ($this->fields as $key => $field) {
            $this->model->fields[$fieldName . "." . $key] = new EmbeddedObjectVirtualField($field);
        }
    }
    public function onLoad($value, $key, $assoc, $populates) {
        $a = [];
        foreach ($this->fields as $fk => $field) {
            $a[$fk] = $field->onLoad(
                isset($assoc[$key . "." . $fk]) ? $assoc[$key . "." . $fk] : $value,
                $key . "." . $fk,
                $assoc,
                $populates
            );
        }
        return $a;
    }

    public function getSanitizedValue($value) {
        $ne = [];
        foreach ($this->fields as $fk => $field) {
            $v = null;
            if (isset($value[$fk])) $v = $value[$fk];
            if ($v instanceof LazyResult) $v = $v->get();
            $v = $field->getSanitizedValue($v);
            if ($v instanceof IgnoreField) continue;
            $ne[$fk] = $v;
        }
        return $ne;
    }
}
