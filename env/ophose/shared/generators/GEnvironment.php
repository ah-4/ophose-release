<?php

use Ophose\Command\Generate\ResourceGenerator;
use Ophose\Parameters\OphoseParameters;

return new class extends ResourceGenerator {

    protected string $resource_name = "environment";

    public function generate() {
        $name = $this->requiredArg(0, "The environment name is required.");
        echo "Generating environment '$name'...\n";
        $author_name = "self";
        $environment_name = $name;
        $relative_export_path = $environment_name;
        if(strrpos($name, ":") !== false) {
            $name_parts = explode(":", $name);
            $author_name = $name_parts[0];
            $environment_name = $name_parts[1];
            $relative_export_path = OphoseParameters::EXT_PATH_NAME . '/' . $author_name . '/' . $environment_name;
        }
        $export_path = ENV_PATH . $relative_export_path;
        $this->copy(ENV_PATH . '/ophose/export/generate/environment', $export_path, [
            "name" => $environment_name,
            "author" => $author_name,
            "version" => "1.0.0",
            "description" => "Environment for  $environment_name"
        ]);
    }

};