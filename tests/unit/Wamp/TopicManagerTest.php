<?php

namespace Appsaeed\Wamp;

use PHPUnit\Framework\TestCase;

class TopicManagerTest extends TestCase
{
    private $mock;
    private $mngr;
    private $conn;

    public function setUp(): void
    {
        $this->conn = $this->createMock(\Appsaeed\ConnectionInterface::class);
        $this->mock = $this->createMock(\Appsaeed\Wamp\WampServerInterface::class);
        $this->mngr = new TopicManager($this->mock);

        $this->conn->WAMP = new \StdClass;
        $this->mngr->onOpen($this->conn);
    }

    public function testGetTopicReturnsTopicObject(): void
    {
        $class = new \ReflectionClass('Appsaeed\Wamp\TopicManager');
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, ['The Topic']);
        $this->assertInstanceOf('Appsaeed\Wamp\Topic', $topic);
    }

    // Update to PHPUnit 9+ assertions
    public function testGetSubProtocolsReturnsArray(): void
    {
        $this->assertIsArray($this->mngr->getSubProtocols());
    }

    // Additional tests...
}
