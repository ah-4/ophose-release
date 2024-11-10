<?php

$JS_ORDER = [
    "plugin/Plugin.js",
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

$dev_includes = [
    "dev/dev.js"
];

// Add dev includes
$JS_ORDER = array_merge($JS_ORDER, $dev_includes);

define('JS_ORDER', $JS_ORDER);
define('JS_DEV_INCLUDES', $dev_includes);

function compile() {
    $time = time();
    $COMPILED_JS_PATH = ROOT . '/public/ophose.js';
    // Get all required files
    echo "Building Ophose... \n";
    $js_order = JS_ORDER;
    $js_order = array_diff($js_order, JS_DEV_INCLUDES);

    $required_files = array_map(function($file) {
        $fixed_path = str_replace('\/', DIRECTORY_SEPARATOR, $file);
        return realpath(ROOT . "/ophose/js/" . $fixed_path);
    }, $js_order);

    $content = "";
    foreach($required_files as $file) {
        if(!file_exists($file)) {
            die("File $file does not exist ! Building failed...");
        }
        echo "Adding $file to build...\n";
        $content .= file_get_contents($file) . ";";
    }

    $content = "const dev = {error:()=>{}};" . $content;
    $content .= ";__OPH_APP_BUILD__=true;";

    file_put_contents($COMPILED_JS_PATH, $content);
    echo "Ophose built in " . round(time() - $time, 2) . "s\n";
}

