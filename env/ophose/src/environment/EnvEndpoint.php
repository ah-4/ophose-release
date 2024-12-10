<?php

namespace Ophose\Environment;

use Closure;

class EnvEndpoint {

    private string $endpoint;
    private string $name;
    private $callback;
    private bool $csrf = false;
    private array $methods = ['*'];
    private array $params = [];
    private bool $locked = false;

    /**
     * EnvEndpoint constructor.
     *
     * @param string $endpoint The endpoint. (e.g. 'create' or 'edit/_id')
     * @param array|callable|string $callback The callback.
     */
    public function __construct(string $endpoint, $callback) {
        $this->endpoint = $endpoint;
        $this->callback = $callback;
    }

    /**
     * Lock the endpoint.
     *
     * @return EnvEndpoint The endpoint.
     */
    public function lock() {
        $this->locked = true;
        return $this;
    }

    /**
     * Set the callback for the endpoint.
     *
     * @param array|callable|string $callback The callback for the endpoint.
     * @return EnvEndpoint The endpoint.
     */
    public function to($callback): EnvEndpoint {
        if(!$this->locked) $this->callback = $callback;
        return $this;
    }

    /**
     * Get the name of the endpoint.
     *
     * @return string The name of the endpoint.
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * If the endpoint requires CSRF protection.
     * 
     * @param bool $csrf Whether the endpoint requires CSRF protection.
     * @return EnvEndpoint The endpoint.
     */
    public function csrf(bool $csrf = true): EnvEndpoint {
        if(!$this->locked) $this->csrf = $csrf;
        return $this;
    }

    /**
     * Get whether the endpoint requires CSRF protection.
     *
     * @return bool Whether the endpoint requires CSRF protection.
     */
    public function requiresCsrf(): bool {
        return $this->csrf;
    }

    /**
     * Set the methods for the endpoint.
     *
     * @param array $methods The methods for the endpoint.
     * @return EnvEndpoint The endpoint.
     */
    public function methods(...$methods): EnvEndpoint {
        if(!$this->locked) $this->methods = $methods;
        return $this;
    }

    /**
     * Set the methods for the endpoint to GET.
     */
    public function get(): EnvEndpoint {
        return $this->methods('GET');
    }

    /**
     * Set the methods for the endpoint to POST.
     */
    public function post(): EnvEndpoint {
        return $this->methods('POST');
    }

    /**
     * Set the methods for the endpoint to PUT.
     */
    public function put(): EnvEndpoint {
        return $this->methods('PUT');
    }

    /**
     * Set the methods for the endpoint to PATCH.
     */
    public function patch(): EnvEndpoint {
        return $this->methods('PATCH');
    }

    /**
     * Set the methods for the endpoint to DELETE.
     */
    public function delete(): EnvEndpoint {
        return $this->methods('DELETE');
    }

    /**
     * Set the methods for the endpoint to HEAD.
     */
    public function head(): EnvEndpoint {
        return $this->methods('HEAD');
    }

    /**
     * Set the methods for the endpoint to OPTIONS.
     */
    public function options(): EnvEndpoint {
        return $this->methods('OPTIONS');
    }

    /**
     * Set the methods for the endpoint to POST, PUT, and PATCH.
     */
    public function edit() {
        return $this->methods('POST', 'PUT', 'PATCH');
    }

    /**
     * Set the method for the endpoint.
     *
     * @param string $method The method for the endpoint.
     * @return EnvEndpoint The endpoint.
     */
    public function method(string $method): EnvEndpoint {
        if(!$this->locked) $this->methods = [$method];
        return $this;
    }
        
    /**
     * Get the methods for the endpoint.
     *
     * @return array The methods for the endpoint.
     */
    public function getMethods(): array {
        return $this->methods;
    }

    /**
     * Get the callback for the endpoint.
     *
     * @return array|Closure|callable|string The callback for the endpoint.
     */
    public function getCallback(): array|Closure|callable|string {
        return $this->callback;
    }

    /**
     * Get the endpoint.
     *
     * @return string The endpoint.
     */
    public function getEndpoint(): string {
        return $this->endpoint;
    }

    /**
     * Set the params for the endpoint.
     *
     * @param array $params The params for the endpoint.
     * @return EnvEndpoint The endpoint.
     */
    public function params($params): EnvEndpoint {
        if(!$this->locked) $this->params = $params;
        return $this;
    }

    /**
     * Get the params for the endpoint.
     *
     * @return array The params for the endpoint.
     */
    public function getParams(): array {
        return $this->params;
    }

}