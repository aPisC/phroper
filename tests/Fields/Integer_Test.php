<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Integer;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;


class Integer_Test extends TestCase {
    private string $autoFieldTest__fieldType = Integer::class;
    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
