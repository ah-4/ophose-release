<?php

namespace Ophose;

class Request {

    /**
     * Returns the value of a $_GET variable if it exists, null otherwise
     *
     * @param string $key The key to look for in the $_GET array
     * @return mixed|null The value of the $_GET variable if it exists, null otherwise
     */
    public static function query(string $key) {
        return $_GET[$key] ?? null;
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

}