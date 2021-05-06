<?php

namespace Phroper\QueryBuilder\Query;

use Exception;
use Phroper\Fields\IgnoreField;
use Phroper\QueryBuilder;
use Phroper\QueryBuilder\Traits\Modifiable;
use Throwable;

class Insert extends QueryBuilder {

    use Modifiable;

    public function __construct(...$p) {
        parent::__construct(...$p);
        $this->__modifiable__init();

        foreach ($this->fields as $key => $field) {
            if (!$field || !$field["source"] || $field["field"]->isVirtual()) continue;

            $def = $field["field"]->getDefault();
            try {
                if (!($def instanceof IgnoreField)) {
                    $this->__modifiable__values->setDefaultValue(
                        $field["source"],
                        $field["field"]->onSave($def)
                    );
                } else if ($field["field"]->forceUpdate() || $field["field"]->isRequired()) {
                    $this->__modifiable__values->setDefaultValue(
                        $field["source"],
                        $field["field"]->onSave(null)
                    );
                }
            } catch (Throwable $ex) {
                $this->__modifiable__values->setDefaultValue(
                    $field["source"],
                    $ex
                );
            }
        }
    }


    function getQuery() {
        $this->bindings->reset("values");

        $columnList = "";
        $valueList = "";
        foreach ($this->__modifiable__values->getFields() as $index => $key) {
            if (!$key) continue;
            if ($columnList) $columnList .= ", ";
            $columnList .= $key;
        }
        $entityCount = $this->__modifiable__values->getEntityCount();
        for ($eid = 0; $eid < $entityCount; $eid++) {
            if ($eid !== 0) $valueList .= ", ";

            foreach ($this->__modifiable__values->getFields() as $index => $key) {
                if (!$key) continue;
                if ($valueList) $valueList .= ", ";
                $value = $this->__modifiable__values->getValue($key, $eid);
                // Exceptions is stored to indicate it has to be overwritten
                if ($value instanceof Exception)
                    throw $value;
                $valueList .= $this->bindings->push($value, "values");
            }
            $valueList =  "(" . $valueList . ")";
        }

        return "INSERT INTO `" . $this->model->getTableName() . "` (" . $columnList . ")\n"
            . "VALUES " . $valueList . " \n";
    }
}
