<?php

require_once(__DIR__ . '/../src/autoload.php');

use Ophose\Response;

// Prevent directory traversal
if(strpos($_SERVER['REQUEST_URI'], "..") !== false) Response::raw("You may not access this file", 403);

/**
 * @var string The sanitized request URL (without the query string)
 */
define('FULL_REQUEST_HTTP_URL', urldecode($_SERVER['REQUEST_URI']));
define('REQUEST_HTTP_URL', parse_url(FULL_REQUEST_HTTP_URL)['path'] ?? '');
define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);

/**
 * @var string|null The request without the target string
 */
$REQUEST_FIXED_URL = null;

/**
 * Returns true if the request starts with the given string and set
 * the REQUEST_FIXED_URL variable to the request without the given string
 *
 * @param string $shouldStartsWith The string the request should start with
 * @return boolean True if the request starts with the given string
 */
function isTargetRequest(string $shouldStartsWith) {
    global $REQUEST_FIXED_URL;
    if(str_starts_with(REQUEST_HTTP_URL, $shouldStartsWith)) {
        $requestFixedUrl = substr(REQUEST_HTTP_URL, strlen($shouldStartsWith));
        if(trim($requestFixedUrl, "\/\\\n\r\t\v\x00") == "") return false;
        $REQUEST_FIXED_URL = $requestFixedUrl;
        define('REQUEST_FIXED_URL', $requestFixedUrl);
        return true;
    }
    return false;
}

// FORBIDDEN EXTENSIONS
$FORBIDDEN_EXTENSIONS = ["oconf", "local", "htaccess"];
foreach($FORBIDDEN_EXTENSIONS as $ext) {
    if(str_ends_with(REQUEST_HTTP_URL, "." . $ext) && file_exists(ROOT . REQUEST_HTTP_URL)) Response::raw("You may not access this file", 403);
}

// ENVRIONMENT REQUEST
if(isTargetRequest('/@env/') || isTargetRequest('/@/') || isTargetRequest('/@api/') || isTargetRequest('/api/')) {
    // Environment request as ('/@env/' + envName + '/' + endpoint)
    define('ENV_REQUEST', 'API');
    include_once(__DIR__ . '/../src/rest/env/env.php');
    Response::json(["message" => "End of the request."], 500);
    die();
}

// DEPENDENCY REQUEST
if(isTargetRequest('/@dep/')) {
    // Dependency request as ('/@dep/' + dependency)
    $REQUEST_FILE_PATH = OPHOSE_APP_PATH . 'dependencies/' . $REQUEST_FIXED_URL;
    Response::file($REQUEST_FILE_PATH);
    die();
}

// OPHOSE JAVASCRIPT REQUEST
if(isTargetRequest('/@ojs/')) {
    // Ophose JavaScript request as ('/@ojs/' + ophoseFile)
    $REQUEST_FILE_PATH = OPHOSE_PATH . 'js/' . $REQUEST_FIXED_URL;
    Response::file($REQUEST_FILE_PATH);
    die();
}

// COMPONENT REQUEST
if(isTargetRequest('/@component/')) {
    // Component request as ('/@component/' + ophoseFile)
    $REQUEST_FILE_PATH = ROOT . 'components/' . $REQUEST_FIXED_URL;
    Response::file($REQUEST_FILE_PATH);
    die();
}

// COMPONENT REQUEST
if(isTargetRequest('/@module/')) {
    // Module request as ('/@module/' + ophoseFile)
    $REQUEST_FILE_PATH = ROOT . 'modules/' . $REQUEST_FIXED_URL;
    Response::file($REQUEST_FILE_PATH);
    die();
}

// COMPONENT REQUEST
if(isTargetRequest('/@envjs/')) {
    // Environment JS request as ('/@envjs/' + env + [file])
    define('ENV_REQUEST', 'JS');
    include_once(__DIR__ . '/../src/rest/env/env.php');
    die();
}

// PAGE QUERY REQUEST
if(REQUEST_HTTP_URL == '/@query/' && REQUEST_METHOD == 'POST') {
    // Page query request
    include_once(__DIR__ . '/../src/rest/router/get_url_queries.php');
    die();
}

// PAGE REQUEST
if(isTargetRequest('/@pages/') || isTargetRequest('/pages/')) {
    // Page request as ('/@pages/' + pageName)
    $REQUEST_FILE_PATH = ROOT . "pages" . DIRECTORY_SEPARATOR . $REQUEST_FIXED_URL;
    Response::file($REQUEST_FILE_PATH);
    die();
}

// PUBLIC REQUEST
if(isTargetRequest('/public/') || isTargetRequest('/@public/')) {
    // Public request as ('/public/' + publicFile)
    $REQUEST_FILE_PATH = ROOT . 'public/' . $REQUEST_FIXED_URL;
    if(file_exists($REQUEST_FILE_PATH) && !is_dir($REQUEST_FILE_PATH) && !str_ends_with($REQUEST_FIXED_URL, ".php")) {
        header('Cache-Control: max-age=31536000');
        Response::file($REQUEST_FILE_PATH);
    }
    return Response::raw("File not found", 404);
}
if(!empty(trim(REQUEST_HTTP_URL, "\/\\\n\r\t\v\x00")) && file_exists(ROOT . 'public/' . REQUEST_HTTP_URL) && !is_dir(ROOT . 'public/' . REQUEST_HTTP_URL)) {
    Response::file(ROOT . 'public/' . REQUEST_HTTP_URL);
    die();
}

// PAGES REQUEST
require_once(ROOT . 'ophose/ophose_request.php');