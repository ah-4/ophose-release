<?php

function startsWith($text, $toFind)
{
    $length = strlen($toFind);
    return substr($text, 0, $length) === $toFind;
}

function endsWith($text, $toFind)
{
    $length = strlen($toFind);
    if (!$length) {
        return true;
    }
    return substr($text, -$length) === $toFind;
}