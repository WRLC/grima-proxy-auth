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

        $sessionKey = $this->getCookieValue($request) ?? null;  // Get the value of the cookie

        // Check if the cookie is set
        if (isset($sessionKey)) {
            $sessionData = $this->getMemcachedSession($sessionKey) ?? null;  // Get the value of the cookie
            if (isset($sessionData)) {
                return $response->withStatus(200);  // Return a 200 OK response
            }
        }

        return $response->withStatus(403);  // Return a 403 Forbidden response
    }

    private function getCookieValue(ServerRequestInterface $request): string
    {
        $cookies = $request->getCookieParams();  // Get the cookies from the request

        return $cookies[$_ENV['COOKIE_NAME']];  // Return the value of the cookie
    }

    private function getMemcachedSession(string $sessionKey): array
    {
        $memcached = new Memcached();  // Create a new Memcached instance
        $memcached->addServer($_ENV['MEMCACHED_HOST'], $_ENV['MEMCACHED_PORT']);  // Add server to Memcached instance

        return $memcached->get($sessionKey);  // Get the value of the session key
    }
}