<?php

namespace App\Controllers;

use Memcached;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers StatusController
 */
#[CoversClass(StatusController::class)]
class StatusControllerTest extends TestCase
{
    /**
     * Test getMemcachedSession method when session is set
     *
     * @covers ::getMemcachedSession
     * @returns void
     */
    public function testGetMemcachedSession(): void
    {
        $memcached = new Memcached();
        $memcached->addServer(getenv('MEMCACHED_HOST'), getenv('MEMCACHED_PORT'));
        $memcached->set('12345', ['test']);

        $statusController = new StatusController();
        $this->assertEquals(['test'], $statusController->getMemcachedSession('12345'));
    }

    /**
     * Test getMemcachedSession method when session is not set
     *
     * @covers ::getMemcachedSession
     * @returns void
     */
    public function testGetMemcachedSessionReturnsNull(): void
    {
        $memcached = new Memcached();
        $memcached->addServer(getenv('MEMCACHED_HOST'), getenv('MEMCACHED_PORT'));
        $memcached->delete('12345');

        $statusController = new StatusController();
        $this->assertEquals(null, $statusController->getMemcachedSession('12345'));
    }

    /**
     * Test getCookieValue method when cookie is set
     *
     * @covers ::getCookieValue
     * @throws Exception
     * @returns void
     */
    public function testGetCookieValue(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')
            ->willReturn([getenv('COOKIE_NAME') => '12345']);

        $statusController = new StatusController();
        $this->assertEquals('12345', $statusController->getCookieValue($request));
    }

    /**
     * Test getCookieValue method when cookie is not set
     *
     * @covers ::getCookieValue
     * @throws Exception
     * @returns void
     */
    public function testGetCookieValueReturnsNull(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')
            ->willReturn([]);

        $statusController = new StatusController();
        $this->assertEquals(null, $statusController->getCookieValue($request));
    }

    /**
     * Test getStatus method when cookie is set and session is not null
     *
     * @covers ::getStatus
     * @throws Exception
     * @returns void
     */
    public function testGetStatusReturns200WhenSessionKeyIsSet(): void
    {

        $memcached = new Memcached();
        $memcached->addServer(getenv('MEMCACHED_HOST'), getenv('MEMCACHED_PORT'));
        $memcached->set('12345', ['test']);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')
            ->willReturn([getenv('COOKIE_NAME') => '12345']);

        $statusController = new StatusController();
        $response = $statusController->getStatus($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test getStatus method when cookie is set and session is null
     *
     * @covers ::getStatus
     * @throws Exception
     * @returns void
     */
    public function testGetStatusReturns403WhenSessionIsNull(): void
    {
        $memcached = new Memcached();
        $memcached->addServer(getenv('MEMCACHED_HOST'), getenv('MEMCACHED_PORT'));
        $memcached->delete('12345');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')
            ->willReturn([getenv('COOKIE_NAME') => '12345']);

        $statusController = new StatusController();
        $response = $statusController->getStatus($request);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * Test getStatus method when cookie is not set
     *
     * @covers ::getStatus
     * @throws Exception
     * @returns void
     */
    public function testGetStatusReturns403WhenSessionKeyIsNotSet(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')
            ->willReturn([]);

        $statusController = new StatusController();
        $response = $statusController->getStatus($request);

        $this->assertEquals(403, $response->getStatusCode());
    }
}
