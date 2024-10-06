<?php

/**
 * Ensure a value is between a minimum and maximum and modify it if necessary
 *
 * @param integer $value The value to check
 * @param integer $min The minimum value
 * @param integer $max The maximum value
 * @return integer The value, modified if necessary
 */
function o_between(int &$value, int $min, int $max): int {
    if($value < $min) $value = $min;
    if($value > $max) $value = $max;
    return $value;
}