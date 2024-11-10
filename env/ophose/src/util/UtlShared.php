<?php

namespace Ophose\Util;

/**
 * Returns all environments.
 */
function all_environments() {
    $folders = [];
    foreach (o_get_files_recursive(ENV_PATH, 'oconf') as $file) {
        if (basename($file) == 'env.oconf') {
            $folder = dirname($file);
            if(str_contains($folder, '/export') || str_contains($folder, '\\export')) continue;
            $folders[] = $folder;
        }
    }
    return $folders;
}

/**
 * Returns all shared paths from environments.
 */
function shared(string $path, bool $return_files = false) {
    $shared = [];
    foreach (all_environments() as $folder) {
        $sharedPath = $folder . '/shared/' . $path;
        if(file_exists($sharedPath)) {
            if($return_files) {
                $shared = array_merge($shared, o_get_files_recursive($sharedPath, 'php'));
            } else {
                $shared[] = $sharedPath;
            }
        }
    }
    return $shared;
}