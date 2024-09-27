<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Controllers\StatusController;
use League\Route\Router;
use Laminas\Diactoros\ServerRequestFactory;

// Create a PSR-7 request from the superglobals
$request = ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$router = new Router();  // Create a new router

// Define a route that responds to GET /status
$router->map('GET', '/status', [new StatusController(), 'getStatus']);

$response = $router->dispatch($request);  // Dispatch the request

(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);  // Emit the response