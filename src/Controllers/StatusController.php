<?php

namespace App\Controllers;

use Dotenv\Dotenv;
use Laminas\Diactoros\Response;
use Memcached;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StatusController
{
    public function getStatus(ServerRequestInterface $request): ResponseInterface
    {
        // Load the environment variables from the .env file
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2), '.env');
        $dotenv->load();

        $response = new Response();  // Create a new response


        // Check if the cookie is set
        if ($this->getCookieValue($request) !== null) {
            if ($this->getMemcachedSession($this->getCookieValue($request)) !== null) {
                return $response->withStatus(200);  // Return a 200 OK response
            }
        }

        return $response->withStatus(403);  // Return a 403 Forbidden response
    }

    public function getCookieValue(ServerRequestInterface $request): ?string
    {
        $cookies = $request->getCookieParams();  // Get the cookies from the request

        if (!isset($cookies[$_ENV['COOKIE_NAME']])) {
            return null;  // Return an empty string if the cookie is not set
        }

        return $cookies[$_ENV['COOKIE_NAME']];  // Return the value of the cookie
    }

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