<?php

namespace Ophose\Environment\Attributes;

class AttributePolicy {

    private array $params;

    public function __construct(array $params) {
        $this->params = $params;
    }

    public function __get($name) {
        return $this->params[$name] ?? null;
    }

    public function __isset($name) {
        return isset($this->params[$name]);
    }

    public function has(string $name): bool {
        return isset($this->params[$name]);
    }

}