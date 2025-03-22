<?php

namespace Appsaeed\Application\Server;

use PHPUnit\Framework\TestCase;
use Appsaeed\Server\IoConnection;
use React\Socket\ConnectionInterface;

/**
 * @covers Appsaeed\Server\IoConnection
 */
class IoConnectionTest extends TestCase
{
    protected $sock;
    protected $conn;

    protected function setUp(): void
    {
        $this->sock = $this->createMock(ConnectionInterface::class);
        $this->conn = new IoConnection($this->sock);
    }

    public function testCloseBubbles()
    {
        $this->sock->expects($this->once())->method('end');
        $this->conn->close();
    }

    public function testSendBubbles()
    {
        $msg = '6 hour rides are productive';

        $this->sock->expects($this->once())->method('write')->with($msg);
        $this->conn->send($msg);
    }

    public function testSendReturnsSelf()
    {
        $this->assertSame($this->conn, $this->conn->send('fluent interface'));
    }
}
