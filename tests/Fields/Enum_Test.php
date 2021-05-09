<?php

use PHPUnit\Framework\TestCase;
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
    private array $autoFieldTest__assoc = [
        ["field" => true],
        ["field" => false],
        ["field" => null],
        ["field" => 1],
        ["field" => 0],
        ["field" => "a"],
        ["field" => "b"],
        ["field" => "c"],
        ["field" => "string"],
        ["field" => [1, 2, "string"]],
        ["field" => ["a" => 1, "b" => 2, "c" => "string"]]
    ];

    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
