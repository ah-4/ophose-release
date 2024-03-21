<?php

namespace Oph;
use AutoLoader;

class BuildFile {

    private ?string $path, $type, $content, $shared;
    private Build $build;
    private array $requiredJSComponents = [], $requiredJSModules = [], $requiredJSEnvironments = [], $dependencies = [], $strings = [];


    public function __construct(Build $build, string $path) {
        $this->build = $build;
        $this->path = $path;
        $this->content = file_get_contents($path);

        if(!file_exists($path)) {
            die("File $path does not exist ! Building failed...");
        }
        

        $this->requiredJSComponents = $this->getRequiredFilesThenRemove("oimpc", "components");
        $this->requiredJSModules = $this->getRequiredFilesThenRemove("oimpm", "modules");
        $this->requiredJSEnvironments = $this->getRequiredFilesThenRemove("oimpe", "env");

        $this->lockString();
        $this->addUsedFiles();
        $this->minify();
    }

    public function lockString() {
        preg_match_all('/\'((?:[^\\\']|\\.)*?)\'|"((?:[^\\"]|\\.)*?)"|`((?:[^\\`]|\\.)*?)`/', $this->content, $this->strings);

        // Replace with variables
        $i = 0;
        foreach($this->strings[0] as $string) {
            $this->content = str_replace($string, "__OCMPL_SLOCK" . $i . '_', $this->content);
            $i++;
        }
    }

    public function unlockString() {
        $i = 0;
        foreach($this->strings[0] as $string) {
            $this->content = str_replace("__OCMPL_SLOCK" . $i . '_', $string, $this->content);
            $i++;
        }
    }

    public function reduce() {
        // Trim each line
        $this->content = implode("\n", array_map(function($line) {
            return trim($line);
        }, explode("\n", $this->content)));
        $this->content = preg_replace('/^\s+$/m', '', $this->content);
        $this->content = preg_replace('/^\s*[\r\n]/m', '', $this->content);
        $this->content = preg_replace('/(\/\*[\w\'\s\r\n\*]*\*\/)|(\/\/.*)|(\<![\-\-\s\w\>\/]*\>)/m', "", $this->content);
        $this->content = preg_replace('/(\}|\]|\)|\;|\,|\{)\s*[\r\n]\s*(\}|\]|\)|\;|\,|\{)/m', "$1$2", $this->content);
        $this->content = preg_replace('/(\;|\,|\{|\[)\s*[\r\n]\s*/m', "$1", $this->content);
        $this->content = preg_replace('/\;+/', ";", $this->content);
    }

    private function getRequiredFilesThenRemove(string $importFunction, string $path) {
        $matches = [];
        $original = [];
        preg_match_all('/^' . $importFunction . '\(("|\'|`)(.*)("|\'|`)\)/m', $this->content, $matches);
        $matches = array_map(function($match) use (&$original) {
            $original[] = $match;
            return str_replace('@/', '.ext/', $match);
        }, $matches[2]);

        if($path != "env") {
            $matches = array_map(function($match) use ($path) {
                return realpath(ROOT . "/" . $path . "/" . $match . ".js");
            }, $matches);
        }else{
            $matches = array_map(function($match) {

                $envInfos = explode("/", $match);
                $envName = $envInfos[0];
                $usedNameCount = 1;
                $envPath = AutoLoader::getEnvironmentPath($envName);
                if(!$envPath && count($envInfos) > 1) {
                    $envName = $envInfos[0] . "/" . $envInfos[1];
                    $envPath = AutoLoader::getEnvironmentPath($envName);
                    if($envPath) {
                        $usedNameCount = 2;
                    }
                }
                if(!$envPath) {
                    echo $this->path . "\n";
                    die("No such environment $envName ! Building failed...");
                }

                $jsPath = implode("/", array_slice($envInfos, $usedNameCount));
                if(empty($jsPath)) {
                    return realpath($envPath . "/env.js");
                } else {
                    return realpath($envPath . "/js/" . $jsPath . ".js");
                }
            }, $matches);
        }
        foreach($original as $match) {
            $this->content = str_replace($importFunction . '("' . $match . '")', "", $this->content);
        }
        return $matches;
    }

    public function getRequiredJSFiles($type = "all") {
        if($type == "all") return array_merge($this->requiredJSComponents, $this->requiredJSModules, $this->requiredJSEnvironments);
        switch($type) {
            case "components":
                return $this->requiredJSComponents;
            case "modules":
                return $this->requiredJSModules;
            case "environments":
                return $this->requiredJSEnvironments;
        }
        return null;
    }

    public function addUsedFiles() {
        foreach($this->getRequiredJSFiles() as $file) {
            $this->build->useFile($file);
        }
    }

    public function getPath() {
        return $this->path;
    }

    public function minify() {
        $content = $this->content;
        $this->content = $content;
    }

    public function setdependencies(array $dependencies) {
        $this->dependencies = $dependencies;
    }

    public function getdependencies() {
        return $this->dependencies;
    }

