<?php

namespace Ophose;

use Ophose\Command;
use ReflectionFunction;
use ReflectionMethod;

class Env
{
    /**
     * Returns true if the environment is valid.
     *
     * @param string $envPath the environment path
     * @return bool
     */
    public static function environmentExists(string $envPath) {
        return file_exists($envPath . '/env.oconf') || file_exists($envPath . '/env.php');
    }

    /**
     * Returns the environment class from the given path or false if not valid.
     *
     * @param string $envPath the environment path
     * @return Env|false the environment instance or false if not valid
     */
    public static function getEnvironment(string $envPath) {
        if(!self::environmentExists($envPath)) return false;
        $envFile = $envPath . '/env.php';
        if(!file_exists($envFile)) return false;
        $env = include $envFile;
        $instance = new $env();
        if(!($instance instanceof Env)) return false;
        $instance->envPath = $envPath;
        return $instance;
    }

    // Class methods

    private $envPath = null;
    private $envEndpoints = [];
    private $envCommands = [];

    /**
     * Returns the configuration of the environment.
     *
     * @return array|null the configuration or null if not found
     */
    protected function getConfiguration() {
        if($this->envPath == null) return null;
        return configuration($this->envPath . '/env.oconf');
    }

    protected final function getEndpoint(string $endpoint) {
        $e = $this->envEndpoints[strtolower($endpoint)] ?? null;
        if($e) return $e;
        foreach($this->envEndpoints as $envEndpoint) {
            $envEndpointInfos = explode('/', $envEndpoint["endpoint"]);
            $endpointInfos = explode('/', $endpoint);
            if(count($envEndpointInfos) != count($endpointInfos)) continue;
            $params = [];
            $match = true;
            for($i = 0; $i < count($envEndpointInfos); $i++) {
                $envEndpointInfo = strtolower($envEndpointInfos[$i]);
                $endpointInfo = strtolower($endpointInfos[$i]);
                if($envEndpointInfo == $endpointInfo) continue;
                if(str_starts_with($envEndpointInfo, '_')) {
                    $params[] = $endpointInfos[$i];
                    continue;
                }
                $match = false;
                break;
            }
            if($match) {
                $envEndpoint["params"] = $params;
                return $envEndpoint;
            }
        }
    }

    protected final function getCommand(string $command) {
        return $this->envCommands[strtolower($command)] ?? null;
    }

    public final function runEndpoint(string $endpoint) {
        $envEndpoint = $this->getEndpoint($endpoint);

        // Handle not found endpoint error
        if(!$envEndpoint) {
            Response::json([
                "error" => "No such endpoint $endpoint"
            ], 400);
        }

        // Check CSRF Token if required
        $requestCSRFToken = getallheaders()["X-Csrf-Token"] ?? null;
        if ($envEndpoint["csrf"] && ($requestCSRFToken == null || $requestCSRFToken !== Cookie::get("CSRF_TOKEN"))) {
            Response::json(["error" => "CSRF Token not valid."], 403);
        }

        // Check request method
        if(!empty($envEndpoint["methods"]) && !in_array("*", $envEndpoint["methods"]) && !in_array($_SERVER["REQUEST_METHOD"], $envEndpoint["methods"])) {
            Response::json(["error" => "Invalid request method"], 400);
        }

        // Check required parameters
        $arrayToCheck = $_SERVER["REQUEST_METHOD"] == "GET" ? $_GET : $_POST;
        if($_SERVER["REQUEST_METHOD"] == "GET") {
            foreach($envEndpoint["required"] as $required) {
                if(!isset($arrayToCheck[$required])) {
                    Response::json(["error" => "Missing required parameter $required"], 400);
                }
            }
        }

        // Run callback
        $params = $envEndpoint["params"] ?? [];
        $callback = $envEndpoint["callback"];
        if(is_array($callback)) {
            $class = $callback[0];
            if(class_exists($class)) {
                $callback = [new $class(), $callback[1]];
            }
        }
        $params = $this->processAutofrom($callback, $params);
        $this->processAttributes($callback, $params);
        call_user_func_array($callback, $params);
    }

    private function getReflection(array|string $callback) : ReflectionMethod|ReflectionFunction {
        if(is_array($callback)) {
            $class = $callback[0];
            $method = $callback[1];
            return new ReflectionMethod($class, $method);
        }
        return new ReflectionFunction($callback);
    }

    private function processAutofrom($callback, $params) {
        $reflection = $this->getReflection($callback);
        $parameters = $reflection->getParameters();
        $newParams = [];
        for($i = 0; $i < count($parameters); $i++) {
            $parameter = $parameters[$i];
            $type = $parameter->getType();
            if($type && class_exists($type->getName())) {
                if(is_callable($type->getName() . "::autofrom")) {
                    $newParams[] = $type->getName()::autofrom($params[$i]);
                } else {
                    $newParams[] = $params[$i];
                }
            } else {
                $newParams[] = $params[$i];
            }
        }
        return $newParams;
    }

    private function processAttributes($callback, $params) {
        $reflection = $this->getReflection($callback);
        $attributes = $reflection->getAttributes();
        foreach($attributes as $attribute) {
            $attribute = new ($attribute->getName())(...$params);
        }
        return $params;
    }

    public final function runCommand(Command $command) {
        $envCommand = $this->getCommand($command->getCommandName());

        if($envCommand === null) {
            echo "No such command " . $command->getCommandName() . "\n";
            die(1);
        }

        $envCommand["callback"]($command);
    }

    /**
     * This functions is called when an endpoint of the environment is requested.
     * It should be overriden by your environment.
     * 
     * @return void
     */
    public function endpoints() {
        return;
    }

    /**
     * This functions is called when the environment is initialized and request a command.
     * It could be overriden by your environment.
     * 
     * @return void
     */
    public function commands() {
        return;
    }

    /**
     * This function is used to register an endpoint.
     * 
     * @param string $endpoint the endpoint
     * @param callable $callback the callback
     * @param bool $csrf if the endpoint requires the CSRF token
     * @param array $methods the methods accepted by the endpoint
     * @param array $required the required parameters
     * @return void
     */
    protected final function endpoint(
        string $endpoint,
        array|string|callable $callback,
        bool $csrf = false,
        array $methods = [],
        array $required = []
    ) {
        $this->envEndpoints[strtolower($endpoint)] = [
            "endpoint" => $endpoint,
            "callback" => $callback,
            "csrf" => $csrf,
            "methods" => $methods,
            "required" => $required
        ];
    }

    /**
     * This function is used to register a command.
     * 
     * @param string $commandName the command
     * @param callable $callback the callback
     * @param string $description the description of the command
     * @return void
     */
    protected final function command(
        string $commandName,
        callable $callback,
        string $description = null
    ) {
        $this->envCommands[strtolower($commandName)] = [
            "command"=> $commandName,
            "callback"=> $callback,
            "description"=> $description
        ];
    }

    /**
     * Returns the path of the environment.
     *
     * @return string
     */
    public final function getPath() {
        return $this->envPath;
    }

    // Events

    /**
     * This function is called when the environment is installed.
     * It could be overriden by your environment.
     * 
     * @return void
     */
    public function onInstall() {
        return;
    }

}