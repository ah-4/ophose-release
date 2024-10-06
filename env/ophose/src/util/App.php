<?php

namespace Ophose\Util;

class App {

    /**
     * Return the path to the app folder with the specified path (relative to the app folder) if provided
     *
     * @param string $inAssetPath The path to the asset file (relative to the app folder)
     * @return string The path to the asset file
     */
    public static function getAppPath(string $inAssetPath = "") {
        return ROOT . '/app/' . $inAssetPath;
    }

    /**
     * Export a sample asset file to the app folder at specified path. Note
     * that the file will be created only if original asset file exists and the new asset file doesn't exist
     * unless the overwrite parameter is set to true.
     *
     * @param string $assetPath the path to the original asset file
     * @param string $inAssetPath the path to the new asset file (relative to the app folder)
     * @param bool $overwrite whether to overwrite the file if it already exists
     * @return string|bool The path to the new asset file or false if the original asset file doesn't exist
     */
    public static function export(string $assetPath, string $inAssetPath, bool $overwrite = false) {
        if(!file_exists($assetPath)) {
            echo "The original asset file doesn't exist\n";
            return false;
        }
        $newAssetPath = self::getAppPath($inAssetPath);
        if (file_exists($newAssetPath) && !$overwrite) return $newAssetPath;
        if (!file_exists(dirname($newAssetPath))) mkdir(dirname($newAssetPath), 0777, true);
        if(copy($assetPath, $newAssetPath)) return $newAssetPath;
        return false;
    }

}