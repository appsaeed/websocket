<?php

namespace Appsaeed\Http;

use Appsaeed\AbstractMessageComponentTestCase;
use Psr\Http\Message\RequestInterface;
use Appsaeed\ConnectionInterface;
use Appsaeed\Http\OriginCheck;
use Appsaeed\Http\HttpServerInterface;

/**
 * @covers Appsaeed\Http\OriginCheck
 */
class OriginCheckTest extends AbstractMessageComponentTestCase
{
    protected RequestInterface $_reqStub;

    protected function setUp(): void
    { // ✅ FIX: Added ": void"
        $this->_reqStub = $this->createMock(RequestInterface::class); // ✅ FIX: Use createMock()
        $this->_reqStub->expects($this->any())->method('getHeader')->willReturn(['localhost']);

        parent::setUp();

        $this->_serv->allowedOrigins[] = 'localhost';
    }

    protected function doOpen($conn)
    {
        $this->_serv->onOpen($conn, $this->_reqStub);
    }

    public function getConnectionClassString(): string
    {
        return ConnectionInterface::class; // ✅ FIX: Use ::class
    }

    public function getDecoratorClassString(): string
    {
        return OriginCheck::class;
    }

    public function getComponentClassString(): string
    {
        return HttpServerInterface::class;
    }

    public function testCloseOnNonMatchingOrigin()
    {
        $this->_serv->allowedOrigins = ['socketo.me'];
        $this->_conn->expects($this->once())->method('close');

        $this->_serv->onOpen($this->_conn, $this->_reqStub);
    }

    public function testOnMessage()
    {
        $this->passthroughMessageTest('Hello World!');
    }
}
