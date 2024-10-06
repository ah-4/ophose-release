<?php

namespace Ophose\Test;

use Ophose\Test\Exception\TestAssertException;

use function Ophose\Util\clr;

/**
 * Abstract class Test
 *
 * This class defines a structure for running tests with optional setup (before)
 * and teardown (after) methods. It ensures that every test will execute the 
 * setup method before running the test, and the teardown method afterward.
 *
 * The `test()` method must be implemented by any class extending this abstract class.
 */
class Test {

    protected function setTestName(string $name): Test {
        echo clr("       Current test name: &magenta;$name\n");
        return $this;
    }

    protected function setTestDescription(string $description): Test {
        echo clr("       Current test description: &blue;$description\n");
        return $this;
    }

    /**
     * Prepares the environment before the test is run.
     *
     * This method is called before the main test logic is executed.
     * It can be used to set up necessary configurations or initialize resources.
     *
     * @return void
     */
    public function before(): void {}

    /**
     * Cleans up after the test has been run.
     *
     * This method is called after the main test logic has completed.
     * It can be used to release resources, close connections, etc.
     *
     * @return void
     */
    public function after(): void {}

    // #region Assertions

    /**
     * Asserts that the given condition is true.
     *
     * This method checks if the given condition is true. If the condition is false,
     * an exception is thrown with the specified message.
     *
     * @param bool $condition The condition to check.
     * @return void
     * @throws TestAssertException
     */
    public function assertTrue(bool $condition): void {
        if (!$condition) throw new TestAssertException("true", "false", "The evaluated condition is false and should be true.");
    }

    /**
     * Asserts that the given condition is false.
     *
     * This method checks if the given condition is false. If the condition is true,
     * an exception is thrown with the specified message.
     *
     * @param bool $condition The condition to check.
     * @return void
     * @throws TestAssertException
     */
    public function assertFalse(bool $condition): void {
        if ($condition) throw new TestAssertException("false", "true", "The evaluated condition is true and should be false.");
    }

    /**
     * Asserts that the two values are equal.
     *
     * This method checks if the two values are equal. If the values are not equal,
     * an exception is thrown with the specified message.
     *
     * @param mixed $expected The expected value.
     * @param mixed $actual The actual value.
     * @return void
     * @throws TestAssertException
     */
    public function assertEquals(mixed $expected, mixed $actual): void {
        if ($expected !== $actual) throw new TestAssertException($expected, $actual, "The two values are not equal.");
    }

    /**
     * Asserts that the two values are not equal.
     *
     * This method checks if the two values are not equal. If the values are equal,
     * an exception is thrown with the specified message.
     *
     * @param mixed $expected The expected value.
     * @param mixed $actual The actual value.
     * @return void
     * @throws TestAssertException
     */
    public function assertNotEquals(mixed $expected, mixed $actual): void {
        if ($expected === $actual) throw new TestAssertException($expected, $actual, "The two values are equal and should not be.");
    }

    /**
     * Asserts that the value is null.
     *
     * This method checks if the value is null. If the value is not null,
     * an exception is thrown with the specified message.
     *
     * @param mixed $value The value to check.
     * @return void
     * @throws TestAssertException
     */
    public function assertNull(mixed $value): void {
        if ($value !== null) throw new TestAssertException(null, $value, "The value is not null.");
    }

    /**
     * Asserts that the value is not null.
     *
     * This method checks if the value is not null. If the value is null,
     * an exception is thrown with the specified message.
     *
     * @param mixed $value The value to check.
     * @return void
     * @throws TestAssertException
     */
    public function assertNotNull(mixed $value): void {
        if ($value === null) throw new TestAssertException("not null", null, "The value is null and should not be.");
    }

    /**
     * Asserts that the value is an instance of the given class.
     *
     * This method checks if the value is an instance of the given class. If the value
     * is not an instance of the class, an exception is thrown with the specified message.
     *
     * @param mixed $value The value to check.
     * @param string $class The class name.
     * @return void
     * @throws TestAssertException
     */
    public function assertInstanceOf(mixed $value, string $class): void {
        if (!($value instanceof $class)) throw new TestAssertException($class, get_class($value), "The value is not an instance of the specified class.");
    }

    /**
     * Asserts that the value is not an instance of the given class.
     *
     * This method checks if the value is not an instance of the given class. If the value
     * is an instance of the class, an exception is thrown with the specified message.
     *
     * @param mixed $value The value to check.
     * @param string $class The class name.
     * @return void
     * @throws TestAssertException
     */
    public function assertNotInstanceOf(mixed $value, string $class): void {
        if (!($value instanceof $class)) throw new TestAssertException($class, get_class($value), "The value is an instance of the specified class and should not be.");
    }

    /**
     * Asserts that the first value is greater than the second value.
     *
     * This method checks if the first value is greater than the second value. If the first value
     * is not greater than the second value, an exception is thrown with the specified message.
     *
     * @param mixed $value1 The first value.
     * @param mixed $value2 The second value.
     * @return void
     * @throws TestAssertException
     */
    public function assertGreaterThan(mixed $value1, mixed $value2): void {
        if ($value1 <= $value2) throw new TestAssertException($value1, $value2, "The first value is not greater than the second value.");
    }

    /**
     * Asserts that the first value is greater than or equal to the second value.
     *
     * This method checks if the first value is greater than or equal to the second value. If the first value
     * is not greater than or equal to the second value, an exception is thrown with the specified message.
     *
     * @param mixed $value1 The first value.
     * @param mixed $value2 The second value.
     * @return void
     * @throws TestAssertException
     */
    public function assertGreaterThanOrEqual(mixed $value1, mixed $value2): void {
        if ($value1 < $value2) throw new TestAssertException($value1, $value2, "The first value is not greater than or equal to the second value.");
    }

    /**
     * Asserts that the first value is less than the second value.
     *
     * This method checks if the first value is less than the second value. If the first value
     * is not less than the second value, an exception is thrown with the specified message.
     *
     * @param mixed $value1 The first value.
     * @param mixed $value2 The second value.
     * @return void
     * @throws TestAssertException
     */
    public function assertLessThan(mixed $value1, mixed $value2): void {
        if ($value1 >= $value2) throw new TestAssertException($value1, $value2, "The first value is not less than the second value.");
    }

    /**
     * Asserts that the first value is less than or equal to the second value.
     *
     * This method checks if the first value is less than or equal to the second value. If the first value
     * is not less than or equal to the second value, an exception is thrown with the specified message.
     *
     * @param mixed $value1 The first value.
     * @param mixed $value2 The second value.
     * @return void
     * @throws TestAssertException
     */
    public function assertLessThanOrEqual(mixed $value1, mixed $value2): void {
        if ($value1 > $value2) throw new TestAssertException($value1, $value2, "The first value is not less than or equal to the second value.");
    }

    // #endregion
}
