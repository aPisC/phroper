<?php

namespace QueryBuilder;

class BindCollector {
    private $groups = [];
    private $bindStr = "";
    private $bindValues = array();

    private function getGroup($group) {
        if (isset($this->groups[$group]))
            return $this->groups[$group];
        $this->groups[$group] = ["", []];
        return $this->groups[$group];
    }

    function push($value, $group = "values") {
        if ($value instanceof QB_Const)
            return $value->getResolved();
        if ($value === true) return "TRUE";
        if ($value === false) return "FALSE";
        if ($value === null) return "NULL";

        $g = $this->getGroup($group);

        $g[1][] = $value;
        if (is_string($value)) $g[0] .= "s";
        if (is_double($value)) $g[0] .= "d";
        if (is_integer($value)) $g[0] .= "i";

        $this->groups[$group] = $g;

        return "?";
    }

    function getBindStr($group = "values") {
        return $this->getGroup($group)[0];
    }
    function getBindValues($group = "values") {
        return $this->getGroup($group)[1];
    }
    function reset($group = "values") {
        $this->groups[$group] = ["", []];
    }
}
