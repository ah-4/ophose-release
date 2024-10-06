<?php

namespace Ophose\Util;

/**
 * Util function to colorize text formatted with ANSI escape codes.
 * To use this function, you must use the following ANSI escape codes:
 * &black; (black), &red; (red), &green; (green), &yellow; (yellow), &blue; (blue), &magenta; (magenta), &cyan; (cyan), &white; (white)
 * &bg-black; (black background), &bg-red; (red background), &bg-green; (green background), &bg-yellow; (yellow background), &bg-blue; (blue background), &bg-magenta; (magenta background), &bg-cyan; (cyan background), &bg-white; (white background)
 * 
 * @example echo colorize("&redHello, &greenWorld!&reset");
 *
 * @param string $text the text to colorize
 * @return string the colorized text (with ANSI escape codes)
 */
function colorize(string $text) {
    $text = str_replace("&reset;", "\033[0m", $text);
    $text = str_replace("&black;", "\033[30m", $text);
    $text = str_replace("&red;", "\033[31m", $text);
    $text = str_replace("&green;", "\033[32m", $text);
    $text = str_replace("&yellow;", "\033[33m", $text);
    $text = str_replace("&blue;", "\033[34m", $text);
    $text = str_replace("&magenta;", "\033[35m", $text);
    $text = str_replace("&cyan;", "\033[36m", $text);
    $text = str_replace("&white;", "\033[37m", $text);
    $text = str_replace("&bg-black;", "\033[40m", $text);
    $text = str_replace("&bg-red;", "\033[41m", $text);
    $text = str_replace("&bg-green;", "\033[42m", $text);
    $text = str_replace("&bg-yellow;", "\033[43m", $text);
    $text = str_replace("&bg-blue;", "\033[44m", $text);
    $text = str_replace("&bg-magenta;", "\033[45m", $text);
    $text = str_replace("&bg-cyan;", "\033[46m", $text);
    $text = str_replace("&bg-white;", "\033[47m", $text);
    if(!str_ends_with($text, "\033[0m")) $text .= "\033[0m";
    return $text;
}

/**
 * Util function to colorize text formatted with ANSI escape codes.
 * To use this function, you must use the following ANSI escape codes:
 * &black; (black), &red; (red), &green; (green), &yellow; (yellow), &blue; (blue), &magenta; (magenta), &cyan; (cyan), &white; (white)
 * &bg-black; (black background), &bg-red; (red background), &bg-green; (green background), &bg-yellow; (yellow background), &bg-blue; (blue background), &bg-magenta; (magenta background), &bg-cyan; (cyan background), &bg-white; (white background)
 * 
 * @example echo colorize("&redHello, &greenWorld!&reset");
 *
 * @param string $text the text to colorize
 * @return string the colorized text (with ANSI escape codes)
 */
function clr(string $text) {
    return colorize($text);
}

function echo_clr(string $text) {
    echo clr($text);
}