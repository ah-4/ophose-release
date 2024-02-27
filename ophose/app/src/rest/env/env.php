<?php

use Ophose\Response;
use Ophose\Env;

include_once(__DIR__ . "/../../../request/security.php");

// If env request is invalid
$requestiInfos = explode('/', REQUEST_FIXED_URL);

if(count($requestiInfos) <= 1) {
    Response::json([
        "error" => "Invalid environment request"
    ], 400);
    die();
}

$envName = __fixPath($requestiInfos[0]);
$endpointOffset = 1;
if(AutoLoader::getEnvironmentPath($envName) === false && count($requestiInfos) >= 2) {
    $envName = __fixPath($requestiInfos[0] . '/' . $requestiInfos[1]);
    $endpointOffset = 2;
    if(AutoLoader::getEnvironmentPath($envName) === false) {
        Response::json([
            "error" => "No such environment $envName"
        ], 400);
        die();
    }
}

$envPath = AutoLoader::getEnvironmentPath($envName);

// Handle not found environment error
if (!$envPath) {
    Response::json([
        "error" => "No such environment $envName"
    ], 400);
}

$endpoint = __fixPath(implode('/', array_slice($requestiInfos, $endpointOffset)));

switch(ENV_REQUEST) {
case 'API':

    if(trim($endpoint) == '') {
        Response::json([
            "error" => "No endpoint provided"
        ], 400);
        die();
    }

    $loaded = Env::getEnvironment($envPath);
    if(!$loaded) {
        Response::json([
            "error" => "Unable to load environment $envName. Please try again later."
        ], 500);
    }

    $loaded->endpoints();
    $loaded->runEndpoint($endpoint);
    break;
case "JS":
    if($endpoint == '') {
        Response::file($envPath . '/env.js');
    }else{
        Response::file($envPath . '/js/' . $endpoint . '.js');
    }
    break;
default:
    Response::json([
        "error" => "Invalid environment request"
    ], 400);
    break;
}