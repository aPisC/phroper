<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Field;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;

class MyField extends Field {
}

class Field_Test extends TestCase {
    private string $autoFieldTest__fieldType = MyField::class;
    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
