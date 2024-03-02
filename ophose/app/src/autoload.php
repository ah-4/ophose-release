<?php

define('ROOT', realpath(__DIR__ . "/../../../") . "/");
define('ENV_PATH', ROOT . "env/");
define('OPHOSE_PATH', ROOT . "ophose/");
define('OPHOSE_APP_PATH', OPHOSE_PATH . "app/");

include_once(__DIR__ . '/util/file_util.php');
include_once(__DIR__ . '/util/config_util.php');
include_once(__DIR__ . '/util/str_util.php');
include_once(__DIR__ . '/util/array_util.php');

include_once(__DIR__ . '/classes/env_class.php');
include_once(__DIR__ . '/classes/request.php');
include_once(__DIR__ . '/classes/response/directive.php');
include_once(__DIR__ . '/classes/response.php');
include_once(__DIR__ . '/classes/session.php');
include_once(__DIR__ . '/classes/with.php');
include_once(__DIR__ . '/classes/cmd_class.php');

session_start();

use Ophose\Configuration;

define('CONFIG', Configuration::get(ROOT . 'project.oconf'));

class AutoLoader {

    private static function load($confPath, $className) {
        $envConfig = Configuration::get($confPath . "/env.oconf");
        if(!isset($envConfig["autoload"])) return false;
    
        $autoload = $envConfig["autoload"];
        foreach($autoload as $autoloadPath) {
            if(str_ends_with($autoloadPath, "/") !== "/") $autoloadPath .= "/";
            $filePath = $confPath . "/" . $autoloadPath . $className . ".php";
            if(!file_exists($filePath)) return false;
            include_once $filePath;
            return true;
        }

        return false;
    }
    
    public static function loadEnvironment($className) {
        $classNameInfos = explode('\\', $className);
        $className = end($classNameInfos);
        $envName = implode('/', array_slice($classNameInfos, 0, -1));
        
        $envPath = self::getEnvironmentPath($envName);
        if(!$envPath) return false;
        return self::load($envPath, $className);
    }

    private static function getPathIgnoringCase($envPath, $path) {
        $split = explode('/', $path, 2);
        if(count($split) > 1 && empty($split[1])) {
            array_pop($split);
        }
        $envs = scandir($envPath);
        foreach($envs as $env) {
            if(strtolower($env) == strtolower($split[0])) {
                if(count($split) == 1) {
                    return $envPath . '/' . $env;
                } else {
                    return self::getPathIgnoringCase($envPath . $env, $split[1]);
                }
            }
        }
        return false;
    }

    /**
     * Returns the environment path from the given name or false if not found.
     *
     * @param string $envName the environment name
     * @return string|false the environment path or false if not found
     */
    public static function getEnvironmentPath(string $envName) {

        $envName = preg_replace('/\:/i', '/', $envName, 1);

        $currentEnvPath = ENV_PATH . $envName;
        if(!is_dir($currentEnvPath)) {
            $currentEnvPath = self::getPathIgnoringCase(ENV_PATH, $envName);
            if(!$currentEnvPath) {
                $currentEnvPath = ENV_PATH . '.ext/' . $envName;
                if(!is_dir($currentEnvPath)) {
                    $currentEnvPath = self::getPathIgnoringCase(ENV_PATH . '.ext/', $envName);
                    if(!is_dir($currentEnvPath)) {
                        return false;
                    }
                }
            }
        }
        if (file_exists($currentEnvPath . '/env.oconf') || file_exists($currentEnvPath . '/env.php')) return $currentEnvPath;
        return false;
    }

}

spl_autoload_register('AutoLoader::loadEnvironment');