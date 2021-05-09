<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Email;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;


class Email_Test extends TestCase {
    private string $autoFieldTest__fieldType = Email::class;
    private array $autoFieldTest__assoc = [
        ["field" => "some string"],
        ["field" => "example@email.com"],
        ["field" => ""],
        ["field" => null]
    ];

    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__
        ]);
    }
}
