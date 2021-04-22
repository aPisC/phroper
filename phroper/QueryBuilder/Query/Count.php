<?php

namespace QueryBuilder\Query;

use QueryBuilder;
use QueryBuilder\Traits\Filterable;
use QueryBuilder\Traits\IJoinable;
use QueryBuilder\Traits\Joinable;

class Count extends QueryBuilder implements IJoinable {

    use Filterable;
    use Joinable;

    protected function execHasResult() {
        return true;
    }

    function getQuery() {
        return "SELECT count(*) FROM " . "`" . $this->model->getTableName() . "`\n"
            . ($this->__joinable__sql ? $this->__joinable__sql . "\n" : "")
            . ($this->__filterable__filter ? ("WHERE " . $this->__filterable__filter . "\n") : "");
    }
}
