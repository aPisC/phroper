<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\TextKey;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;


class TextKey_Test extends TestCase {
    private string $autoFieldTest__fieldType = TextKey::class;
    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
