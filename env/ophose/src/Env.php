<?php

namespace Ophose;

use Closure;
use Ophose\Environment\EnvEndpoint;
use Ophose\Command\Command;
use Ophose\Environment\Attributes\AttributePolicy;
use ReflectionFunction;
use ReflectionMethod;

use function Ophose\Util\configuration;

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
    private $envMiddlewares = [];

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
            $envEndpointInfos = explode('/', $envEndpoint->getEndpoint());
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
                $envEndpoint->params($params);
                return $envEndpoint->lock();
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
            return response()->json([
                "error" => "No such endpoint $endpoint"
            ], 400);
        }

        // Check CSRF Token if required
        $requestCSRFToken = getallheaders()["X-Csrf-Token"] ?? null;
        $secureKey = getallheaders()["X-Secure-Key"] ?? null;
        if ($envEndpoint->requiresCsrf() &&
            ($requestCSRFToken == null || $requestCSRFToken !== Cookie::get("CSRF_TOKEN")) &&
            ($secureKey == null || $secureKey !== configuration()->get("secure_key")) &&
            (!defined('TEST_MODE') || !TEST_MODE)
        ) {
            return response()->json(["error" => "CSRF Token not valid."], 403);
        }

        // Check request method
        if(!empty($envEndpoint->getMethods()) && !in_array("*", $envEndpoint->getMethods()) && !in_array($_SERVER["REQUEST_METHOD"], $envEndpoint->getMethods())) {
            return response()->json(["error" => "Invalid request method"], 400);
        }

        // Run callback
        $params = $envEndpoint->getParams() ?? [];
        $callback = $envEndpoint->getCallback();
        if(is_array($callback)) {
            $class = $callback[0];
            if(class_exists($class)) {
                $callback = [new $class(), $callback[1]];
            }
        }
        $params = $this->processAutofrom($callback, $params);
        if(Response::getLastResponse()) return;
        $this->processAttributes($callback, $params);
        if(Response::getLastResponse()) return;
        call_user_func_array($callback, $params);
    }

    private function getReflection(array|string|Closure $callback) : ReflectionMethod|ReflectionFunction {
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
        $parameters = $reflection->getParameters();
        $p = [];
        for($i = 0; $i < count($parameters); $i++) {
            $p[$parameters[$i]->getName()] = $params[$i];
        }
        $attributePolicy = new AttributePolicy($p);
        foreach($attributes as $attribute) {
            $attribute = $attribute->newInstance();
            if(is_subclass_of($attribute, 'Ophose\Environment\Attributes\Attr')) {
                $attribute->setPolicy($attributePolicy);
                $attribute->check();
            }
            if(Response::getLastResponse()) return;
        }
        return $params;
    }

    public final function runCommand(Command $command) {
        $envCommand = $this->getCommand($command->getCommandName());

        if($envCommand === null) {
            echo "No such command " . $command->getCommandName() . "\n";
            die(1);
        }

        $callback = $envCommand["callback"];
        if(is_array($callback)) {
            $class = $callback[0];
            if(class_exists($class)) {
                $callback = [new $class(COMMAND->getAllArguments()), $callback[1]];
            }
        }
        if($callback instanceof Command) {
            $callback = [$callback, "run"];
        }
        if(is_string($callback) && class_exists($callback)) {
            $callback = [new $callback(COMMAND->getAllArguments()), "run"];
        }

        call_user_func($callback);
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
     * @param string $endpoint the endpoint (e.g. 'create' or 'edit/_id')
     * @param array|callable|string $callback the callback
     * @return EnvEndpoint the environment endpoint
     */
    protected final function endpoint(
        string $endpoint,
        array|callable|string $callback
    ) {
        $e = new EnvEndpoint($endpoint, $callback);
        $this->envEndpoints[strtolower($endpoint)] = $e;
        return $e;
    }

    /**
     * This function is used to register a command.
     * 
     * @param string $commandName the command
     * @param callable|Command $callback the callback or the command object
     * @param string $description the description of the command
     * @return void
     */
    protected final function command(
        string $commandName,
        string|callable|Command $callback,
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


    /**
     * Add a middleware to the environment.
     * 
     * @param string $request_path the request path expression (e.g. /post/upload)
     * @param Closure $callback the callback (should return)
     * @return void
     */
    public function middleware(string $request_path, Closure $callback) {
        $this->envMiddlewares[$request_path] = $callback;
    }

    /**
     * This function is called when the middlewares of a request are requested.
     *  It could be overriden by your environment.
     * 
     * @return void
     */
    public function middlewares() {
        return;
    }

}