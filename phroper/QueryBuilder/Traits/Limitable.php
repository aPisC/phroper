<?php

namespace QueryBuilder\Traits;

use Phroper;

trait Limitable {
    protected bool $__trait__limitable = true;

    protected string $__limitable__limit = "";
    protected string $__limitable__offset = "";

    public function limit($amount, $disableCap = false) {
        $amount = intval($amount);
        if ($amount < 0)
            $this->__limitable__limit = 0;
        else if (!$disableCap && $amount > 100)
            $this->__limitable__limit = 100;
        else
            $this->__limitable__limit = $amount;
    }

    public function offset($amount) {
        $this->__limitable__offset = intval($amount);
    }
}
