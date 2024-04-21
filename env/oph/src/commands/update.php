<?php

$current_version = CONFIG['ophose']['version'] ?? null;

if($current_version === null) {
    echo "No version found in config file... Version should be specified at: ophose.version \n";
    exit(1);
}

$version_url = "https://github.com/ah-4/ophose-release/archive/refs/tags/" . $current_version . ".zip";
$version_file = sys_get_temp_dir() . "/ophose-" . $current_version . ".zip";

echo "Downloading Ophose version: " . $current_version . "...\n";

$download = file_get_contents($version_url);

if($download === false) {
    echo "Failed to download Ophose version: " . $current_version . "\n";
    exit(1);
}

file_put_contents($version_file, $download);

echo "Extracting Ophose version: " . $current_version . "...\n";

$to_replace = [
    'env/oph/',
    'ophose/',
    '.htaccess',
    'ocl'
];

$zip = new ZipArchive;
$res = $zip->open($version_file);
$tmp_dir = sys_get_temp_dir() . "/ophose-" . $current_version;
if(!file_exists($tmp_dir)) {
    mkdir($tmp_dir, 0777, true);
}

// Extract zip files starting with $to_replace into current directory
for($i = 0; $i < $zip->numFiles; $i++) {
    $original_filename = $zip->getNameIndex($i);
    $filename = str_replace('\\', '/', $original_filename);
    $filename = explode('/', $filename, 2)[1] ?? $filename;
    $extract = false;
    foreach($to_replace as $replace) {
        if((strpos($filename, $replace) === 0 && str_ends_with($replace, '/')) || $filename === $replace) {
            $extract = true;
            break;
        }
    }

    if($extract && !str_ends_with($filename, '/')) {
        $zip->extractTo($tmp_dir, $original_filename);
        copy($tmp_dir . "/" . $original_filename, ROOT . "/" . $filename);
    }
}

$zip->close();
o_rm_dir_recursive($tmp_dir);

echo "Update complete!\n";