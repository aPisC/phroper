<?php

namespace Phroper\QueryBuilder\Query;

use Exception;
use Phroper\QueryBuilder;
use Phroper\QueryBuilder\BindCollector;
use Phroper\QueryBuilder\Traits\Filterable;
use Phroper\QueryBuilder\Traits\IJoinable;
use Phroper\QueryBuilder\Traits\Joinable;

class Delete extends QueryBuilder implements IJoinable {
    use Filterable;
    use Joinable;

    function getQuery() {
        $this->bind_params = new BindCollector();


        return
            "DELETE FROM " . "`" . $this->model->getTableName() . "` \n"
            . ($this->__joinable__sql ? $this->__joinable__sql . "\n" : "")
            . ($this->__filterable__filter ? ("WHERE " . $this->__filterable__filter . "\n") : "")
            . "\n";
    }
}
