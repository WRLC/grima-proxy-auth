<?php

namespace App\Controllers;

use Dotenv\Dotenv;
use Laminas\Diactoros\Response;
use Memcached;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StatusController
{
    /**
     * Get the status of the session
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     *
     */
    public function getStatus(ServerRequestInterface $request): ResponseInterface
    {
        // Load the environment variables from the .env file
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2), '.env');
        $dotenv->safeLoad();

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

        if (!isset($cookies[$_ENV['COOKIE_NAME']])) {
            return null;  // Return an empty string if the cookie is not set
        }

        return $cookies[$_ENV['COOKIE_NAME']];  // Return the value of the cookie
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
        $memcached->addServer($_ENV['MEMCACHED_HOST'], $_ENV['MEMCACHED_PORT']);  // Add server to Memcached instance

        if ($memcached->get($sessionKey) === false) {
            return null;  // Return an empty array if the session key is not set
        }

        return $memcached->get($sessionKey);  // Get the value of the session key
    }
}