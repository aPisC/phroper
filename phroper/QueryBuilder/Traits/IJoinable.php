<?php

namespace QueryBuilder\Traits;

use Phroper;

interface IJoinable {
    public function join($join, $collFields = null);
}
