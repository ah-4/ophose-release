<?php

namespace Ophose\Command;

use Ophose\Command\Command;
use ZipArchive;
use Exception;

use function Ophose\Util\configuration;

class CmdUpdate extends Command

{
    protected $current_version;
    protected $version_url;
    protected $version_file;
    protected $tmp_dir;

    public function run()
    {
        if (!$this->loadCurrentVersion()) {
            return false;
        }

        $this->setVersionDetails();

        try {
            $this->downloadVersion();
            $this->extractVersion();
            echo "Update complete!\n";
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    private function loadCurrentVersion(): bool
    {
        $this->current_version = configuration()->get("ophose.version");

        if ($this->current_version === null) {
            echo "No version found in config file... Version should be specified at: ophose.version\n";
            return false;
        }

        return true;
    }

    private function setVersionDetails(): void
    {
        $this->version_url = "https://github.com/ah-4/ophose-release/archive/refs/tags/" . $this->current_version . ".zip";
        $this->version_file = sys_get_temp_dir() . "/ophose-" . $this->current_version . ".zip";
        $this->tmp_dir = sys_get_temp_dir() . "/ophose-" . $this->current_version;
    }

    private function downloadVersion(): void
    {
        echo "Downloading Ophose version: " . $this->current_version . "...\n";
        $download = file_get_contents($this->version_url);

        if ($download === false) {
            throw new Exception("Failed to download Ophose version: " . $this->current_version . "\n");
        }

        file_put_contents($this->version_file, $download);
    }

    private function extractVersion(): void
    {
        echo "Extracting Ophose version: " . $this->current_version . "...\n";

        $to_replace = ['env/oph/', 'ophose/', '.htaccess', 'ocl'];
        $this->prepareTmpDir();

        $zip = new ZipArchive;
        if ($zip->open($this->version_file) === true) {
            $this->extractFilesFromZip($zip, $to_replace);
            $zip->close();
        } else {
            throw new Exception("Failed to open ZIP archive.\n");
        }

        o_rm_dir_recursive($this->tmp_dir);
    }

    private function prepareTmpDir(): void
    {
        if (!file_exists($this->tmp_dir)) {
            mkdir($this->tmp_dir, 0777, true);
        }
    }

    private function extractFilesFromZip(ZipArchive $zip, array $to_replace): void
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $original_filename = $zip->getNameIndex($i);
            $filename = $this->normalizeFilename($original_filename);

            if ($this->shouldExtract($filename, $to_replace)) {
                $this->extractAndCopyFile($zip, $original_filename, $filename);
            }
        }
    }

    private function normalizeFilename($original_filename): string
    {
        $filename = str_replace('\\', '/', $original_filename);
        return explode('/', $filename, 2)[1] ?? $filename;
    }

    private function shouldExtract(string $filename, array $to_replace): bool
    {
        foreach ($to_replace as $replace) {
            if ((strpos($filename, $replace) === 0 && str_ends_with($replace, '/')) || $filename === $replace) {
                return true;
            }
        }
        return false;
    }

    private function extractAndCopyFile(ZipArchive $zip, string $original_filename, string $filename): void
    {
        if (!str_ends_with($filename, '/')) {
            $zip->extractTo($this->tmp_dir, $original_filename);
            copy($this->tmp_dir . "/" . $original_filename, ROOT . "/" . $filename);
        }
    }

}