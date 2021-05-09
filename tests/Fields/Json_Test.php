<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Json;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;


class Json_Test extends TestCase {
    private string $autoFieldTest__fieldType = Json::class;
    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
