<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Boolean;
use Phroper\Fields\Enum;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;

class MyEnum extends Enum {
    public function __construct(array $data = null) {
        parent::__construct(["a", "b", "c"], $data);
    }
}

class Enum_Test extends TestCase {
    private string $autoFieldTest__fieldType = MyEnum::class;
    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
