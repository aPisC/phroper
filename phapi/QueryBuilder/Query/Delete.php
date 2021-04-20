<?php

namespace QueryBuilder\Query;

use Exception;
use QueryBuilder;
use QueryBuilder\Traits\Filterable;
use QueryBuilder\Traits\IJoinable;
use QueryBuilder\Traits\Joinable;

class Delete extends QueryBuilder implements IJoinable {
    use Filterable;
    use Joinable;

    function getQuery() {
        $this->bind_params = new QueryBuilder\BindCollector();


        return
            "DELETE FROM " . "`" . $this->model->getTableName() . "` \n"
            . ($this->__joinable__sql ? $this->__joinable__sql . "\n" : "")
            . ($this->__filterable__filter ? ("WHERE " . $this->__filterable__filter . "\n") : "");
    }
}
