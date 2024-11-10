<?php

namespace Ophose\Command\Exception;

class InvalidGenerateTypeException extends \Exception {
    public function __construct($type = null) {
        if($type === null) {
            parent::__construct("The generate type is invalid. Please specify a valid type.");
            return;
        }
        parent::__construct("The generate type '$type' is invalid. Please specify a valid type.");
    }
}