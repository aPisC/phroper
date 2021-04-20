<?php

namespace QueryBuilder\Query;

use Exception;
use QueryBuilder;

class CreateTable extends QueryBuilder {

    function getQuery() {
        $fieldList = "";
        foreach ($this->fields as $key => $field) {
            if (!$field["source"]) continue;
            if (strpos($field["alias"], ".") !== strrpos($field["alias"], ".")) continue;
            if ($field["field"]->isVirtual()) continue;
            $fn = $field['field']->getFieldName($key);
            $tp = $field['field']->getSQLType();

            if (!$fn || !$tp) continue;

            if ($fieldList) $fieldList .= ", \n";
            $fieldList .= "`" . $fn . "` " . $tp;
        }
        return "CREATE TABLE `" . $this->model->getTableName()  . "` (\n" . $fieldList . "\n)";
    }
}
