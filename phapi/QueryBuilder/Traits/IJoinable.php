<?php

namespace QueryBuilder\Traits;

use Phapi;

interface IJoinable {
    public function join($join, $collFields = null);
}
