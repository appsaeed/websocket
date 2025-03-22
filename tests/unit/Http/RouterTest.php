<?php

namespace Appsaeed\Http;

use PHPUnit\Framework\TestCase;
use Appsaeed\WebSocket\WsServer;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @covers Appsaeed\Http\Router
 */
class RouterTest extends TestCase
{
    protected $_router;
    protected $_matcher;
    protected $_conn;
    protected $_uri;
    protected $_req;

    public function setUp(): void
    {
        $this->_conn = $this->createMock(\Appsaeed\ConnectionInterface::class);
        $this->_uri  = $this->createMock(UriInterface::class);
        $this->_req  = $this->createMock(RequestInterface::class);
        $this->_matcher = $this->createMock(UrlMatcherInterface::class);

        $this->_matcher->method('getContext')->willReturn($this->createMock(RequestContext::class));
        $this->_router  = new Router($this->_matcher);
    }

    public function testNullRequest()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->_router->onOpen($this->_conn);
    }
}
