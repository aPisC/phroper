<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\EmbeddedObject;
use Phroper\Fields\Enum;
use Phroper\Fields\Text;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;

class MyEmbeddedObject extends EmbeddedObject {
    public function __construct(array $data = null) {
        parent::__construct([
            "a" => new Text(),
            "b" => new Text()
        ], $data);
    }
}

class EmbeddedObject_Test extends TestCase {
    private string $autoFieldTest__fieldType = MyEmbeddedObject::class;
    private array $autoFieldTest__assoc = [
        [],
        ["field.a" => "", "field.b" => ""],
        ["field.a" => "asd", "field.b" => ""],
        ["field.a" => "", "field.b" => "qwe"],
        ["field.a" => "asd", "field.b" => "qwe"],
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
