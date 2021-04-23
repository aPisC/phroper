<?php

namespace admin;

use Phroper\Router;

class AdminRouter extends Router {
    public function __construct() {
        parent::__construct();
        $this->add("content-schema/", "admin\\Controllers\\ContentSchema");
        $this->add("content-manager/", "admin\\Controllers\\ContentManager");
    }
}
