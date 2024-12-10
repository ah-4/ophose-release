<?php

/**
 * Adds a value or all values of an array to the array at the specified key (note
 * that if value is an empty array, nothing will be added)
 *
 * @param array $array the array
 * @param string|integer $key the key
 * @param mixed $value the value (if it is an array, it will be flattened)
 * @return void
 */
function o_auto_add(array &$array, mixed $key, mixed $value) {
    if(!is_array($array)) return;
    if(is_array($value)) {
        if(count($value) == 0) return;
        if(!array_key_exists($key, $array)) $array[$key] = [];
        foreach($value as $v) {
            $array[$key][] = $v;
        }
    } else {
        if(!array_key_exists($key, $array)) $array[$key] = [];
        $array[$key][] = $value;
    }
}

function dig(array $array, string $key, mixed $default = null) {
    $keys = explode(".", $key);
    $value = $array;
    foreach($keys as $k) {
        if(!array_key_exists($k, $value)) return $default;
        $value = $value[$k];
    }
    return $value;
}