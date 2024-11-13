<?php

use Ophose\Test\Test;

return new class extends Test
{
    public function testTrueIsTrue() {
        $this->setTestName("True is true")->setTestDescription("This test asserts that true is true.");

        $this->assertTrue(true);
    }

    public function testHelloWorldEndpoint() {
        $this->setTestName("Ophose endpoint /api/ophose/hello_world sends 'Hello, World!'")->setTestDescription("This test asserts that the Ophose endpoint /api/ophose/hello_world sends 'Hello, World!'.");

        $request = $this->client('/ophose/hello_world');
        $request->send();
        $this->assertEquals($request->status(), 200);
        $this->assertEquals($request->response()->body(), "Hello, World!");
    }
};