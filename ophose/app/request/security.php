<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$SECURITY_SESSION_TTL_IN_MINUTES = 1440; // 24 hours

if (!isset($_SESSION["CSRF_TOKEN"]) || (isset($_SESSION["CSRF_TOKEN"]) && (time() - $_SESSION["CSRF_TOKEN_TIME"]) > $SECURITY_SESSION_TTL_IN_MINUTES * 60)) {
    $_SESSION["CSRF_TOKEN"] = bin2hex(random_bytes(32));
    $_SESSION["CSRF_TOKEN_TIME"] = time();
}