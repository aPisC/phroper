<?php

namespace Phroper\QueryBuilder\Query;

use Phroper\QueryBuilder;
use Phroper\QueryBuilder\Traits\Filterable;
use Phroper\QueryBuilder\Traits\IJoinable;
use Phroper\QueryBuilder\Traits\Joinable;

class Count extends QueryBuilder implements IJoinable {

    use Filterable;
    use Joinable;

    protected function execHasResult() {
        return true;
    }

    function getQuery() {
        return "SELECT count(*) FROM " . "`" . $this->model->getTableName() . "`\n"
            . ($this->__joinable__sql ? $this->__joinable__sql . "\n" : "")
            . ($this->__filterable__filter ? ("WHERE " . $this->__filterable__filter . "\n") : "")
            . "\n";
    }
}
