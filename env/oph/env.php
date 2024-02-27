<?php

namespace Oph;

use Ophose\Env;
use Oph\Build;

return new class extends Env {

    public function commands()
    {
        $this->command('build', function () {
            $b = new Build();
            $b->buildJSFiles();
            include_once __DIR__ . '/src/commands/build-ophose.php';
            compile();
        });

        $this->command('install', function () {
            include_once __DIR__ . '/src/commands/install.php';
        });
    }

};