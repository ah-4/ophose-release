<?php
namespace Ophose;

class Configuration{

    /**
     * Get the configuration from a .oconf file
     *
     * @param string $configPath The path to the configuration file
     * @return array|null The configuration array or null if the file doesn't exist
     */
    public static function get(string $configPath) {
        if (!file_exists($configPath)) {
            return null;
        }
        $configArray = json_decode(file_get_contents($configPath), true);

        if (file_exists($configPath . '.local')) {
            $configLocalArray = json_decode(file_get_contents($configPath . '.local'), true);
            $configArray = array_replace_recursive($configArray, $configLocalArray);
        }
        
        return $configArray;
    }

    /**
     * Export a sample configuration file to the configurations folder at specified path. Note
     * that the file will be created only if it doesn't exist.
     *
     * @param string $configPath the path to the original configuration file
     * @param string $inConfigPath the path to the new configuration file (relative to the configurations folder)
     * @return string|bool The path to the new configuration file or
     */
    public static function export(string $configPath, string $inConfigPath) {
        $configArray = self::get($configPath);
        if ($configArray === null) {
            echo "The original configuration file doesn't exist\n";
            return;
        }
        $newConfigPath = ROOT . '/app/configuration/' . $inConfigPath;
        if(!str_ends_with($newConfigPath, '.oconf')) $newConfigPath .= '.oconf';
        if (file_exists($newConfigPath)) return $newConfigPath;
        if (!file_exists(dirname($newConfigPath))) mkdir(dirname($newConfigPath), 0777, true);
        if(file_put_contents($newConfigPath, json_encode($configArray, JSON_PRETTY_PRINT))) return $newConfigPath;
        return false;
    }

    public static function import(string $inConfigPath) {
        $configPath = ROOT . '/app/configuration/' . $inConfigPath;
        if(!str_ends_with($configPath, '.oconf')) $configPath .= '.oconf';
        return self::get($configPath);
    }

}