<?php

namespace Ophose\Http;

class MiddlewareHandler {

    private string $url;
    private array $middlewares = [];
    private array $data = [];

    public function __construct(string $url) {
        $this->url = $url;
    }

    public function addMiddleware($middlewareClass) {
        $this->middlewares[] = $middlewareClass;
    }

    public function url() {
        return $this->url;
    }

    public function handle() {
        $urls = [];
        $handled = false;
        $data = [];

        while(!$handled) {
            $handled = true;
            $data = [];
            // Check if the URL is handled twice
            if((array_count_values($urls)[$this->url] ?? 0) > 1) return false;
            foreach($this->middlewares as $middleware) {
                $middleware = new $middleware();
                $middleware->setUrlThenRouter($this->url);
                $middleware->handle($this->url);
                $issueData = $middleware->getIssueData();
                $data = array_merge($data, $middleware->getData());
                if($issueData) {
                    switch($issueData["type"]) {
                        case "redirect":
                            $this->url = $issueData["url"];
                            $handled = false;
                            $urls[] = $this->url;
                            break;
                    }
                }
            }
        }

        $this->data = $data;
        return true;
    }

    public function getData() {
        return $this->data;
    }

}