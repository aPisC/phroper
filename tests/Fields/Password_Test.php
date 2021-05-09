<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Password;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;


class Password_Test extends TestCase {
    private string $autoFieldTest__fieldType = Password::class;
    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }

    public function testRequiredSaving() {
        $this->assertTrue(true);
    }
}
