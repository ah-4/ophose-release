<?php

namespace Ophose;

/**
 * With class
 */
class With {

    /**
     * Stores the condition
     *
     * @var boolean
     */
    private bool $condition;

    /**
     * Stores the arguments from the send method
     *
     * @var array
     */
    private array $arguments;

    /**
     * Stores the fail callback
     *
     * @var callable
     */
    private $failCallback;

    /**
     * Constructor
     *
     * @param boolean $condition the condition to check
     */
    public static function condition(bool $condition) : With {
        return new With($condition);
    }

    /**
     * Constructor
     *
     * @param boolean $condition the condition to check
     */
    private function __construct(bool $condition) {
        $this->condition = $condition;
    }

    /**
     * Sends the arguments
     *
     * @param mixed ...$arguments the arguments to send
     * @return With the current instance
     */
    public function send(...$arguments) : With {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * Sets the fail callback
     *
     * @param callable $callback the fail callback
     * @return With the current instance
     */
    public function otherwise(callable $callback) : With {
        $this->failCallback = $callback;
        return $this;
    }

    /**
     * Goes to the callback if the condition is true, otherwise goes to the fail callback
     *
     * @param callable $callback the callback to go to
     * @return void
     */
    public function go(callable $callback) {
        if($this->condition) {
            $callback(...$this->arguments);
        } else {
            $callback = $this->failCallback;
            $callback();
        }
    }

}
