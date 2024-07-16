<?php

define('ROOT', realpath(__DIR__ . "/../../../") . "/");
define('ENV_PATH', ROOT . "env/");
define('OPHOSE_PATH', ROOT . "ophose/");
define('OPHOSE_APP_PATH', OPHOSE_PATH . "app/");

include_once(__DIR__ . '/util/file_util.php');
include_once(__DIR__ . '/util/config_util.php');
include_once(__DIR__ . '/util/str_util.php');
include_once(__DIR__ . '/util/app_util.php');
include_once(__DIR__ . '/util/array_util.php');
include_once(__DIR__ . '/util/numeric_util.php');
include_once(__DIR__ . '/util/header_util.php');

include_once(__DIR__ . '/classes/env_class.php');
include_once(__DIR__ . '/classes/request.php');
include_once(__DIR__ . '/classes/response/directive.php');
include_once(__DIR__ . '/classes/response.php');
include_once(__DIR__ . '/classes/session.php');
include_once(__DIR__ . '/classes/cookie.php');
include_once(__DIR__ . '/classes/with.php');
include_once(__DIR__ . '/classes/cmd_class.php');
include_once(__DIR__ . '/classes/resource/template.php');
include_once(__DIR__ . '/classes/numeric/duration.php');
include_once(__DIR__ . '/classes/events/Event.php');

use Ophose\Configuration;

use function Ophose\configuration;

define('CONFIG', configuration(ROOT . 'project.oconf')->get());

class AutoLoader {

    public static function loadEnvironment(string $namespace) {
        $namespace = str_replace("\\", "/", $namespace);
        $namespaces = explode("/", $namespace);
        // Absolute path
        $path = o_realpath(ROOT . $namespace . ".php");
        // Local environment path
        if(!$path) $path = o_realpath(ENV_PATH . $namespaces[0] . '/src/' . implode('/', array_slice($namespaces, 1)) . ".php");
        // External environment path
        if(!$path) $path = o_realpath(ENV_PATH . 'ext/' . $namespaces[0] . '/' . $namespaces[1] . '/src/' . implode('/', array_slice($namespaces, 2)) . ".php");

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
        return $path;
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