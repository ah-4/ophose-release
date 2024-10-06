<?php

namespace Ophose\Command;

use AutoLoader;
use Exception;
use Ophose\Command\Command;
use Ophose\Env;
use Ophose\Parameters\OphoseParameters;
use ZipArchive;

use function Ophose\Http\client;
use function Ophose\Util\clr;
use function Ophose\Util\configuration;

class CmdInstall extends Command
{
    /**
     * Run the command to install dependencies.
     */
    public function run()
    {
        $dependencies = configuration()->get("dependencies");
        $shouldReinstall = $this->hasOption('u');

        $stats = [
            "installed" => 0,
            "failed" => 0,
            "re-installed" => 0
        ];

        foreach ($dependencies as $name => $version) {
            $this->installDependency($name, $version, $shouldReinstall, $stats);
        }

        $this->printInstallSummary($stats);
    }

    /**
     * Installs a single dependency.
     *
     * @param string $name The dependency name in 'author/resource' format.
     * @param string $version The version of the dependency.
     * @param bool $shouldReinstall Flag to indicate if reinstallation is needed.
     * @param array $stats The installation statistics.
     */
    private function installDependency(string $name, string $version, bool $shouldReinstall, array &$stats): void
    {
        $infos = explode("/", $name);

        // Validate dependency format
        if (count($infos) !== 2) {
            echo clr("Invalid dependency name: &red;" . $name . "&reset;\n");
            $stats["failed"]++;
            return;
        }

        $author = $infos[0];
        $resource = $infos[1];
        $response = $this->fetchDependency($author, $resource, $version);

        if ($response === null) {
            $stats["failed"]++;
            return;
        }

        $zipPath = $this->saveDependencyZip($response, $author, $name, $version);
        $this->handleZipFile($zipPath, $response, $author, $version, $shouldReinstall, $stats);
    }

    /**
     * Fetches the dependency data from the server.
     *
     * @param string $author The author of the dependency.
     * @param string $resource The resource name.
     * @param string $version The version of the resource.
     * @return array|null The decoded response data or null on failure.
     */
    private function fetchDependency(string $author, string $resource, string $version): ?array
    {
        echo clr("Installing &yellow;{$author}/{$resource}&reset; with version &cyan;{$version}&reset;...\n");
        $result = client(OphoseParameters::URL . "/@/resources/fetch/{$author}/{$resource}/{$version}")
            ->post(["api_key" => configuration()->get("api_key")])
            ->send();

        if ($result->status() !== 200) {
            echo clr("Failed to install &red;{$author}/{$resource}&reset; with version &cyan;{$version}&reset;. Status: &yellow;{$result->status()}&reset;\n");
            echo clr("Message: &red;" . $result->response() . "&reset;\n");
            return null;
        }

        return $result->json();
    }

    /**
     * Saves the dependency zip data to a temporary file.
     *
     * @param array $response The response data from the server.
     * @param string $author The author of the dependency.
     * @param string $name The name of the dependency.
     * @param string $version The version of the dependency.
     * @return string The path to the saved zip file.
     */
    private function saveDependencyZip(array $response, string $author, string $name, string $version): string
    {
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5($author . $name . $version) . ".zip";
        file_put_contents($zipPath, base64_decode($response["data"]));
        return $zipPath;
    }

    /**
     * Handles the extraction and installation of the zip file.
     *
     * @param string $zipPath The path to the zip file.
     * @param array $response The server response containing dependency info.
     * @param string $author The author of the dependency.
     * @param string $version The version of the dependency.
     * @param bool $shouldReinstall Flag to indicate if reinstallation is needed.
     * @param array $stats The installation statistics.
     */
    private function handleZipFile(string $zipPath, array $response, string $author, string $version, bool $shouldReinstall, array &$stats): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $envPath = $this->getEnvironmentPath($response, $author);
            if (file_exists($envPath) && !$this->handleExistingDependency($envPath, $response, $shouldReinstall, $stats)) {
                $zip->close();
                unlink($zipPath);
                return;
            }

            mkdir($envPath, 0777, true);
            $zip->extractTo($envPath);
            $zip->close();
            unlink($zipPath);

            echo clr("Dependency &green;{$author}/{$response['name']}&reset; with version &cyan;{$version}&reset; installed.\n");
            $stats["installed"]++;

