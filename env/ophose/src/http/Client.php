<?php

namespace Ophose\Http;

use CurlHandle;
use Ophose\Http\Exception\RequestNotSentException;

use function Ophose\Util\configuration;

/**
 * HTTP Client Class to handle HTTP requests like GET, POST, PUT, DELETE, etc.
 */
class Client
{
    /**
     * @var string|null $url The URL to which the HTTP request is sent.
     */
    protected ?string $url = null;

    /**
     * @var string $method The HTTP method used for the request (GET, POST, PUT, etc.).
     */
    protected string $method = 'GET';

    /**
     * @var array $headers The HTTP headers to send with the request.
     */
    protected array $headers = [];

    /**
     * @var array|string|null $body The body of the HTTP request (for POST, PUT, etc.).
     */
    protected array|string|null $body = null;

    /**
     * @var string|false|null $response The response received from the server after sending the request.
     */
    protected string|false|null $response = null;

    /**
     * @var CurlHandle|false|null $ch The cURL handle for the HTTP request.
     */
    private CurlHandle|false|null $ch = null;

    /**
     * @var array $cookies The cookies sent with the request.
     */
    private array $cookies = [];

    /**
     * Client constructor.
     * 
     * @param string $url The base URL for the request.
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Prepares an HTTP request with the specified method, headers, and body.
     * 
     * @param string $method The HTTP method to use (GET, POST, PUT, etc.).
     * @param array $headers Optional HTTP headers to include.
     * @param array|string|null $body Optional body content for the request.
     * @return $this
     */
    public function request(string $method, array $headers = [], string|array|null $body = null): static
    {
        $this->method = $method;
        $this->headers = array_merge($this->headers, $headers);
        $this->body = $body;

        return $this;
    }

    /**
     * Sends a GET request with optional parameters and headers.
     * 
     * @param array $params Query parameters to append to the URL.
     * @param array $headers Optional HTTP headers to include.
     * @return $this
     */
    public function get(array $params = [], array $headers = []): static
    {
        if (count($params) > 0) {
            $this->url .= (strpos($this->url, '?') === false) ? '?' : '&';
            $this->url .= http_build_query($params);
        }
        return $this->request('GET', $headers);
    }

    /**
     * Sends a POST request with optional body content and headers.
     * 
     * @param string|array|null $body The body content for the request.
     * @param array $headers Optional HTTP headers to include.
     * @return $this
     */
    public function post(string|array|null $body = null, array $headers = []): static
    {
        return $this->request('POST', $headers, $body);
    }

    /**
     * Sends a PUT request with optional body content and headers.
     * 
     * @param string|array|null $body The body content for the request.
     * @param array $headers Optional HTTP headers to include.
     * @return $this
     */
    public function put(string|array|null $body = null, array $headers = []): static
    {
        return $this->request('PUT', $headers, $body);
    }

    /**
     * Sends a DELETE request with optional headers.
     * 
     * @param array $headers Optional HTTP headers to include.
     * @return $this
     */
    public function delete(array $headers = []): static
    {
        return $this->request('DELETE', $headers);
    }

    /**
     * Sends a PATCH request with optional body content and headers.
     * 
     * @param string|array|null $body The body content for the request.
     * @param array $headers Optional HTTP headers to include.
     * @return $this
     */
    public function patch(string|array|null $body = null, array $headers = []): static
    {
        return $this->request('PATCH', $headers, $body);
    }

    /**
     * Sends a HEAD request with optional headers.
     * 
     * @param array $headers Optional HTTP headers to include.
     * @return $this
     */
    public function head(array $headers = []): static
    {
        return $this->request('HEAD', $headers);
    }

    /**
     * Sends an OPTIONS request with optional headers.
     * 
     * @param array $headers Optional HTTP headers to include.
     * @return $this
     */
    public function options(array $headers = []): static
    {
        return $this->request('OPTIONS', $headers);
    }

    /**
     * Adds a custom HTTP header to the request.
     * 
     * @param string $key The header key.
     * @param string $value The header value.
     * @return $this
     */
    public function header(string $key, string $value): static
    {
        $this->headers[] = "$key: $value";
        return $this;
    }

    /**
     * Adds a secure header using the application's secure key.
     * 
     * @return $this
     */
    public function secure(): static
    {
        $this->header('X-Secure-Key', configuration()->get('secure_key'));
        return $this;
    }

    /**
     * Adds a Bearer token for authorization.
     * 
     * @param string $token The Bearer token.
     * @return $this
     */
    public function bearer(string $token): static
    {
        $this->header('Authorization', 'Bearer ' . $token);
        return $this;
    }

    /**
     * Sends the HTTP request prepared by the client.
     * 
     * @return $this
     */
    public function send(): static
    {
        // Check if URL is relative and make it absolute if necessary
        if (!preg_match('/^http(s)?:\/\//', $this->url)) {
            $this->url = configuration()->get('url') . $this->url;
        }
        // Clean URL and ensure proper formatting
        $this->url = parse_url($this->url, PHP_URL_SCHEME) . '://' .
                     parse_url($this->url, PHP_URL_HOST) . 
                     preg_replace('/\/+/', '/', parse_url($this->url, PHP_URL_PATH));

        // Check if cookies are set and add them to the headers
        if (count($this->cookies) > 0) {
            $this->headers[] = 'Cookie: ' . http_build_query($this->cookies, '', '; ');
        }

        // Initialize cURL and set options
        $this->ch = curl_init($this->url);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->body !== null) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->body);
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);

        // Execute the request and store the response
        $this->response = curl_exec($this->ch);
        curl_close($this->ch);

        return $this;
    }

    public function cookie(string $name, string $value): static
    {
        $this->cookies[$name] = $value;
        return $this;
    }

    /**
     * Retrieves the HTTP status code of the response.
     * 
     * @return int The HTTP status code.
     * @throws RequestNotSentException If the request was not sent before calling this method.
     */
    public function status(): int
    {
        if ($this->ch === null) {
            throw new RequestNotSentException();
        }
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }

    /**
     * Returns the raw response body as a string.
     * 
     * @return string|false|null The response body or false/null if not available.
     * @throws RequestNotSentException If the request was not sent before calling this method.
     */
    public function response(): string|false|null
    {
        if ($this->ch === null) {
            throw new RequestNotSentException();
        }
        return $this->response;
    }

    /**
     * Decodes the response body as JSON and returns it as an associative array.
     * 
     * @return array|null The decoded JSON response, or null if decoding fails.
     * @throws RequestNotSentException If the request was not sent before calling this method.
     */
    public function json(): ?array
    {
        if ($this->ch === null) {
            throw new RequestNotSentException();
        }
        return json_decode($this->response, true);
    }
}

/**
 * Helper function to create a new HTTP Client instance.
 * 
 * @param string $url The base URL for the request.
 * @return Client A new Client instance.
 */
function client(string $url): Client
{
    return new Client($url);
}