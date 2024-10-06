<?php

namespace Ophose\Test\Exception;

class TestAssertException extends \Exception
{
    public function __construct($expected = null, $actual = null, $message = null)
    {
        parent::__construct("Assertion failed: Expected <$expected>, got <$actual>.\n" . ($message ? "Message: $message" : ""));
    }
}