    public function getContent() {
        return $this->content;
    }

    public function getShared() {
        return $this->shared;
    }

    function isOrdered(BuildFile $file, array $ordered) {
        $dependencies = $file->getdependencies();
        $i = array_search($file, $ordered);
        foreach($dependencies as $dependency) {
            if(array_search($dependency, $ordered) > $i) return false;
        }
        return true;
    }

    public function sortdependencies() {
        $ordered = $this->dependencies;
        $ordered = array_unique($ordered);
        $ordered = array_map(function($file) {
            return $this->build->getFile($file);
        }, $ordered);
        $ordered = array_filter($ordered, function($file) {
            return $file != null;
        });
        $ordered = array_values($ordered);
        // Remove this file from dependencies
        $s = array_search($this, $ordered);
        if($s !== false) {
            array_splice($ordered, $s, 1);
        }

        $i = 0;
        while($i < count($ordered)) {
            if($this->isOrdered($ordered[$i], $ordered)) {
                $i++;
            }else{
                $file = $ordered[$i];
                array_splice($ordered, $i, 1);
                $ordered[] = $file;
            }
        }

        $this->dependencies = $ordered;
    }

    public function getFullContent() {
        $content = "";
        foreach($this->dependencies as $file) {
            $content .= $file->getContent();
        }
        $content .= $this->content;
        return $content;
    }

}

class Build {

    private array $usedFiles = [], $entryFiles = [];

    public function __construct() {
        ;
    }

    // Get folders

    private function getBuildFolder() {
        return ROOT . "/public";
    }

    public function createBuildFolder() {
        $buildFolder = $this->getBuildFolder();
        if(!file_exists($buildFolder)) {
            mkdir($buildFolder);
        }
    }

    // Get JS files path
    public function getJSPageFiles() {
        $dir = ROOT . "pages";
        $files = o_get_files_recursive($dir, "js");
        return $files;
    }

    public function getJSBaseFile() {
        return realpath(ROOT . "/components/Base.js");
    }

    // Use files
    public function useFile(string $path) {
        if(isset($this->usedFiles[$path])) return;
        $this->usedFiles[$path] = new BuildFile($this, $path);
    }

    public function useEntryFile(string $path) {
        if(isset($this->entryFiles[$path])) return;
        $this->entryFiles[$path] = new BuildFile($this, $path);
    }

    public function getAllFiles() {
        return array_merge($this->entryFiles, $this->usedFiles);
    }

    public function getFile(string $path) {
        return $this->entryFiles[$path] ?? $this->usedFiles[$path] ?? null;
    }

    public function getdependencies(string $path, array $d = []) {
        $files = [];
        $d[] = $path;
        $file = $this->entryFiles[$path] ?? $this->usedFiles[$path] ?? null;
        if($file) {
            $dependencies = $file->getRequiredJSFiles();
            foreach($dependencies as $dependency) {
                if(in_array($dependency, $d)) continue;
                $files = array_merge($files, $this->getdependencies($dependency, $d));
            }
        }else{
            die("File $path does not exist ! Building failed...");
        }
        $files = array_unique($files);
        $files[] = $path;
        return $files;
    }

    public function verify(string $path) {
        $dependencies = $this->getdependencies($path);
        if(array_count_values($dependencies)[$path] > 1) {
            die("Circular dependency detected ! Building failed...");
        }
    }

    // Build JS files per page
    public function buildJSFiles() {
        $time = microtime(true);
        echo "Building your front application files...\n";
        echo "Creating build folder...\n";
        $this->createBuildFolder();
        echo "Listing entry files... (pages and Base component)\n";
        $jsPageFiles = $this->getJSPageFiles();
        $baseFile = $this->getJSBaseFile();
        $entryFiles = array_merge($jsPageFiles, [$baseFile]);

        // Add entry files
        foreach($entryFiles as $file) {
            echo "Using entry file: $file\n";
            $this->useEntryFile($file);
        }

        $deps = [];

        // Verify and add depedencies
        echo "Ordering and compiling...";
        foreach($this->getAllFiles() as $file) {
            $this->verify($file->getPath());
            $d = $this->getdependencies($file->getPath());
            $file->setdependencies($d);
            $deps = array_merge($deps, $d);
            $file->reduce();
            $file->unlockString();
        }

        foreach($this->getJSPageFiles() as $file) {
            unset($this->usedFiles[$file]);
            unset($deps[array_search($file, $deps)]);
        }

        // For base file, build the full content
        $file = $this->getFile($baseFile);
        $file->setdependencies($deps);
        $file->sortdependencies();
        $file->minify();
        $content = $file->getFullContent();
        $buildPath = $this->getBuildFolder() . "/app.js";
        if(!file_exists(dirname($buildPath))) {
            mkdir(dirname($buildPath), 0777, true);
        }
        file_put_contents($buildPath, $content);
        echo "Build done in " . round(microtime(true) - $time, 2) . "s\n";
    }

}

