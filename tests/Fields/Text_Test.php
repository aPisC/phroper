<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Text;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;


class Text_Test extends TestCase {
    private string $autoFieldTest__fieldType = Text::class;
    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
