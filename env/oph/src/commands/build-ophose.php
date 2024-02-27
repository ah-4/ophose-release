<?php

$JS_ORDER = [
    "html/render.js",

    "import.js",
    "script.js",
    "app.js",
    "event.js",

    "element/OphoseElement.js",
    "element/OphoseComponent.js",
    "element/OphoseModule.js",
    "element/OphosePage.js",
    "element/OphoseBase.js",
    "route/route.js",

    "env/OphoseEnvironment.js",

    "dynamic/Live.js",

    "ophose.js"
];
define('JS_ORDER', $JS_ORDER);

function compile() {
    $COMPILED_JS_PATH = ROOT . '/public/ophose.js';
    // Get all required files
    $required_files = array_map(function($file) {
        $fixed_path = str_replace('\/', DIRECTORY_SEPARATOR, $file);
        return realpath(ROOT . "/ophose/js/" . $fixed_path);
    }, JS_ORDER);

    $content = "";
    foreach($required_files as $file) {
        if(!file_exists($file)) {
            die("File $file does not exist ! Building failed...");
        }
        $content .= file_get_contents($file) . ";";
    }

    $content .= ";__OPH_APP_BUILD__ = true;";

    file_put_contents($COMPILED_JS_PATH, $content);
}

