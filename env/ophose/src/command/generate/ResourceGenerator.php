<?php

namespace Ophose\Command\Generate;

class ResourceGenerator {

    /**
     * @var array $args The arguments passed to the resource generator
     */
    protected array $args = [];

    /**
     * @var string $resource_name The name of the resource
     */
    protected string $resource_name;

    /**
     * Set the arguments
     * 
     * @param array $args The arguments
     */
    public function setArgs(array $args) {
        $this->args = $args;
    }

    /**
     * Run before generating the resource
     */
    public function before() {}

    /**
     * Generate a resource
     * 
     * @param array $args
     */
    public function generate() {}

    /**
     * Copy a file or directory
     * 
     * @param string $source The source file
     * @param string $destination The destination file
     * @param array $data The data to replace in the file
     */
    protected function copy($source, $destination, $data) {
        if(is_dir($source)) {
            $this->copyDirectory($source, $destination, $data);
        } else {
            $this->copyFile($source, $destination, $data);
        }
    }

    /**
     * Copy a directory recursively
     * 
     * @param string $source The source directory
     * @param string $destination The destination directory
     * @param array $data The data to replace in the files
     */
    protected function copyDirectory($source, $destination, $data) {
        if(!is_dir($destination)) {
            mkdir($destination, 0755, true);
        } else {
            throw new \Exception("Directory $destination already exists.");
        }
        $dir = opendir($source);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($source . '/' . $file) ) {
                    $this->copyDirectory($source . '/' . $file, $destination . '/' . $file, $data);
                } else {
                    $this->copyFile($source . '/' . $file, $destination . '/' . $file, $data);
                }
            }
        }
    }

    /**
     * Copy a file
     * 
     * @param string $source The source file
     * @param string $destination The destination file
     * @param array $data The data to replace in the file
     */
    protected function copyFile($source, $destination, $data) {
        if(file_exists($destination)) {
            throw new \Exception("File $destination already exists.");
        }
        $content = file_get_contents($source);
        foreach($data as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        file_put_contents($destination, $content);
    }

    public final function getResourceName() {
        return $this->resource_name;
    }

    protected function requiredArg(int $index, string $message) {
        if(!isset($this->args[$index])) {
            throw new \Exception("Missing required argument at index $index." . "\n" . $message);
        }
        return $this->args[$index];
    }

}