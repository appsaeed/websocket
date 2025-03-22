<?php

namespace Appsaeed\Wamp;

use PHPUnit\Framework\TestCase;
use Appsaeed\AbstractMessageComponentTestCase;

/**
 * @covers Appsaeed\Wamp\WampServer
 */
class WampServerTest extends AbstractMessageComponentTestCase
{
    public function getConnectionClassString()
    {
        return '\Appsaeed\Wamp\WampConnection';
    }

    public function getDecoratorClassString()
    {
        return 'Appsaeed\Wamp\WampServer';
    }

    public function getComponentClassString()
    {
        return '\Appsaeed\Wamp\WampServerInterface';
    }

    public function testOnMessageToEvent()
    {
        $published = 'Client published this message';

        $this->_app->expects($this->once())
            ->method('onPublish')
            ->with(
                $this->isExpectedConnection(),
                $this->isInstanceOf(\Appsaeed\Wamp\Topic::class),
                $published,
                [],
                []
            );

        $this->_serv->onMessage($this->_conn, json_encode([7, 'topic', $published]));
    }

    public function testGetSubProtocols()
    {
        // todo: could expand on this
        $this->assertIsArray($this->_serv->getSubProtocols());
    }

    public function testConnectionClosesOnInvalidJson()
    {
        $this->_conn->expects($this->once())->method('close');
        $this->_serv->onMessage($this->_conn, 'invalid json');
    }

    public function testConnectionClosesOnProtocolError()
    {
        $this->_conn->expects($this->once())->method('close');
        $this->_serv->onMessage($this->_conn, json_encode(['valid' => 'json', 'invalid' => 'protocol']));
    }
}
