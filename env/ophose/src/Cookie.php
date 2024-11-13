<?php

namespace Ophose;

use function Ophose\Util\configuration;

class Cookie {

    private static array $TEST_COOKIES = [];
        
    private static function getKey($key) {
        return (configuration()->get("project_id") ? configuration()->get("project_id") . "_" : "") . $key;
    }

    /**
    * Set a cookie variable
    *
    * @param string $key The key
    * @param mixed $default The value to return if not found
    * @return mixed the cookie value or default if not found
    */
    public static function get(string $key, mixed $default = null) {
        $key = self::getKey($key);
        if(defined('TEST_MODE') && TEST_MODE) {
            if (isset(self::$TEST_COOKIES[$key])) return self::$TEST_COOKIES[$key];
            return $default;
        }
        if (isset($_COOKIE[$key])) return $_COOKIE[$key];
        return $default;
    }

    /**
    * Set a cookie variable
    *
    * @param string $key The key
    * @param mixed $value The value
    * @param int $expire The expiration time (in seconds from now)
    * @param string $path The path
    * @param string $domain The domain
    * @param bool $secure The secure flag
    * @param bool $httponly The httponly flag
    * @return void
    */
    public static function set(string $key, mixed $value, int $expire = 3600, string $path = "/", string $domain = "", bool $secure = true, bool $httponly = true) {
        $key = self::getKey($key);
        if(defined('TEST_MODE') && TEST_MODE) {
            self::$TEST_COOKIES[$key] = $value;
            return;
        }
        setcookie($key, $value, time() + $expire, $path, $domain, $secure, $httponly);
    }

    /**
    * Delete a cookie variable
    *
    * @param string $key The key
    * @return void
    */
    public static function delete(string $key) {
        $key = self::getKey($key);
        if(defined('TEST_MODE') && TEST_MODE) {
            unset(self::$TEST_COOKIES[$key]);
            return;
        }
        setcookie($key, "", time() - 3600, "/");
    }

    /**
    * Clear all cookie variables of a client
    *
    * @return void
    */
    public static function clear() {
        if(defined('TEST_MODE') && TEST_MODE) {
            self::$TEST_COOKIES = [];
            return;
        }
        foreach ($_COOKIE as $key => $value) {
            if($key == "PHPSESSID") continue;
            setcookie($key, "", time() - 3600, "/");
        }
    }

    /**
    * Check if a cookie variable exists
    *
    * @param string $key The key
    * @return boolean true if the cookie variable exists
    */
    public static function has(string $key) {
        $key = self::getKey($key);
        if(defined('TEST_MODE') && TEST_MODE) {
            return isset(self::$TEST_COOKIES[$key]);
        }
        return isset($_COOKIE[$key]);
    }

    /**
    * Get all cookie variables
    *
    * @return array all cookie variables
    */
    public static function all() {
        if(defined('TEST_MODE') && TEST_MODE) {
            return self::$TEST_COOKIES;
        }
        return $_COOKIE;
    }

}

/**
 * Get a cookie variable or the default value if not found
 *
 * @param string $key The key of the cookie variable
 * @param mixed $default The default value to return if the cookie variable is not found
 * @return mixed the cookie variable or the default value if not found
 */
function cookie(string $key, mixed $default = null) {
    return Cookie::get($key, $default);
}