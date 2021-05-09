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

class EmbeddedObject_Identity extends Field {
    public function __construct() {
        parent::__construct([
            "virtual" => true,
            "private" => true,
            "readonly" => true,
            "auto" => true,
            "visible" => false,
            "type" => "embedded_object"
        ]);
    }
    public function onLoad($value, $key, $assoc, $populates) {
        return $value;
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

    public function getUiInfo(): array {
        $data = parent::getUiInfo();
        $data["fields"] = [];
        foreach ($this->fields as $key => $field) {
            if (!$field) continue;
            if ($field->isHelperField()) continue;

            $fd = $field->getUiInfo();
            if (!$fd) continue;

            $data["fields"][$key] = $fd;
        }

        return $data;
    }

    public function bindModel($model, $fieldName) {
        parent::bindModel($model, $fieldName);
        if ($model->fields["id"])
            $this->fields["id"] = new EmbeddedObject_Identity();

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

    public function postUpdate($value, $key, $entity) {
        $hadUpdate = false;
        foreach ($this->fields as $fk => $field) {
            $hadUpdate |= $field->postUpdate(
                array_key_exists($fk, $value) ? $value[$fk] : $entity["id"],
                $fk,
                $entity[$key],
            );
        }
        return $hadUpdate;
    }

    public function getSanitizedValue($value) {
        $value = parent::getSanitizedValue($value);

        if ($value instanceof IgnoreField) return $value;
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
