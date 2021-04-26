<?php

namespace Phroper\QueryBuilder\Query;

use Phroper\QueryBuilder;
use Phroper\QueryBuilder\Traits\Filterable;
use Phroper\QueryBuilder\Traits\IJoinable;
use Phroper\QueryBuilder\Traits\Joinable;
use Phroper\QueryBuilder\Traits\Limitable;
use Phroper\QueryBuilder\Traits\Orderable;

class Select extends QueryBuilder implements IJoinable {

    use Limitable;
    use Orderable;
    use Filterable;
    use Joinable;

    protected function getFieldList() {
        $fieldList = "";
        foreach ($this->fields as $field) {
            if ($field["field"]->isVirtual()) continue;
            if ($field["hidden"]) continue;
            if (!$field["source"]) continue;

            if ($fieldList) $fieldList .= ", ";
            $fieldList .= $field["source"] . " as '" . $field["alias"] . "'";
        }
        return $fieldList;
    }

    protected function execHasResult() {
        return true;
    }

    public function getQuery() {
        $fieldList = $this->getFieldList();

        return "SELECT " . $fieldList . "\n"
            . "FROM " . "`" . $this->model->getTableName() . "`\n"
            . ($this->__joinable__sql ? ($this->__joinable__sql . "\n") : "")
            . ($this->__filterable__filter ? ("WHERE " . $this->__filterable__filter . "\n") : "")
            . ($this->__orderable__order ? ("ORDER BY " . $this->__orderable__order . "\n") : "")
            . ($this->__limitable__limit ? ("LIMIT " . $this->__limitable__limit . "\n") : "")
            . ($this->__limitable__offset ? ("OFFSET " . $this->__limitable__offset . "\n") : "")
            . "\n";
    }
}
