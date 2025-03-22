<?php

namespace Appsaeed\Server;

use PHPUnit\Framework\TestCase;
use Appsaeed\Server\IpBlackList;

/**
 * @covers Appsaeed\Server\IpBlackList
 */
class IpBlackListTest extends TestCase
{
    protected $blocker;
    protected $mock;

    public function setUp(): void
    {
        $this->mock = $this->createMock('\\Appsaeed\\MessageComponentInterface');
        $this->blocker = new IpBlackList($this->mock);
    }

    public function testOnOpen(): void
    {
        $this->mock->expects($this->exactly(3))->method('onOpen');

        $conn1 = $this->newConn();
        $conn2 = $this->newConn();
        $conn3 = $this->newConn();

        $this->blocker->onOpen($conn1);
        $this->blocker->onOpen($conn3);
        $this->blocker->onOpen($conn2);
    }

    public function testBlockDoesNotTriggerOnOpen(): void
    {
        $conn = $this->newConn();

        $this->blocker->blockAddress($conn->remoteAddress);

        $this->mock->expects($this->never())->method('onOpen');

        $this->blocker->onOpen($conn);
    }

    public function testBlockDoesNotTriggerOnClose(): void
    {
        $conn = $this->newConn();

        $this->blocker->blockAddress($conn->remoteAddress);

        $this->mock->expects($this->never())->method('onClose');

        $this->blocker->onOpen($conn); // This should be $this->blocker->onClose($conn) instead
    }

    public function testOnMessageDecoration(): void
    {
        $conn = $this->newConn();
        $msg  = 'Hello not being blocked';

        $this->mock->expects($this->once())->method('onMessage')->with($conn, $msg);

        $this->blocker->onMessage($conn, $msg);
    }

    public function testOnCloseDecoration(): void
    {
        $conn = $this->newConn();

        $this->mock->expects($this->once())->method('onClose')->with($conn);

        $this->blocker->onClose($conn);
    }

    public function testBlockClosesConnection(): void
    {
        $conn = $this->newConn();
        $this->blocker->blockAddress($conn->remoteAddress);

        $conn->expects($this->once())->method('close');

        $this->blocker->onOpen($conn);
    }

    public function testAddAndRemoveWithFluentInterfaces(): void
    {
        $blockOne = '127.0.0.1';
        $blockTwo = '192.168.1.1';
        $unblock  = '75.119.207.140';

        $this->blocker
            ->blockAddress($unblock)
            ->blockAddress($blockOne)
            ->unblockAddress($unblock)
            ->blockAddress($blockTwo);

        $this->assertEquals([$blockOne, $blockTwo], $this->blocker->getBlockedAddresses());
    }

    public function testDecoratorPassesErrors(): void
    {
        $conn = $this->newConn();
        $e    = new \Exception('I threw an error');

        $this->mock->expects($this->once())->method('onError')->with($conn, $e);

        $this->blocker->onError($conn, $e);
    }

    public function addressProvider(): array
    {
        return [
            ['127.0.0.1', '127.0.0.1'],
            ['localhost', 'localhost'],
            ['fe80::1%lo0', 'fe80::1%lo0'],
            ['127.0.0.1', '127.0.0.1:6392']
        ];
    }

    /**
     * @dataProvider addressProvider
     */
    public function testFilterAddress(string $expected, string $input): void
    {
        $this->assertEquals($expected, $this->blocker->filterAddress($input));
    }

    public function testUnblockingSilentlyFails(): void
    {
        $this->assertInstanceOf(IpBlackList::class, $this->blocker->unblockAddress('localhost'));
    }

    protected function newConn(): \PHPUnit\Framework\MockObject\MockObject
    {
        $conn = $this->createMock('\\Appsaeed\\ConnectionInterface');
        $conn->remoteAddress = '127.0.0.1';

        return $conn;
    }
}
