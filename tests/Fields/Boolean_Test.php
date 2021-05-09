<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Boolean;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;


class Boolean_Test extends TestCase {
    private string $autoFieldTest__fieldType = Boolean::class;
    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
