<?php

namespace App\Controllers;

use Memcached;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers StatusController
 */
class StatusControllerTest extends TestCase
{
    /**
     * Test getStatus method when cookie is set and session is not null
     *
     * @covers ::getStatus
     * @throws Exception
     */
    public function testGetStatusReturns200WhenSessionKeyIsSet()
    {

        $memcached = new Memcached();
        $memcached->addServer($_ENV['MEMCACHED_HOST'], $_ENV['MEMCACHED_PORT']);
        $memcached->set('123456', ['test']);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')
            ->willReturn([$_ENV['COOKIE_NAME'] => '123456']);

        $statusController = new StatusController();
        $response = $statusController->getStatus($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test getStatus method when cookie is set and session is null
     *
     * @covers ::getStatus
     * @throws Exception
     */
    public function testGetStatusReturns403WhenSessionIsNull()
    {
        $memcached = new Memcached();
        $memcached->addServer($_ENV['MEMCACHED_HOST'], $_ENV['MEMCACHED_PORT']);
        $memcached->delete('123456');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')
            ->willReturn([$_ENV['COOKIE_NAME'] => '123456']);

        $statusController = new StatusController();
        $response = $statusController->getStatus($request);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test getStatus method when cookie is not set
     *
     * @covers ::getStatus
     * @throws Exception
     */
    public function testGetStatusReturns403WhenSessionKeyIsNotSet()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')
            ->willReturn([]);

        $statusController = new StatusController();
        $response = $statusController->getStatus($request);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test getCookieValue method when cookie is set
     *
     * @covers ::getCookieValue
     * @throws Exception
     */
    public function testGetCookieValue()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')
            ->willReturn([$_ENV['COOKIE_NAME'] => '123456']);

        $statusController = new StatusController();
        $this->assertEquals('123456', $statusController->getCookieValue($request));
    }

    /**
     * Test getCookieValue method when cookie is not set
     *
     * @covers ::getCookieValue
     * @throws Exception
     */
    public function testGetCookieValueReturnsNull()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')
            ->willReturn([]);

        $statusController = new StatusController();
        $this->assertEquals(null, $statusController->getCookieValue($request));
    }

    /**
     * Test getMemcachedSession method when session is set
     *
     * @covers ::getMemcachedSession
     */
    public function testGetMemcachedSession()
    {
        $_ENV['MEMCACHED_HOST'] = 'grima_proxy_auth_memcached_test';
        $_ENV['MEMCACHED_PORT'] = '11211';

        $memcached = new Memcached();
        $memcached->addServer($_ENV['MEMCACHED_HOST'], $_ENV['MEMCACHED_PORT']);
        $memcached->set('123456', ['test']);

        $statusController = new StatusController();
        $this->assertEquals(['test'], $statusController->getMemcachedSession('123456'));

    }

    /**
     * Test getMemcachedSession method when session is not set
     *
     * @covers ::getMemcachedSession
     */
    public function testGetMemcachedSessionReturnsNull()
    {
        $_ENV['MEMCACHED_HOST'] = 'grima_proxy_auth_memcached_test';
        $_ENV['MEMCACHED_PORT'] = '11211';

        $memcached = new Memcached();
        $memcached->addServer($_ENV['MEMCACHED_HOST'], $_ENV['MEMCACHED_PORT']);
        $memcached->delete('123456');

        $statusController = new StatusController();
        $this->assertEquals(null, $statusController->getMemcachedSession('123456'));
    }
}
