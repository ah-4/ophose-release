<?php

namespace Ophose\Command;

use Ophose\Command\Command;

use function Ophose\Http\client;

class CmdBuild extends Command
{

    public function getOphoseBuilt()
    {
        include_once __DIR__ . '/build-ophose.php';
        $ophose_files = array_diff(JS_ORDER, JS_DEV_INCLUDES);
        $content = "";
        foreach($ophose_files as $file) {
            $path = OPHOSE_PATH . "/js/" . $file;
            $content .= file_get_contents($path) . ";";
        }
        $content = "const dev = {error:()=>{},log:()=>{}};" . $content;
        $content .= ";__OPH_APP_BUILD__=true;";
        return $content;
    }

    public function getAppBuilt() {
        $build = new Build();
        return $build->buildJSFiles();
    }

    public function run() {
        $ophose_build_content = $this->getOphoseBuilt();
        $ophose_build_content .= "\n" . $this->getAppBuilt();
        $request = client('https://www.toptal.com/developers/javascript-minifier/api/raw')->post([
            'input' => $ophose_build_content
        ])->encode('query');
        $request->send();
        $compiled_app = $request->response();
        file_put_contents(ROOT . 'public/app.js', $compiled_app);
        echo "App built successfully !";
    }
}