<?php

namespace admin;

use Phapi\Router;

class AdminRouter extends Router {
    public function __construct() {
        $this->add("content-schema/", "admin\\Controllers\\ContentSchema");
        $this->add("content-manager/", "admin\\Controllers\\ContentManager");
    }
}
