<?php

namespace Phroper\QueryBuilder\Traits;

interface IModifiableQuery {

    public function nextEntity(): void;
    public function setValue(string $key, mixed $value, int $flags = 0): void;
    public function setAllValue($values, $prefix = "", $flags = 0): void;
}
