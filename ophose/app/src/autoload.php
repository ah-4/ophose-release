<?php

use function Ophose\Util\configuration;

define('ROOT', realpath(__DIR__ . "/../../../") . "/");
define('APP_PATH', ROOT . "app/");
define('ENV_PATH', ROOT . "env/");
define('OPHOSE_PATH', ROOT . "ophose/");
define('OPHOSE_APP_PATH', OPHOSE_PATH . "app/");
include_once(ENV_PATH . 'ophose/src/util/UtlFile.php');
include_once(ENV_PATH . 'ophose/src/util/Configuration.php');

class AutoLoader {

    public static function loadEnvironment(string $namespace) {
        $namespace = str_replace("\\", "/", $namespace);
        $namespaces = explode("/", $namespace);
        // Absolute path
        $path = o_realpath(ROOT . $namespace . ".php");
        // Local environment path
        if(!$path) $path = o_realpath(ENV_PATH . $namespaces[0] . '/src/' . implode('/', array_slice($namespaces, 1)) . ".php");
        // External environment path
        if(!$path && sizeof($namespaces) >= 2) $path = o_realpath(ENV_PATH . 'ext/' . $namespaces[0] . '/' . $namespaces[1] . '/src/' . implode('/', array_slice($namespaces, 2)) . ".php");

        if($path) {
            include_once($path);
            return true;
        }
        return false;
    }

    public static function getEnvironmentPath(string $envPath) {
        $envPath = str_replace("\\", "/", $envPath);
        $envPath = str_replace(":", "/", $envPath);
        $path = o_realpath(ENV_PATH . $envPath);
        if(!$path) $path = o_realpath(ENV_PATH . 'ext/' . $envPath);
        if($path) return (file_exists($path . '/src') || file_exists($path . '/env.php') || file_exists($path . '/env.oconf')) ? $path : false;
        return false;
    }

}

spl_autoload_register('AutoLoader::loadEnvironment');

// Preload all environments in the env folder
foreach (o_get_files_recursive(ENV_PATH, 'oconf') as $file) {
    // Check if filename is 'env.oconf'
    if (basename($file) == 'env.oconf') {
        $env = configuration($file);
        $folder = dirname($file);
        $preload = $env->get('preload');
        if($preload) {
            foreach ($preload as $preloadFilepath) {
                $preloadFile = $folder . '/src/' . $preloadFilepath;
                if(file_exists($preloadFile)) {
                    include_once($preloadFile);
                }
            }
        }
    }
}