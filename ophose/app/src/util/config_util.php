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

}