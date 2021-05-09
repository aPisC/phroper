<?php

use PHPUnit\Framework\TestCase;
use Phroper\Router;

class Router_Test extends TestCase {
    public function testUrlMatching(): void {
        $router = new Router();

        // Empty url  matching
        $this->assertNotFalse($router->matchUrl("/", ""));
        $this->assertFalse($router->matchUrl("/", "id"));

        // Empty namespace matching
        $this->assertNotFalse($router->matchUrl("//", ""));
        $this->assertEquals("", $router->matchUrl("//", "")['url']);

        $this->assertNotFalse($router->matchUrl("//", "id"));
        $this->assertEquals("id", $router->matchUrl("//", "id")['url']);

        $this->assertNotFalse($router->matchUrl("//", "id/abc/3"));
        $this->assertEquals("id/abc/3", $router->matchUrl("//", "id/abc/3")['url']);

        // static url matching
        $this->assertNotFalse($router->matchUrl("/get", "get"));
        $this->assertNotFalse($router->matchUrl("/get", "get/"));
        $this->assertFalse($router->matchUrl("/get", "get/2"));

        // static namespace matching
        $this->assertNotFalse($router->matchUrl("/get/", "get"));
        $this->assertEquals("", $router->matchUrl("/get/", "get")['url']);

        $this->assertNotFalse($router->matchUrl("/get/", "get/2"));
        $this->assertEquals("2", $router->matchUrl("/get/", "get/2")['url']);

        $this->assertNotFalse($router->matchUrl("/get/", "get/"));
        $this->assertEquals("", $router->matchUrl("/get/", "get/")['url']);

        // parameter matching
        $this->assertNotFalse($router->matchUrl("/:id", "/anything"));
        $this->assertEquals("anything", $router->matchUrl("/:id", "/anything")['id']);

        $this->assertNotFalse($router->matchUrl("/:id", "2"));
        $this->assertEquals("2", $router->matchUrl("/:id", "2")['id']);

        $this->assertNotFalse($router->matchUrl("/:num", "2/"));
        $this->assertEquals("2", $router->matchUrl("/:num", "2/")['num']);

        $this->assertFalse($router->matchUrl("/:id", "2/get"));

        $this->assertNotFalse($router->matchUrl("/:id/", "2/get"));
        $this->assertEquals("2", $router->matchUrl("/:id/", "2/get")['id']);
        $this->assertEquals("get", $router->matchUrl("/:id/", "2/get")['url']);

        // path parameter matching
        $this->assertNotFalse($router->matchUrl("/::path", "/anything"));
        $this->assertEquals("anything", $router->matchUrl("/:path", "/anything")['path']);

        $this->assertNotFalse($router->matchUrl("/::path", "/anything/long/path"));
        $this->assertEquals("anything/long/path", $router->matchUrl("/::path", "/anything/long/path")['path']);

        $this->assertNotFalse($router->matchUrl("/::path/static", "/anything/long/path/static"));
        $this->assertEquals("anything/long/path", $router->matchUrl("/::path/static", "/anything/long/path/static")['path']);

        $this->assertFalse($router->matchUrl("/::path/static", "/anything/long/path/else"));
    }

    public function testRouterHandlerExecutionOrder(): void {
        $router = new Router();

        $handlerCounter = 0;
        $inc = function () use (&$handlerCounter) {
            return $handlerCounter++;
        };

        $router->addHandler(fn ($p, $n) => [$this->assertEquals(2, $inc()), $n()], 0);
        $router->addHandler(fn ($p, $n) => [$this->assertEquals(0, $inc()), $n()], 2);
        $router->addHandler(fn ($p, $n) => [$this->assertEquals(1, $inc()), $n()], 1);
        $router->addHandler(fn ($p, $n) => [$this->assertEquals(3, $inc()), $n()], -1);
        $router->addHandler(fn ($p, $n) => 0, -3);
        $router->addHandler(fn ($p, $n) => $this->assertTrue(false), -4);

        $router->run([]);
    }

    public function testRoutedHandlerExecutions() {
        $router = new Router();

        $router->add("/get/:id", function ($p, $n) {
            $this->assertEquals("3", $p["id"]);
            $this->assertEquals("", $p["url"]);
            $this->assertEquals(true, $p["custom"]);
        }, "GET");

        $router->add("/put/:id", function ($p, $n) {
            $this->assertEquals("4", $p["id"]);
            $this->assertEquals("", $p["url"]);
            $this->assertEquals(true, $p["custom"]);
        },);

        $mock = $this
            ->getMockBuilder(\stdclass::class)
            ->addMethods(['__invoke'])
            ->getMock();

        $mock
            ->expects(self::exactly(0))
            ->method('__invoke');
        $router->run(["url" => "get/3", "method" => "GET", "custom" => true], $mock);
        $router->run(["url" => "put/4", "method" => "PUT", "custom" => true], $mock);



        $mock = $this
            ->getMockBuilder(\stdclass::class)
            ->addMethods(['__invoke'])
            ->getMock();

        $mock
            ->expects(self::exactly(1))
            ->method('__invoke');
        $router->run(["url" => "del/3", "method" => "GET", "custom" => true], $mock);
    }
}
