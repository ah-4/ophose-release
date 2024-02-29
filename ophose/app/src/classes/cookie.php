<?php

namespace Ophose;

class Cookie {
        
        private static function getKey($key) {
            return (isset(CONFIG["project_id"]) ? CONFIG["project_id"] . "_" : "") . $key;
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
        public static function set(string $key, mixed $value, int $expire = 3600, string $path = "/", string $domain = "", bool $secure = true, bool $httponly = false) {
            $key = self::getKey($key);
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
            setcookie($key, "", time() - 3600, "/");
        }
    
        /**
        * Clear all cookie variables of a client
        *
        * @return void
        */
        public static function clear() {
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
            return isset($_COOKIE[$key]);
        }
    
        /**
        * Get all cookie variables
        *
        * @return array all cookie variables
        */
        public static function all() {
            return $_COOKIE;
        }
    
        /**
        * Get all cookie variables
        *
        * @return array all cookie variables
        */
        public static function clearAll() {
            foreach ($_COOKIE as $key => $value) {
                setcookie($key, "", time() - 3600, "/");
            }
        }

}