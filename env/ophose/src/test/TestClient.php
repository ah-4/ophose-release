<?php

namespace Ophose\Test;

use Ophose\Environment\EnvRequestProcessor;
use Ophose\Http\Client;
use Ophose\Response;
use function Ophose\Util\clr;
use function Ophose\Util\configuration;

class TestClient extends Client {

    public function __construct(string $url) {
        parent::__construct($url);
    }

    private function prepareTestInformations(): void
    {
        $_SERVER['REQUEST_METHOD'] = $this->method;
        $_SERVER['REQUEST_URI'] = $this->url;
        $_SERVER['HTTP_USER_AGENT'] = 'Ophose Test Client';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_HOST'] = configuration()->get('url');
        $_SERVER['SERVER_NAME'] = configuration()->get('url');
        $_SERVER['SERVER_PORT'] = 80;

        if(is_array($this->body)) {
            $body = [];
            foreach($this->body as $key => $value) {
                if($value instanceof \CURLFile){
                    $file = [
                        'name' => basename($value->name),
                        'type' => $value->mime,
                        'tmp_name' => $value->name,
                        'error' => 0,
                        'size' => filesize($value->name)
                    ];
                    $body[$key] = $file;
                    $_FILES[$key] = $file;
                    continue;
                }
                $body[$key] = $value;
            }
            $this->body = $body;
        }

        if ($this->method == 'GET') {
            $_GET = $this->body;
        } else {
            $_POST = $this->body;
        }

        if ($this->headers) {
            foreach ($this->headers as $key => $value) {
                $_SERVER[$key] = $value;
            }
        }
    }

    private function clearTestInformations(): void
    {
        $_SERVER = [];
        $_GET = [];
        $_POST = [];
        $_FILES = [];
    }

    public function send(): static
    {
        // Check if URL is relative and make it absolute if necessary
        if (preg_match('/^http(s)?:\/\//', $this->url)) throw new \Exception("URL must be relative");
        $this->prepareTestInformations();
        echo clr("\t&green;Sending request to &blue;{$this->url}&reset;\n");
        echo clr("\t&green;Method: &blue;{$this->method}&reset;\n");
        echo clr("\t&green;Headers: &blue;" . json_encode($this->headers, flags: JSON_PRETTY_PRINT) . "&reset;\n");
        echo clr("\t&green;Body: &blue;" . json_encode($this->body, JSON_PRETTY_PRINT) . "&reset;\n");
        (new EnvRequestProcessor($this->url, 'API'))->run();
        $response = Response::getLastResponse();
        Response::clearLastResponse();
        $this->clearTestInformations();
        $this->response = $response;
        echo clr("\n\t&green;Response: &blue;" . $response->body() . "&reset;\n");
        echo clr("\t&green;Status: &blue;" . $response->status() . "&reset;\n");
        echo clr("\t&green;Response content type: &blue;" . $response->contentType() . "&reset;\n");
        return $this;
    }

    public function response(): string|false|Response|null
    {
        return $this->response;
    }

}