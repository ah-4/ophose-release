<?php

namespace Ophose\Environment;

use AutoLoader;
use Ophose\Response;
use Ophose\Env;
use function Ophose\response;

include_once ROOT . '/ophose/app/request/security.php';

class EnvRequestProcessor {

    /**
     * @var string $url the url of the request
     */
    private string $url;

    /**
     * @var string $request_type the type of the request
     */
    private string $request_type;

    /**
     * Creates a new instance of the EnvRequestProcessor
     *
     * @param string $url the url of the request
     * @param string $request_type the type of the request
     */
    public function __construct(string $url, string $request_type) {
        $this->url = $url;
        $this->request_type = $request_type;
    }

    public function run() {

        // If env request is invalid
        $requestiInfos = explode('/', $this->url);

        if(count($requestiInfos) < 1) {
            response()->json([
                "error" => "Invalid environment request"
            ], 400);
            return Response::sendLastResponseAndDie();
        }

        $envName = __fixPath($requestiInfos[0]);
        $endpointOffset = 1;
        if(AutoLoader::getEnvironmentPath($envName) === false && count($requestiInfos) >= 2) {
            $envName = __fixPath($requestiInfos[0] . '/' . $requestiInfos[1]);
            $endpointOffset = 2;
            if(AutoLoader::getEnvironmentPath($envName) === false) {
                response()->json([
                    "error" => "No such environment $envName"
                ], 400);
                return Response::sendLastResponseAndDie();
            }
        }

        $envPath = AutoLoader::getEnvironmentPath($envName);

        // Handle not found environment error
        if (!$envPath) {
            response()->json([
                "error" => "No such environment $envName"
            ], 400);
            return Response::sendLastResponseAndDie();
        }

        $endpoint = __fixPath(implode('/', array_slice($requestiInfos, $endpointOffset)));

        switch($this->request_type) {
        case 'API':

            if(trim($endpoint) == '') {
                response()->json([
                    "error" => "No endpoint provided"
                ], 400);
                return Response::sendLastResponseAndDie();
            }

            $loaded = Env::getEnvironment($envPath);
            if(!$loaded) {
                response()->json([
                    "error" => "Unable to load environment $envName. Please try again later."
                ], 500);
                return Response::sendLastResponseAndDie();
            }

            $loaded->endpoints();
            $loaded->runEndpoint($endpoint);
            return Response::sendLastResponseAndDie();
        case "JS":
            if($endpoint == '') {
                response()->file($envPath . '/env.js');
            }else{
                response()->file($envPath . '/js/' . $endpoint . '.js');
            }
            return Response::sendLastResponseAndDie();
        default:
            response()->json([
                "error" => "Invalid environment request"
            ], status: 400);
            return Response::sendLastResponseAndDie();
        }

    }
}