<?php

// Phroper engine initialization

use Phroper\JWT;
use Phroper\Phroper;
use Phroper\QueryBuilder;

Phroper::addInitializer(function () {
  $router = Phroper::instance()->router;
  $injector = Phroper::instance()->injector;

  $router->addHandler(function ($p, $n) {
    return JWT::TokenParserMiddleware($p, $n);
  }, 1000);
  $router->addServeFolder("/uploads/", Phroper::ini("ROOT") . DIRECTORY_SEPARATOR . "uploads");
  $router->add("/api/:controller/", "Phroper\\Routers\\ApiRouter");

  // Register base models
  $injector->provideType("Models\\AuthPermission", "Phroper\\Models\\AuthPermission");
  $injector->provideType("Models\\AuthRole", "Phroper\\Models\\AuthRole");
  $injector->provideType("Models\\AuthUser", "Phroper\\Models\\AuthUser");
  $injector->provideType("Models\\FileUpload", "Phroper\\Models\\FileUpload");
  $injector->provideType("Models\\Log", "Phroper\\Models\\Log");
  $injector->provideType("Models\\Store", "Phroper\\Models\\Store");

  // Register base controllers
  $injector->provideType("Controllers\\Auth", "Phroper\\Controllers\\Auth");
  $injector->provideType("Controllers\\FileUpload", "Phroper\\Controllers\\FileUpload");
  $injector->provideType("Controllers\\Init", "Phroper\\Controllers\\Init");
  $injector->provideType("Controllers\\Role", "Phroper\\Controllers\\Role");
  $injector->provideType("Controllers\\User", "Phroper\\Controllers\\User");

  // Register base services
  $injector->provideType("Services\\Auth", "Phroper\\Services\\Auth");
  $injector->provideType("Services\\Role", "Phroper\\Services\\Role");
  $injector->provideType("Services\\User", "Phroper\\Services\\User");
  $injector->provideType("Services\\Email", "Phroper\\Services\\Email");
  $injector->provideType("Services\\Log", "Phroper\\Services\\Log");
  $injector->provideType("Services\\Store", "Phroper\\Services\\Store");
});
