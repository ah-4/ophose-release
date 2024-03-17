<?php

use Ophose\Env;

$envName = COMMAND->getArguments()[0] ?? null;
if($envName === null) {
    echo "Insufficient arguments...\n";
    echo "Usage: php ocl oph trigger <environment> <trigger>\n";
    die(1);
}
$envPath = AutoLoader::getEnvironmentPath($envName);
if($envPath === false) {
    echo "This environment does not exist... Please check your environment name.\nEnvironment: " . $envName . "\n";
    die(1);
}

define('TRIGGERS', [
    "install" => [
        "requireEnv" => true,
        "message" => "Installing the environment.",
        "callback" => function($env) {
            $env->onInstall();
        }
    ]
    ]);

$trigger = strtolower(COMMAND->getArguments()[1] ?? null);
if(!$trigger) {
    echo "Insufficient arguments...\n";
    echo "Usage: php ocl oph trigger <environment> <trigger>\n";
    die(1);
}

if(!array_key_exists($trigger, TRIGGERS)) {
    echo "This trigger does not exist... Please check your trigger name.\nTrigger: " . $trigger . "\n";
    echo "Available triggers: " . implode(", ", array_keys(TRIGGERS)) . "\n";
    die(1);
}

$trigger = TRIGGERS[$trigger];
$args = [];
if($trigger["requireEnv"] ?? false) {
    $env = Env::getEnvironment($envPath);
    if($env === false) {
        echo "This environment does not have Environment class... Please check your environment name.\nEnvironment: " . $envName . "\n";
        die(1);
    }
    $args[] = $env;
}

echo $trigger["message"] . "\n";
$trigger["callback"](...$args);
echo "Done.\n";