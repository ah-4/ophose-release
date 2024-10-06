<?php

namespace Ophose;

use Ophose\Command\CmdBuild;
use Ophose\Command\CmdInstall;
use Ophose\Command\CmdPublish;
use Ophose\Command\CmdUpdate;
use Ophose\Command\CmdTest;
use Ophose\Command\CmdTrigger;
use Ophose\Env;

return new class extends Env {

    public function commands()
    {
        $this->command('install', CmdInstall::class);
        $this->command('trigger', CmdTrigger::class);
        $this->command('update', CmdUpdate::class);
        $this->command('build', CmdBuild::class);
        $this->command('test', CmdTest::class);
        $this->command('publish', CmdPublish::class);
    }

    public function endpoints()
    {
        $this->endpoint('hello_world', function () {
            response("Hello, World!");
        }, true);
    }

};