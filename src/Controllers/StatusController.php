<?php

namespace App\Controllers;

use Laminas\Diactoros\Response;
use Memcached;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StatusController
{
    private array $env;

    public function __construct($env)
    {
        $this->env = $env;
    }

    /**
     * Get the status of the session
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     *
     */
    public function getStatus(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response();  // Create a new response


        // If cookie is set...
        if ($this->getCookieValue($request) !== null) {
            // If session is set...
            if ($this->getMemcachedSession($this->getCookieValue($request)) !== null) {
                return $response->withStatus(200);  // ...return a 200 OK response
            }
        }

        // If cookie is not set or session is not set...
        return $response->withStatus(403);  // ..return a 403 Forbidden response
    }

    /**
     * Get the value of the cookie
     *
     * @param ServerRequestInterface $request
     * @return string|null
     *
     */
    public function getCookieValue(ServerRequestInterface $request): ?string
    {
        $cookies = $request->getCookieParams();  // Get the cookies from the request
        $cookieName = $this->env['COOKIE_NAME'];  // Get the name of the cookie
        if (!isset($cookies[$cookieName])) {
            return null;  // Return an empty string if the cookie is not set
        }

        return $cookies[$cookieName];  // Return the value of the cookie
    }

    /**
     * Get the value of the session key
     *
     * @param string $sessionKey
     * @return array|null
     *
     */
    public function getMemcachedSession(string $sessionKey): ?array
    {
        $memcached = new Memcached();  // Create a new Memcached instance
        $memcached->addServer($this->env['MEMCACHED_HOST'], $this->env['MEMCACHED_PORT']);  // Add Memcached server

        if ($memcached->get($sessionKey) === false) {
            return null;  // Return an empty array if the session key is not set
        }

        $rawSession = $memcached->get($sessionKey);  // Get the value of the session key
        $lines = explode("\n", $rawSession);  // Split the session string into an array of lines
        $session = [];  // Create an empty array to store the session data
        foreach ($lines as $line) {  // Loop through each line in the session string
            $parts = explode('=', $line);  // Split the line into key-value pairs
            if (count($parts) === 2) {  // If the line contains a key-value pair
                $session[$parts[0]] = $parts[1];  // Add the key-value pair to the session array
            }
        }

        return $session;  // Return the session array
    }
}
