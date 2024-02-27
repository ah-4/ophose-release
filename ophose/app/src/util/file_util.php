<?php

/**
 * Returns fixed path ('/my/path/' -> 'my/path')
 * @param string $path the path
 * @return string
 */
function __fixPath(string $path)
{
    $fixedPath = $path;
    if (substr($fixedPath, -1) == "/") {
        $fixedPath = substr($fixedPath, 0, strlen($fixedPath) - 1);
    }
    if (substr($fixedPath, 0, 1) == "/") {
        $fixedPath = substr($fixedPath, 1, strlen($fixedPath) - 1);
    }
    return $fixedPath;
}

/**
 * Returns the files in the given directory and its subdirectories
 * @param string $dir the directory
 * @param string|null $ext the file extension
 * @return array|bool the files or false if the directory does not exist
 */
function o_get_files_recursive($dir, $ext = null, $max_recursion = 512)
{
    if(!is_dir($dir)) return false;
    if($max_recursion <= 0) return false;
    $files = [];
    $dir = realpath($dir);
    $ext = strtolower($ext);

    foreach (scandir($dir) as $file) {
        if ($file == '.' || $file == '..') continue;
        if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
            $files = array_merge($files, o_get_files_recursive($dir . DIRECTORY_SEPARATOR . $file, $ext, $max_recursion - 1));
        } else {
            if ($ext) {
                if (strtolower(pathinfo($dir . $file, PATHINFO_EXTENSION)) == $ext) {
                    $files[] = $dir . DIRECTORY_SEPARATOR . $file;
                }
            } else {
                $files[] = $dir . $file;
            }
        }
    }

    return $files;
}