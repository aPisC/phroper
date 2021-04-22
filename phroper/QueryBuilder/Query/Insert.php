<?php

namespace QueryBuilder\Query;

use Exception;
use Phroper\Model\Fields\IgnoreField;
use QueryBuilder;
use QueryBuilder\Traits\Modifiable;

class Insert extends QueryBuilder {

    use Modifiable;

    public function __construct(...$p) {
        parent::__construct(...$p);
        $this->__modifiable__init();

        foreach ($this->fields as $key => $field) {
            if (!$field || !$field["source"] || $field["field"]->isVirtual()) continue;

            $def = $field["field"]->getDefault();
            if (!($def instanceof IgnoreField)) {
                $this->__modifiable__values->setDefaultValue(
                    $field["source"],
                    $field["field"]->onSave($def)
                );
            } else if ($field["field"]->forceUpdate()) {
                $this->__modifiable__values->setDefaultValue(
                    $field["source"],
                    $field["field"]->onSave(null)
                );
            } else if ($field["field"]->isRequired()) {
                $this->__modifiable__values->setDefaultValue(
                    $field["source"],
                    $field["field"]->onSave(null)
                );
            }
        }
    }


    function getQuery() {
        $this->bindings->reset("values");

        $columnList = "";
        $valueList = "";
        foreach ($this->__modifiable__values->getFields() as $index => $key) {
            if ($index++ !== 0) $columnList .= ", ";
            $columnList .= $key;
        }
        $entityCount = $this->__modifiable__values->getEntityCount();
        for ($eid = 0; $eid < $entityCount; $eid++) {
            if ($eid !== 0) $valueList .= ", ";
            $valueList .= "(";
            foreach ($this->__modifiable__values->getFields() as $index => $key) {
                if ($index++ !== 0) $valueList .= ", ";
                $value = $this->__modifiable__values->getValue($key, $eid);
                // Exceptions is stored to indicate it has to be overwritten
                if ($value instanceof Exception)
                    throw $value;
                $valueList .= $this->bindings->push($value, "values");
            }
            $valueList .= ")";
        }

        return "INSERT INTO `" . $this->model->getTableName() . "` (" . $columnList . ")\n"
            . "VALUES " . $valueList . " \n";
    }
}
