<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Timestamp;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;


class Timestamp_Test extends TestCase {
    private string $autoFieldTest__fieldType = Timestamp::class;
    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
