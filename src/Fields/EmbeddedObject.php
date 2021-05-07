<?php

namespace Phroper\Fields;

use Phroper\Model;
use Phroper\Model\Entity;
use Phroper\Model\LazyResult;
use Phroper\QueryBuilder;
use Phroper\QueryBuilder\QBFlags;
use Phroper\QueryBuilder\Traits\IModifiableQuery;

class EmbeddedObjectVirtualField extends FieldExtension {
    public function __construct($field) {
        parent::__construct($field);
    }
    public function onLoad($value, $key, $assoc, $populates) {
        return IgnoreField::instance();
    }


    public function isHelperField(): bool {
        return true;
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
            $field = Field::createField($field);
            $this->fields[$key] = $field;
            $this->model->fields->addInternalField($fieldName . "." . $key, new EmbeddedObjectVirtualField($field));
        }
    }
    public function onLoad($value, $key, $assoc, $populates) {
        $a = [];
        foreach ($this->fields as $fk => $field) {
            $a[$fk] = $field->onLoad(
                array_key_exists($key . "." . $fk, $assoc) ? $assoc[$key . "." . $fk] : $value,
                $key . "." . $fk,
                $assoc,
                $populates
            );
        }
        return $a;
    }

    public function getSanitizedValue($value) {
        if (!$value) return null;
        $ne = [];
        foreach ($this->fields as $fk => $field) {
            $v = null;
            if (array_key_exists($fk, $value)) $v = $value[$fk];
            if ($v instanceof LazyResult) $v = $v->get();
            $v = $field->getSanitizedValue($v);
            if ($v instanceof IgnoreField) continue;
            $ne[$fk] = $v;
        }
        return $ne;
    }

    public function handleSetValue(mixed $value, string $key, IModifiableQuery $query, int $flags) {
        if (!$value) return;
        $query->setAllValue($value, $key, $flags | QBFlags::SET_HELPERS);
    }
}
