<?php

use PHPUnit\Framework\TestCase;
use Phroper\Fields\Json;
use Phroper\Phroper;
use Spatie\Snapshots\MatchesSnapshots;


class Json_Test extends TestCase {
    private string $autoFieldTest__fieldType = Json::class;
    private array $autoFieldTest__assoc;
    public function __construct(...$args) {
        parent::__construct(...$args);
        $this->autoFieldTest__assoc = [
            ["field" => json_encode(true)],
            ["field" => json_encode(false)],
            ["field" => json_encode(null)],
            ["field" => json_encode(1)],
            ["field" => json_encode(0)],
            ["field" => json_encode("")],
            ["field" => json_encode("string")],
            ["field" => json_encode([1, 2, "string"])],
            ["field" => json_encode(["a" => 1, "b" => 2, "c" => "string"])]
        ];
    }


    use MatchesSnapshots;
    use AutoFieldTest;

    public function setUp(): void {
        Phroper::reinitialize([
            "ROOT" => __DIR__,
            "MYSQLI" => null
        ]);
    }
}
