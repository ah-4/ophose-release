<?php

namespace Ophose;

class Session {
    
    private static function getKey($key) {
        return (isset(CONFIG["project_id"]) ? CONFIG["project_id"] . "_" : "") . $key;
    }

    /**
     * Set a session variable
     *
     * @param string $key The key
     * @param mixed $default The value to return if not found
     * @return mixed the session value or default if not found
     */
    public static function get(string $key, mixed $default = null) {
        if(!isset($_SESSION)) session_start();
        $key = self::getKey($key);
        if (isset($_SESSION[$key])) return $_SESSION[$key];
        return $default;
    }

    /**
     * Set a session variable
     *
     * @param string $key The key
     * @param mixed $value The value
     * @return void
     */
    public static function set(string $key, mixed $value) {
        if(!isset($_SESSION)) session_start();
        $key = self::getKey($key);
        $_SESSION[$key] = $value;
    }

    /**
     * Delete a session variable
     *
     * @param string $key The key
     * @return void
     */
    public static function delete(string $key) {
        if(!isset($_SESSION)) session_start();
        $key = self::getKey($key);
        unset($_SESSION[$key]);
    }

    /**
     * Clear all session variables of a client
     *
     * @return void
     */
    public static function clear() {
        if(!isset($_SESSION)) session_start();
        session_unset();
    }

    /**
     * Check if a session variable exists
     *
     * @param string $key The key
     * @return boolean true if the session variable exists
     */
    public static function exists(string $key) : bool {
        if(!isset($_SESSION)) session_start();
        $key = self::getKey($key);
        return isset($_SESSION[$key]);
    }

}