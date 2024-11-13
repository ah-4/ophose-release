<?php

namespace Ophose;

class Response {

    private int $status = 200;
    private array $headers = [];
    private ?string $body = "";
    private static ?Response $last_response = null;

    /**
     * Send a JSON response
     *
     * @param array $data The data to send
     * @param integer $status The HTTP status code
     * @return void
     */
    public function json(array $data, int $status = 200) {
        $response = new Response();
        $response->setHeader("Content-Type", "application/json");
        $response->setBody(json_encode($data));
        $response->setStatus($status);
    }

    /**
     * Send a raw response
     *
     * @param mixed $data The data to send
     * @param integer $status The HTTP status code
     * @return void
     */
    public function raw(mixed $data, int $status = 200) {
        $response = new Response();
        $response->setBody($data);
        $response->setStatus($status);
    }

    /**
     * Send a file response
     *
     * @param string $filePath The path to the file
     * @param integer $status The HTTP status code
     * @return void
     */
    public function file(string $filePath, int $status = 200) {
        $response = new Response();
        if(!file_exists($filePath) || is_dir($filePath)) {
            $response->setBody("File not found");
            $response->setStatus(404);
        }
        $response->setHeader("Content-Length", filesize($filePath));
        $response->setHeader("Content-Type", mime_content_type($filePath));
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if($extension === "css") $response->setHeader("Content-Type", "text/css");
        $response->setBody(file_get_contents($filePath));
        $response->setStatus($status);
    }

    /**
     * Send a file download response
     *
     * @param string $filePath The path to the file
     * @param string $fileName The name of the file
     * @param integer $status The HTTP status code
     * @return void
     */
    public function download(string $filePath, string $fileName = null, int $status = 200) {
        $response = new Response();
        if(!file_exists($filePath) || is_dir($filePath)) {
            $response->setBody("File not found");
            $response->setStatus(404);
        }
        if($fileName === null) $fileName = basename($filePath);
        header("Content-Type: application/octet-stream");
        header("Content-Description: File Transfer");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Send an HTML response
     *
     * @param string $html The HTML to send
     * @param integer $status The HTTP status code
     * @return void
     */
    public function html(string $html, int $status = 200) {
        $response = new Response();
        $response->setHeader("Content-Type", "text/html");
        $response->setBody($html);
        $response->setStatus($status);
    }

    /**
     * Redirect to a URL
     *
     * @param string $url The URL to redirect to
     * @param integer $status The HTTP status code
     * @return void
     */
    public function redirect(string $url, int $status = 302) {
        header('Location: ' . $url);
        exit;
    }

    public function directive(Directive $directive) {
        return response()->json([
            "ophose_encoded_directives" => $directive->getDirectives()
        ]);
    }

    /**
     * Construct a new response
     *
     * @param string $body The response body
     * @param integer $status The HTTP status code
     * @param array $headers The HTTP headers
     * @return void
     */
    public function __construct(string $body = "", int $status = 200, array $headers = []) {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
        self::$last_response = $this;
    }

    /**
     * Set the response status code
     *
     * @param integer $status The HTTP status code
     * @return void
     */
    public function setStatus(int $status) {
        $this->status = $status;
    }

    /**
     * Add a header to the response
     *
     * @param array $headers The HTTP headers
     * @return void
     */
    public function setHeader(string $header, string $value) {
        $this->headers[$header] = $value;
    }

    /**
     * Set the response body
     *
     * @param string $body The response body
     * @return void
     */
    public function setBody(?string $body) {
        if($body === null) $body = "null";
        $this->body = $body;
    }

    /**
     * Send the HTTP response
     *
     * @return void
     */
    private function sendHttpResponse() {
        http_response_code($this->status);
        foreach($this->headers as $header => $value) {
            header($header . ": " . $value);
        }
        echo $this->body;
    }

    /**
     * Send the last response then die (or only set the last response if in test mode)
     * 
     * @return void
     */
    public static function sendLastResponseAndDie() {
        $last_response = self::$last_response;
        if(defined('TEST_MODE') && TEST_MODE) return;
        if($last_response) $last_response->sendHttpResponse();
        die();
    }

    /**
     * Get the last response
     *
     * @return Response The last response
     */
    public static function getLastResponse() {
        return self::$last_response;
    }

    /**
     * Clear the last response
     *
     * @return void
     */
    public static function clearLastResponse() {
        self::$last_response = null;
    }

    /**
     * Get the response status code
     * 
     * @return integer The response status code
     */
    public function status() {
        return $this->status;
    }

    /**
     * Get the response body
     * 
     * @return string The response body
     */
    public function body() {
        return $this->body;
    }

    /**
     * Get the response content type
     * 
     * @return string The response content type
     */
    public function contentType() {
        return $this->headers["Content-Type"];
    }

}

function response(string $body = "", int $status = 200, array $headers = []) {
    $response = new Response($body, $status, $headers);
    return $response;
}
