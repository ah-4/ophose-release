<?php

use Ophose\Configuration;
use Ophose\Env;

$dependencies = CONFIG["dependencies"];
define('OPHOSE_URL', "https://ophose.ah4.fr");
define('EXT_ENV_PATH', ROOT . "/env/.ext/");
define('CPN_ENV_PATH', ROOT . "/components/.ext/");

$shouldReinstall = COMMAND->hasOption('u');

$stats = [
    "installed" => 0,
    "failed" => 0,
    "re-installed" => 0
];

foreach($dependencies as $name=>$version) {
    $infos = explode("/",$name);

    // Check if the dependency name is valid
    if(count($infos) !== 2) {
        echo "Invalid dependency name: " . $name . "\n";
        $stats["failed"]++;
        continue;
    }

    // Define the author and the resource
    $author = $infos[0];
    $resource = $infos[1];

    // Fetch
    $url = OPHOSE_URL . "/@/resource/fetch/" . $author . "/" . $resource . "/" . $version;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        "apiKey" => CONFIG["api_key"]
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check if the request was successful
    if($status !== 200) {
        echo "Failed to install " . $name . " with version " . $version . ". Status: " . $status . "\n";
        echo "Message: " . $response . "\n";
        $stats["failed"]++;
        continue;
    }

    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5($name . $version) . ".zip";
    $response = json_decode($response, true);
    file_put_contents($path, base64_decode($response["data"]));
    $zip = new ZipArchive;
    $name = $response["name"];
    $author = $response["author"];
    if ($zip->open($path) === TRUE) {
        $env_path = $author . DIRECTORY_SEPARATOR . $name;
        if($response["type"] === "Component") {
            $env_path = CPN_ENV_PATH . $env_path;
        } else {
            $env_path = EXT_ENV_PATH . $env_path;
        }
        if(!file_exists($env_path)) {
            mkdir($env_path, 0777, true);
        }else{
            if($shouldReinstall) {
                echo "Re-installing " . $name . " with version " . $version . ".\n";
                o_rm_dir_recursive($env_path);
            }else{
                echo "Dependency " . $author . ":" . $name . " already exists.\n";
                $zip->close();
                unlink($path);
                if($response["type"] === "Environment") {
                    $env = Env::getEnvironment($env_path);
                    if(!$env) continue;
                    try {
                        echo "Running installation...\n";
                        $env->onInstall();
                        echo "Re-installed " . $name . " with version " . $version . ".\n";
                        $stats["re-installed"]++;
                    } catch(Error $e) {
                        echo "Failed to install " . $name . " with version " . $version . ".\n";
                        echo $e->getMessage() . "\n";
                        $stats["failed"]++;
                    }
                }
                continue;
            }
            
        }
        $zip->extractTo($env_path);
        $zip->close();
        unlink($path);
        echo "Dependency " . $author . ":" . $name . " with version " . $version . " installed.\n";
        $stats["installed"]++;
        $shouldInstall = true;

        if($response["type"] === "Environment") {
            $env = Env::getEnvironment($env_path);
            $conf_file = $env_path . "/env.oconf";
            if(file_exists($conf_file)) {
                $conf = Configuration::get($conf_file);
                if($conf && isset($conf["dependencies"]) && is_array($conf["dependencies"])) {
                    foreach($conf["dependencies"] as $dep_name) {
                        $p = AutoLoader::getEnvironmentPath($dep_name);
                        if(!$p) {
                            echo "Dependency " . $dep_name . " not found. This environment may not work properly.\n";
                            $shouldInstall = false;
                        }
                    }
                }
                
            }
            if($env && $shouldInstall) {
                try {
                    $env->onInstall();
                    echo "Installed " . $name . " with version " . $version . ".\n";
                    $stats["installed"]++;
                } catch(Error $e) {
                    echo "Failed to install " . $name . " with version " . $version . ".\n";
                    echo $e->getMessage() . "\n";
                    $stats["failed"]++;
                }
            }else{
                if(!$shouldInstall) {
                    echo "A dependency is missing. This environment may not work properly and has not been installed.\n";
                }
            }
        }
    } else {
        echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
        echo "Failed to extract " . $name . " with version " . $version . ".\n";
        $stats["failed"]++;
    }
}

echo "Installation finished.\n";
echo "Installed: " . $stats["installed"] . "\n";
echo "Re-installed: " . $stats["re-installed"] . "\n";
echo "Failed: " . $stats["failed"] . "\n";