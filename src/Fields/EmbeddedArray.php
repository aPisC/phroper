<?php

namespace Phroper\Fields;

use Phroper\Model;
use Phroper\Model\EntityList;

class EmbeddedArray_Model extends Model {
    public function __construct($fields, $model, $tableName) {
        parent::__construct(["sql_table" => $tableName]);
        $this->fields->clear();
        $this->fields["__parent__"] = new RelationToOne($model, [
            "required",
            "private",
            "sql_delete_action" => "CASCADE"
        ]);
        foreach ($fields as $fn  => $f) {
            $this->fields[$fn] = Field::createField($f);
        }
    }
}

class EmbeddedArray extends RelationToMany {

    private $fields;

    public function __construct($fields, $data = null) {
        parent::__construct(null, "__parent__", [
            "min" => null,
            "max" => null,
            "default" => [],
            "populate" => true
        ]);
        $this->updateData($data);

        $this->fields = $fields;
    }

    public function bindModel($model, $fieldName) {
        parent::bindModel($model, $fieldName);

        $model = new EmbeddedArray_Model($this->fields, $model, $model->getTableName() . "." . $fieldName);
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

            $this->relationModel->delete(["__parent__" => $id], false);

            if (count($value) == 0) return true;
            $this->relationModel->createMulti(
                array_map(
                    function ($value) use ($id) {
                        return array_merge($value, ["__parent__" => $id]);
                    },
                    $value
                ),
                false
            );
            return true;
        }
        return false;
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
                return $model->sanitizeEntity($entity);
            }, $value);
        }
        return IgnoreField::instance();
    }

    public function onSave($value) {
        return IgnoreField::instance();
    }
}
