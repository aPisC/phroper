<?php

$router = Phroper::instance()->router;

$router->add("admin/", "admin\\AdminRouter");
$router->addServeFolder("static/", implode(DS, [__DIR__, "ui", "build", "static"]));
$router->addServeFolder("admin/", implode(DS, [__DIR__, "ui", "build"]));
$router->addServeFile("admin/", implode(DS, [__DIR__, "ui", "build", "index.html"]));
