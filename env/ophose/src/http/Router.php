<?php

namespace Ophose\Http;

class Router {

    /** 
     * @var string $path The path to the route
     */
    private string|null $path;

    /** 
     * @var string $originalPath The original path to the route
     */
    private string|null $originalPath;

    /** 
     * @var array|null $data The data of the route
     */
    private ?array $data = null;

    /** 
     * @var array|null $pageData The data for the resolver (contains error.js if the route is not found)
     */
    private ?array $resolverData = null;

    /**
     * Constructor
     * @param string|null $path The path to the route
     */
    public function __construct(?string $path)
    {
        $this->originalPath = $path;
        $this->path = $this->cleanPath($path);
        $this->findRoute();
    }

     /**
     * Cleans the path
     * @param string $path The path to the route
     * @return string
     */
    private function cleanPath(?string $path) {
        $value = trim($path, '/');
        $value = str_replace('//', '/', $value);
        if($value == '/') $value = '';
        return $value;
    }

    private function findFileStartingWith_(string $path, array &$vars, string $value) {
        if(!$path) return null;
        $path = dirname($path);
        if(!is_dir($path)) return null;
        $files = scandir($path);
        foreach ($files as $file) {
            if(str_starts_with($file, '_')) {
                $varName = substr($file, 1, strlen($file));
                $vars[$varName] = $value;
                return $path . '/' . $file;
            }
        }
        return null;
    }

    private function findRoute() {
        $route = $this->getRoute();
        $this->data = $route;


        if(!$route) {
            $this->path = $this->cleanPath($this->originalPath . '/index');
            $route = $this->getRoute();
            $this->data = $route;
        }

        if(!$route) $route = [
            "js" => ROOT . 'pages/error.js',
            "query" => null
        ];

        $path = o_realpath($route['js']);
        $path = str_replace(o_realpath(ROOT . 'pages/'), '', $path);
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);
        $route['js'] = $path;
        $route['url'] = $this->originalPath;

        $this->resolverData = $route;
    }

    private function getRoute() {
        if($this->path === null) return null;

        $pathParts = explode('/', $this->path);

        $requestPath = ROOT . 'pages/';
        $vars = [];
        
        foreach ($pathParts as $i => $currentPath) {
            $last = $i == count($pathParts) - 1;
            $value = $currentPath;
            if($value == '') $value = 'index';

            if($last) {
                $path = $requestPath . $value . '.js';
                $realpath = o_realpath($path);
                if(!$realpath) {
                    $requestPath =  $this->findFileStartingWith_($requestPath, $vars, $value);
                    $realpath = o_realpath($requestPath . '/' . $value . '.js');
                }
                if(!$realpath) return null;
                return [
                    "js" => $realpath,
                    "query" => empty($vars) ? null : $vars
                ];
            }

            $path = $requestPath . $value;
            $realpath = o_realpath($path);
            if(!$realpath) $realpath = $this->findFileStartingWith_($path, $vars, $value);
            if(!$realpath) return null;
            $requestPath = $realpath . '/';
        }

        return null;
    }

    public function getQuery(string $name) {
        $route = $this->getRoute();
        if(!$route) return null;
        return $route['query'][$name] ?? null;
    }

    public function getData() {
        return $this->data;
    }

    public function getResolverData() {
        return $this->resolverData;
    }

    public function getOriginalPath() {
        return $this->originalPath;
    }

}