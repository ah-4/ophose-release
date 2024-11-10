<?php

namespace Ophose\Command;

use Ophose\Command\Command;

use Ophose\Command\Exception\InvalidGenerateTypeException;
use function Ophose\Util\clr;
use function Ophose\Util\configuration;
use function Ophose\Util\shared;

class CmdGenerate extends Command {

    private $resource_name = null;

    private function findResourceGenerator() {
        foreach(shared('generators', true) as $generator) {
            $generator = require $generator;
            if(strtolower($generator->getResourceName()) == strtolower($this->resource_name)) return $generator;
        }
        throw new InvalidGenerateTypeException($this->resource_name);
    }

    private function processResouceName() {
        $resource_name = $this->getArguments()[0] ?? null;
        if($resource_name === null) throw new InvalidGenerateTypeException();
        $this->resource_name = $resource_name;
    }

    public function run() {
        echo clr("&yellow;Generating resource...\n");
        try {
            $this->processResouceName();
            $generator = $this->findResourceGenerator();
            $generator->setArgs(array_slice($this->getArguments(), 1));
            $generator->generate();
            echo clr('&green;Resource &yellow;' . $this->resource_name . '&green; generated successfully.');
        } catch(InvalidGenerateTypeException $e) {
            echo clr('&red;' . $e->getMessage());
            return;
        } catch(\Exception $e) {
            echo clr('&red;An error occurred while generating the resource: ' . $e->getMessage());
            return;
        }
        
    }

}