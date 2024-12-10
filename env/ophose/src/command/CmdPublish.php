<?php

namespace Ophose\Command;

use CURLFile;
use Ophose\Command\Command;
use Ophose\Parameters\OphoseParameters;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

use function Ophose\Http\client;
use function Ophose\Util\clr;
use function Ophose\Util\configuration;

class CmdPublish extends Command
{

    protected ?string $resource_type = null;
    protected ?string $resource_name = null;
    protected ?string $resource_author = null;
    protected ?string $resource_path = null;
    protected ?string $resource_version = null;
    protected ?string $resource_version_description = null;

    private function setResourceType() {
        if($this->hasOption('type')) {
            $resource_type = $this->getOption('type');
            if($resource_type == 'e' || $resource_type == 'env') $resource_type = 'environment';
            if($resource_type == 'c' || $resource_type == 'cpn') $resource_type = 'component';
            if($resource_type == 'environment' || $resource_type == 'component') $this->resource_type = $resource_type;
        }
    }

    private function setResourceAuthorAndName() {
        $author_name = $this->getArguments()[0];
        $author_name_parts = explode(':', $author_name);
        if(count($author_name_parts) == 2) {
            $this->resource_author = $author_name_parts[0];
            $this->resource_name = $author_name_parts[1];
            return $this->resource_name;
        }
        return null;
    }

    private function setResourceVersion() {
        if($this->hasOption('v')) $this->resource_version = $this->getOption('v');
        if($this->hasOption('version')) $this->resource_version = $this->getOption('version');
    }

    private function setResourceVersionDescription() {
        if($this->hasOption('d')) $this->resource_version_description = $this->getOption('d');
        if($this->hasOption('description')) $this->resource_version_description = $this->getOption('description');
    }

    public function before()
    {
        $this->setResourceType();
        $this->setResourceAuthorAndName();
        $this->setResourceVersion();
        $this->setResourceVersionDescription();
    }

    public function run() {
        if($this->resource_type === null) {
            echo clr("&red;Invalid resource type.&reset; Please for option 'type' specify either 'environment' or 'component'.\n");
            return;
        }

        if($this->resource_author === null) {
            echo clr("&red;Invalid resource author and name.&reset; Please specify the author and name of the resource as '<author>:<resource_name>' in first argument.\n");
            return;
        }

        if($this->resource_version === null) {
            echo clr("&red;Invalid resource version.&reset; Please specify the version of the resource with option '-v'.\n");
            return;
        }

        if($this->resource_version_description === null) {
            echo clr("&red;Invalid resource version description.&reset; Please specify the description of the version with option '-d'.\n");
            return;
        }

        switch($this->resource_type) {
            case 'environment':
                $this->resource_path = OphoseParameters::EXT_ENV_PATH_NAME . $this->resource_author . DIRECTORY_SEPARATOR . $this->resource_name;
                break;
            case 'component':
                $this->resource_path = OphoseParameters::EXT_CPN_PATH_NAME . $this->resource_author . DIRECTORY_SEPARATOR . $this->resource_name;
                break;
        }
        $resource_path = $this->resource_path;
        $this->resource_path = o_realpath($this->resource_path);

        if(!file_exists($this->resource_path)) {
            echo clr("&red;Resource not found.&reset; The resource path '$resource_path' does not exist.\n");
            return;
        }

        echo clr("Publishing (&cyan;$this->resource_type&reset;) resource &green;$this->resource_author:$this->resource_name&reset;\n");
        
        $zip_path = $this->createZip();
        if($zip_path === null) {
            echo clr("&red;Failed to create zip file.\n");
            return;
        }

        $documentation_path = $this->resource_path . DIRECTORY_SEPARATOR . 'documentation.json';
        if(!file_exists($documentation_path)) {
            echo clr("&red;Please provide a &yellow;documentation.json&red; in the resource path in JSON formatted for Ophose.&reset; The documentation file '$documentation_path' does not exist.\n");
            return;
        }

        if(json_decode(file_get_contents($documentation_path) === null)) {
            echo clr("&red;Invalid documentation file.&reset; The documentation file '$documentation_path' is not a valid JSON file.\n");
            return;
        }

        $picture_path = $this->resource_path . DIRECTORY_SEPARATOR . 'picture.jpg';
        if(!file_exists($picture_path)) {
            echo clr("&red;Please provide a &yellow;picture.jpg&red; in the resource path for the resource.&reset; The picture file '$picture_path' does not exist.\n");
            return;
        }

        $request = client(OphoseParameters::URL . '/api/resources/share')->post([
            'type' => $this->resource_type == 'component' ? 1 : 2,
            'api_key' => configuration()->get('api_key'),
            'title' => $this->resource_name,
            'subtitle' => $this->resource_name . ' by ' . $this->resource_author,
            'picture' => new CURLFile($picture_path),
            'documentation' => new CURLFile($documentation_path),
            'version_file' => new CURLFile($zip_path),
            'version_name' => $this->resource_version,
            'version_description' => $this->resource_version_description
        ])->send();

        if($request->status() !== 201 && $request->status() !== 200) {
            echo clr("&red;Failed to publish resource.&reset; Status: " . $request->status() . "\n");
            echo "Message: " . $request->response() . "\n";
            return;
        }

        unlink($zip_path);
        echo clr("&green;Resource published successfully.&reset;\n");
    }

    private function createZip() {
        if($this->resource_path === null) return null;
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5($this->resource_author . $this->resource_name) . ".zip";
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->resource_path),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                if(pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'local') continue;
                if($this->resource_type == 'component' && pathinfo($file->getFilename(), PATHINFO_EXTENSION) != 'js') continue;
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($this->resource_path) + 1);
                $relativePath = str_replace('\\', '/', $relativePath);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        return $zipPath;
    }

}