            // Handle environment-specific installations
            if ($response["type"] === "environment") {
                $this->installEnvironment($envPath, $response["name"], $version, $stats);
            }
        } else {
            echo clr("&red;Failed to extract &yellow;{$author}/{$response['name']}&red; with version &cyan;{$version}&red;.\n");
            $stats["failed"]++;
        }
    }

    /**
     * Determines the environment path for the dependency.
     *
     * @param array $response The response data containing dependency info.
     * @param string $author The author of the dependency.
     * @return string The full path to the environment directory.
     */
    private function getEnvironmentPath(array $response, string $author): string
    {
        $envPath = $author . DIRECTORY_SEPARATOR . $response["name"];
        return $response["type"] === "component" ? OphoseParameters::EXT_CPN_PATH_NAME . $envPath : OphoseParameters::EXT_ENV_PATH_NAME . $envPath;
    }

    /**
     * Handles existing dependencies, deciding whether to reinstall or skip.
     *
     * @param string $envPath The path to the environment directory.
     * @param array $response The response data containing dependency info.
     * @param bool $shouldReinstall Flag to indicate if reinstallation is needed.
     * @param array $stats The installation statistics.
     * @return bool True if the process should continue, false if it should stop.
     */
    private function handleExistingDependency(string $envPath, array $response, bool $shouldReinstall, array &$stats): bool
    {
        if (!$shouldReinstall) {
            echo clr("Dependency &yellow;{$response['author']}:{$response['name']}&reset; already exists.\n");
            if ($response["type"] === "environment") {
                $env = Env::getEnvironment($envPath);
                if ($env) {
                    $this->runInstallation($env, $response["name"], $stats, "re-installed");
                }
            }
            return false;
        }

        echo clr("&yellow;Re-installing &cyan;{$response['name']}&reset; with version &cyan;{$response['version']}&reset;.\n");
        o_rm_dir_recursive($envPath);
        return true;
    }

    /**
     * Installs an environment and its dependencies.
     *
     * @param string $envPath The path to the environment directory.
     * @param string $name The name of the environment.
     * @param string $version The version of the environment.
     * @param array $stats The installation statistics.
     */
    private function installEnvironment(string $envPath, string $name, string $version, array &$stats): void
    {
        $env = Env::getEnvironment($envPath);
        $confFile = $envPath . "/env.oconf";
        $shouldInstall = true;

        if (file_exists($confFile)) {
            $conf = configuration($confFile)->get();
            if ($conf && isset($conf["dependencies"]) && is_array($conf["dependencies"])) {
                foreach ($conf["dependencies"] as $depName) {
                    if (!AutoLoader::getEnvironmentPath($depName)) {
                        echo clr("&red;Dependency &yellow;{$depName}&reset; not found. This environment may not work properly.\n");
                        $shouldInstall = false;
                    }
                }
            }
        }

        if ($env && $shouldInstall) {
            $this->runInstallation($env, $name, $stats);
        } else {
            echo clr("&red;A dependency is missing. This environment may not work properly and has not been installed.&reset;\n");
        }
    }

    /**
     * Runs the environment installation and handles any errors.
     *
     * @param Env $env The environment instance.
     * @param string $name The name of the environment.
     * @param array $stats The installation statistics.
     * @param string $statusKey The key to update in stats ('installed' or 're-installed').
     */
    private function runInstallation(Env $env, string $name, array &$stats, string $statusKey = "installed"): void
    {
        try {
            $env->onInstall();
            echo clr("&green;Installed {$name}.&reset;\n");
            $stats[$statusKey]++;
        } catch (Exception $e) {
            echo clr("&red;Failed to install {$name}.&reset;\n");
            echo clr("&red;" . $e->getMessage() . "&reset;\n");
            $stats["failed"]++;
        }
    }

    /**
     * Prints a summary of the installation process.
     *
     * @param array $stats The installation statistics.
     */
    private function printInstallSummary(array $stats): void
    {
        echo clr("&green;Installation finished.&reset;\n");
        echo clr("&green;Installed: &reset;" . $stats["installed"] . "\n");
        echo clr("&yellow;Re-installed: &reset;" . $stats["re-installed"] . "\n");
        echo clr("&red;Failed: &reset;" . $stats["failed"] . "\n");
    }
}