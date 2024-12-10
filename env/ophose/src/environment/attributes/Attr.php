<?php


namespace Ophose\Environment\Attributes;

#[\Attribute]
abstract class Attr {

    /**
     * @var AttributePolicy $policy the policy of the attribute
     */
    protected AttributePolicy $policy;

    /**
     * @var bool $passed whether the attribute passed
     */
    private bool $passed = true;

    /**
     * Checks the attribute
     */
    public function check() {
        $this->onCheck();
        if(!$this->passed) $this->onFail();
    }

    /**
     * Sets the policy of the attribute
     *
     * @param AttributePolicy $policy the policy of the attribute
     */
    public function setPolicy(AttributePolicy $policy) {
        $this->policy = $policy;
    }

    /**
     * Marks the attribute as failed
     */
    protected final function fail() {
        $this->passed = false;
    }

    /**
     * Returns whether the attribute passed
     *
     * @return bool whether the attribute passed
     */
    public function passed(): bool {
        return $this->passed;
    }

    /**
     * This function will run before the endpoint is reached. You should check the policy here
     * and returns `$this->fail()` if the policy is not met.
     */
    abstract protected function onCheck();

    /**
     * This function will run if the policy is not met. Typically, you should return a response here.
     */
    abstract protected function onFail();

}