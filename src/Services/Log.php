<?php

namespace Phroper\Services;

use Phroper\Phroper;
use Phroper\Service;

class Log extends Service {
    public function insertLog($type, $message) {
        Phroper::model("log")->createMulti([["type" => $type, "message" => $message]], false);
    }

    public function error($message) {
        $this->insertLog("error", $message);
    }

    public function warn($message) {
        $this->insertLog("warn", $message);
    }

    public function info($message) {
        $this->insertLog("info", $message);
    }

    public function debug($message) {
        $this->insertLog("debug", $message);
    }

    public function allowDefaultController() {
        return false;
    }
}
