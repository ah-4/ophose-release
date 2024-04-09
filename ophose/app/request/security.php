<?php

use Ophose\Cookie;

header("Access-Control-Allow-Origin: *");

$SECURITY_SESSION_TTL_IN_MINUTES = 1440; // 24 hours

$originalCsrf = bin2hex(random_bytes(32));
if (!Cookie::has("CSRF_TOKEN")) Cookie::set("CSRF_TOKEN", $originalCsrf, $SECURITY_SESSION_TTL_IN_MINUTES * 60);
define('CSRF_TOKEN', Cookie::get("CSRF_TOKEN") ?? $originalCsrf);
unset($originalCsrf);