<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Identity;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;


class Identity_Test extends TestCase {
    private string $autoFieldTest__fieldType = Identity::class;
    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
