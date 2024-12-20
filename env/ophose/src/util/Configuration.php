<?php
namespace Ophose\Util;

class Configuration {

    private $configurations = null;

    public function __construct(string $path)
    {
        if(!file_exists($path)) $path = $path . '.oconf';
        if(!file_exists($path)) return;
        $array = json_decode(file_get_contents($path), true);
        if(file_exists($path . '.local')) {
            $localArray = json_decode(file_get_contents($path . '.local'), true);
            $array = array_replace_recursive($array, $localArray);
        }
        $this->configurations = $array;
    }

    public function get(string $key = null, mixed $default = null) {
        if ($key === null) return $this->configurations;
        $keys = explode('.', $key);
        $value = $this->configurations;
        foreach ($keys as $key) {
            if (!isset($value[$key])) return $default;
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * Export a sample configuration file to the configurations folder at specified path. Note
     * that the file will be created only if it doesn't exist.
     *
     * @param string $configPath the path to the original configuration file
     * @param string $inConfigPath the path to the new configuration file (relative to the configurations folder)
     * @return string|bool The path to the new configuration file or
     */
    public function export(string $inConfigPath) {
        $newConfigPath = ROOT . 'app/configuration/' . $inConfigPath . '.oconf';
        if(file_exists($newConfigPath)) return $newConfigPath;
        if(!file_exists(dirname($newConfigPath))) mkdir(dirname($newConfigPath), 0777, true);
        file_put_contents($newConfigPath, json_encode($this->configurations, JSON_PRETTY_PRINT));
        return $newConfigPath;
    }

}

/**
 * Returns a Configuration object from the specified path. (project.oconf by default)
 *
 * @param string|null $configPath the path to the configuration file
 * @return Configuration|null the Configuration object or null if the file does not exist
 */
function configuration(string $configPath = null) {
    // Absolute path
    if(!$configPath) $configPath = ROOT . 'project.oconf';
    $path = o_realpath($configPath);
    // In configurations folder
    if(!$path) $path = o_realpath(ROOT . 'app/configuration/' . $configPath . '.oconf');
    if($path) return new Configuration($path);
    return null;
}