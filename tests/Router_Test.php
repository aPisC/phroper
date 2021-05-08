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
}
