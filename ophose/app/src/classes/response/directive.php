<?php

namespace Ophose;

class Directive {

    private array $directives = [];

    private function addDirective(string $directive, mixed $value) {
        $this->directives[] = [
            "type" => $directive,
            "data" => $value
        ];
    }

    public function getDirectives() {
        return $this->directives;
    }

    public function redirect(string $url) {
        $this->addDirective('redirect', $url);
        return $this;
    }

    public static function get() {
        return new Directive();
    }

}