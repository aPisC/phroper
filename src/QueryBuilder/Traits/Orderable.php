<?php

namespace Phroper\QueryBuilder\Traits;

use Phroper;

trait Orderable {
    protected bool $__trait__orderable = true;

    private string $__orderable__order = "";


    public function orderBy($field, $isDesc = false) {
        foreach (explode(',', $field) as $f) {
            $id = $isDesc;
            if (str_ends_with(strtolower($f), ":desc")) {
                $f = str_drop_end($f, 5);
                $id = true;
            } else if (str_ends_with(strtolower($f), ":asc")) {
                $f = str_drop_end($f, 4);
                $id = false;
            }
            $key = $this->resolve($f);
            if (!$key || !$key["source"]) continue;
            if ($this->__orderable__order) $this->__orderable__order .= ", ";
            $this->__orderable__order .=
                $key["source"] . ($id ? " DESC" : " ASC");
        }
    }

    public function orderByEq($field, $value, $isDesc = true) {
        $key = $this->resolve($field);
        if (!$key || !$key["source"]) return;
        if ($this->__orderable__order) $this->__orderable__order =  ", " . $this->__orderable__order;
        $this->__orderable__order =
            $key["source"] . " = " . intval($value) . ($isDesc ? " DESC" : " ASC") . $this->__orderable__order;
    }
}
