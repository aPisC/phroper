<?php

namespace Phroper\Fields;

use Exception;
use Phroper\Model;
use Phroper\Model\EntityList;
use Phroper\Model\LazyResult;

class EmbeddedArraySimple_Model extends Model {
    public ?int $parentId = null;

    public function __construct($field, $model, $tableName) {
        parent::__construct(["sql_table" => $tableName]);
        $this->fields->clear();
        $this->fields["id"] = new Identity(["private"]);
        $this->fields["__parent__"] = new RelationToOne($model, [
            "private",
            "sql_delete_action" => "CASCADE",
            "auto" => true,
            "forced" => true,
            "default" => fn () => $this->parentId,
            "type" => "embedded_array_simple"
        ]);
        $this->fields["value"] = Field::createField($field);
    }
}

class EmbeddedArraySimple extends RelationToMany {

    private $field;

    public function __construct($field, $data = null) {
        parent::__construct(null, "__parent__", [
            "min" => null,
            "max" => null,
            "default" => [],
            "populate" => false
        ]);
        $this->updateData($data);

        $this->field = $field;
    }

    public function getUiInfo(): array {
        $data = parent::getUiInfo();
        $data["field"] = $this->relationModel->fields["value"]->getUiInfo();
        return $data;
    }

    public function bindModel($model, $fieldName) {
        parent::bindModel($model, $fieldName);
        if (!$model->fields["id"])
            throw new Exception("Model has to have id to use EmbeddedArray");

        $model = new EmbeddedArraySimple_Model($this->field, $model, $model->getTableName() . "." . $fieldName);
        $this->relationModel = $model;
        $this->updateData([
            "model" => $model ? $this->getModel()->getName() : "",
            "model_display" => $model ? $this->getModel()->getDisplayField() : "",
        ]);
    }
    public function postUpdate($value, $key, $entity) {
        if (!$value) $value = [];

        if (is_array($value)) {
            if (isset($this->data["min"]) && count($value) < $this->data["min"])
                throw $this->validationError("min", $this->data["name"] . " requires at least " . $this->data["min"] . " entry.");
            if (isset($this->data["max"]) && count($value) > $this->data["max"])
                throw $this->validationError("max", $this->data["name"] . " can have " . $this->data["max"] . " entry.");
            $id = $entity["id"];
            $this->relationModel->parentId = $id;

            $this->relationModel->delete(["__parent__" => $id], false);

            if (count($value) == 0) return true;
            $this->relationModel->createMulti(array_map(fn ($v) => ["value" => $v], $value));
            return true;
        }
        return false;
    }

    public function onLoad($value, $key, $assoc, $populates) {
        if (in_array($key, $populates)) {
            $populates = array_filter($populates, fn ($p) => str_starts_with($p, $key));
            $populates = array_map(fn ($p) => $key . ".value" . substr($p, strlen($key)), $populates);
            $populates[] = $key;
        } else return IgnoreField::instance();
        $v = parent::onLoad($value, $key, $assoc, $populates);
        return new LazyResult(function () use ($v) {
            if ($v instanceof LazyResult) $v = $v->get();
            if ($v instanceof IgnoreField) return $v;
            return $v->map(fn ($v) => $v["value"]);
        });
    }

    public function getSanitizedValue($value) {
        if ($this->isPrivate())
            return IgnoreField::instance();
        if ($value instanceof EntityList) {
            $model = $this->getModel();
            return  $value->map(function ($entity) use ($model) {
                return $model->sanitizeEntity($entity);
            });
        }
        if (is_array($value)) {
            $model = $this->getModel();
            return  array_map(function ($entity) use ($model) {
                return $model->fields["value"]->getSanitizedValue($entity);
            }, $value);
        }
        return IgnoreField::instance();
    }

    public function onSave($value) {
        return IgnoreField::instance();
    }
}
