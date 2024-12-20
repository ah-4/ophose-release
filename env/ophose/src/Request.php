<?php

namespace Ophose;

class Request {

    /**
     * Returns the value of a $_GET variable if it exists, null otherwise
     *
     * @param string $key The key to look for in the $_GET array
     * @param mixed $default The default value to return if the $_GET variable does not exist
     * @return mixed|null The value of the $_GET variable if it exists, the default value otherwise
     */
    public static function query(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Returns the value of a $_POST variable if it exists and is not empty string, null otherwise
     *
     * @param string $key The key to look for in the $_POST array
     * @param bool $trim Whether to trim the value or not
     * @return mixed|null The value of the $_POST variable if it exists, null otherwise
     */
    public static function post(string $key, $trim = false) {
        $value = $_POST[$key] ?? null;
        if($value !== null && $trim) return trim($value);
        if($value !== null && $value == '') return null;
        return $value;
    }

    /**
     * Returns the file if it exists and has no error, null otherwise
     *
     * @param string $key The key to look for in the $_FILES array
     * @return mixed|null The file if it exists and has no error, null otherwise
     */
    public static function file(string $key) {
        $file = $_FILES[$key] ?? null;
        if($file !== null && $file['error'] === UPLOAD_ERR_OK) return $file;
        return null;
    }

    public static function input(string $key, $default = null, $inputOrder = ['get', 'post', 'json', 'file']) {
        foreach($inputOrder as $input) {
            switch($input) {
                case 'get':
                    $value = self::query($key);
                    break;
                case 'post':
                    $value = self::post($key);
                    break;
                case 'json':
                    $value = self::json($key) ?? null;
                    break;
                case 'file':
                    $value = self::file($key);
                    break;
                default:
                    $value = null;
            }
            if($value !== null) return $value;
        }
        return $default;
    }

    /**
     * Returns the request method (GET, POST, PUT, DELETE, etc.)
     *
     * @return string The request method
     */
    public static function getMethod() : string {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Returns the request URI (e.g. 'http://example.com')
     *
     * @return string The request URI
     */
    public static function getHost() : string {
        return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * Returns the request body
     */
    public static function getBody() : string {
        return file_get_contents('php://input');
    }

    /**
     * Returns the request body as JSON if the content type is 'application/json', $default otherwise
     * 
     * @param array $default The default value to return if the content type is not 'application/json'
     * @return array|null The request body as JSON if the content type is 'application/json', $default otherwise
     */
    public static function json(string $key = null, mixed $default = null) {
        if($_SERVER['CONTENT_TYPE'] ?? null == 'application/json' && $key !== null) {
            $keys = explode('.', $key);
            $body = json_decode(self::getBody(), true);
            foreach ($keys as $key) {
                if (!isset($body[$key])) return $default;
                $body = $body[$key];
            }
            return $body;
        }
        return $default;
    }

    /**
     * Returns the request URI (e.g. '/path/to/resource')
     *
     * @return string The request URI
     */
    public static function url() : string {
        return $_SERVER['REQUEST_URI'];
    }

}