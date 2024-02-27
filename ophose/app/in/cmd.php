<?php

include_once(__DIR__ . '/../src/autoload.php');

use Ophose\Command;
use Ophose\Env;

if($argc < 3) {
    echo "Insufficient arguments...\n";
    echo "Usage: php ocl <environment> <command> [arguments]\n";
    die(1);
}

$envName = $argv[1];
$envPath = AutoLoader::getEnvironmentPath($envName);

if($envPath === false) {
    echo "This environment does not exist... Please check your environment name.\nEnvironment: " . $envName . "\n";
    die(1);
}

$env = Env::getEnvironment($envPath);
define('COMMAND', new Command($argv));
$env->commands();
$env->runCommand(COMMAND);