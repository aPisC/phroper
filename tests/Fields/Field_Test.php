<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Field;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;

class MyField extends Field {
}

class Field_Test extends TestCase {
    private string $autoFieldTest__fieldType = MyField::class;
    private array $autoFieldTest__assoc = [
        ["field" => true],
        ["field" => false],
        ["field" => null],
        ["field" => 1],
        ["field" => 0],
        ["field" => ""],
        ["field" => "string"],
        ["field" => [1, 2, "string"]],
        ["field" => ["a" => 1, "b" => 2, "c" => "string"]]
    ];

    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__,
            "MYSQLI" => null
        ]);
    }
}
