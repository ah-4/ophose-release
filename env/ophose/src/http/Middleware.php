<?php

namespace Ophose\Http;

class Middleware {

    private string $url;
    private ?array $issueData = null;
    protected ?Router $router = null;
    private $data = [];

    public function setUrlThenRouter(string $url) {
        $this->url = $url;
        $this->router = new Router($url);
    }

    protected final function redirect(string $url) {
        $this->issueData = [
            "type" => "redirect",
            "url" => $url
        ];
        return false;
    }

    protected final function matches(string $url) {
        return fnmatch(strtolower($url), strtolower($this->url));
    }

    public function handle(string $url) {
        
    }

    public final function url() {
        return $this->url;
    }

    public final function getIssueData() {
        return $this->issueData;
    }

    protected function query(string $key) {
        return $this->router->getQuery($key);
    }

    protected function route(string $url) {
        $r = new Router($url);
        return $r->getResolverData()['js'] == $this->router->getResolverData()['js'];
    }

    protected function data(string $key, $value) {
        $this->data[$key] = $value;
    }

    public function getData() {
        return $this->data;
    }
    
}
