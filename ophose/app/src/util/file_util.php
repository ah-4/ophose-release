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

/**
 * Removes a directory and its content
 * @param string $dir the directory
 * @return bool
 */
function o_rm_dir_recursive($dir)
{
    if(!is_dir($dir)) return false;
    $dir = realpath($dir);
    foreach (scandir($dir) as $file) {
        if ($file == '.' || $file == '..') continue;
        if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
            o_rm_dir_recursive($dir . DIRECTORY_SEPARATOR . $file);
        } else {
            unlink($dir . DIRECTORY_SEPARATOR . $file);
        }
    }
    rmdir($dir);
    return true;
}

/**
 * Returns the real path of the given path ignoring case sensitivity
 * @param string $path the path
 * @return string|false the realpath or false if the path does not exist
 */
function o_realpath($path) {
    // Remplacer les backslashes par des slashes pour la compatibilité
    $path = str_replace('\\', '/', $path);
    $path = str_replace('//', '/', $path);

    // Séparer le chemin en segments
    $segments = explode('/', $path);

    // Si le premier segment est vide, c'est un chemin absolu (Unix-like)
    if (empty($segments[0])) {
        $currentPath = '/';
        array_shift($segments);
    } else {
        // Chemin relatif ou absolu Windows
        if (preg_match('/^[a-zA-Z]:$/', $segments[0])) {
            // C'est un chemin absolu Windows (comme C:)
            $currentPath = $segments[0];
            array_shift($segments);
        } else {
            $currentPath = '';
        }
    }

    foreach ($segments as $segment) {
        // Lister les fichiers et dossiers dans le chemin courant
        $found = false;
        $directory = $currentPath ? $currentPath : '.';
        $files = scandir($directory);

        foreach ($files as $file) {
            if (strcasecmp($file, $segment) === 0) {
                $currentPath = $currentPath ? $currentPath . '/' . $file : $file;
                $found = true;
                break;
            }
        }

        if (!$found) {
            return false; // Si le segment n'est pas trouvé, retourner false
        }
    }

    // Utiliser realpath pour obtenir le chemin canonique
    return realpath($currentPath);
}