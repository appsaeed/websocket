<?php

namespace Appsaeed\Session;

use PHPUnit\Framework\TestCase;
use Appsaeed\AbstractMessageComponentTestCase;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Psr\Http\Message\RequestInterface;

class SessionProviderTest extends AbstractMessageComponentTestCase
{
    /**
     * @var SessionProvider
     */
    protected $_serv;

    protected function setUp(): void
    {
        // Skipping test due to ini_set issue in PHP 7.2
        $this->markTestIncomplete('Test needs to be updated for ini_set issue in PHP 7.2');

        if (!class_exists('Symfony\Component\HttpFoundation\Session\Session')) {
            $this->markTestSkipped('Dependency of Symfony HttpFoundation failed');
        }

        parent::setUp();
        $this->_serv = new SessionProvider($this->_app, new NullSessionHandler());
    }

    protected function tearDown(): void
    {
        ini_set('session.serialize_handler', 'php');
    }

    public function getConnectionClassString(): string
    {
        return '\Appsaeed\ConnectionInterface';
    }

    public function getDecoratorClassString(): string
    {
        return '\Appsaeed\NullComponent';
    }

    public function getComponentClassString(): string
    {
        return '\Appsaeed\Http\HttpServerInterface';
    }

    public function classCaseProvider(): array
    {
        return [
            ['php', 'Php'],
            ['php_binary', 'PhpBinary'],
        ];
    }

    /**
     * @dataProvider classCaseProvider
     */
    public function testToClassCase(string $in, string $out): void
    {
        $ref = new \ReflectionClass('\\Appsaeed\\Session\\SessionProvider');
        $method = $ref->getMethod('toClassCase');
        $method->setAccessible(true);

        $component = new SessionProvider($this->getMock($this->getComponentClassString()), $this->getMock('\SessionHandlerInterface'));
        $this->assertEquals($out, $method->invokeArgs($component, [$in]));
    }

    public function testConnectionValueFromPdo(): void
    {
        if (!extension_loaded('PDO') || !extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Session test requires PDO and pdo_sqlite');
        }

        $sessionId = md5('testSession');

        $dbOptions = [
            'db_table'    => 'sessions',
            'db_id_col'   => 'sess_id',
            'db_data_col' => 'sess_data',
            'db_time_col' => 'sess_time',
            'db_lifetime_col' => 'sess_lifetime',
        ];

        $pdo = new \PDO("sqlite::memory:");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec(vsprintf("CREATE TABLE %s (%s TEXT NOT NULL PRIMARY KEY, %s BLOB NOT NULL, %s INTEGER NOT NULL, %s INTEGER)", $dbOptions));

        $pdoHandler = new PdoSessionHandler($pdo, $dbOptions);
        $pdoHandler->write($sessionId, '_sf2_attributes|a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}_sf2_flashes|a:0:{}');

        $component  = new SessionProvider($this->getMock($this->getComponentClassString()), $pdoHandler, ['auto_start' => 1]);
        $connection = $this->getMock('Appsaeed\\ConnectionInterface');

        $headers = $this->getMock(RequestInterface::class);
        $headers->expects($this->once())
            ->method('getHeader')
            ->willReturn([ini_get('session.name') . "={$sessionId};"]);

        $component->onOpen($connection, $headers);

        $this->assertEquals('world', $connection->Session->get('hello'));
    }

    protected function newConn(): \Appsaeed\ConnectionInterface
    {
        $conn = $this->getMock('Appsaeed\ConnectionInterface');

        $headers = $this->getMock('Psr\Http\Message\Request', ['getCookie'], ['POST', '/', []]);
        $headers->expects($this->once())
            ->method('getCookie')
            ->with(ini_get('session.name'))
            ->willReturn(null);

        return $conn;
    }

    public function testOnMessageDecorator(): void
    {
        $message = "Database calls are usually blocking  :(";
        $this->_app->expects($this->once())
            ->method('onMessage')
            ->with($this->isExpectedConnection(), $message);

        $this->_serv->onMessage($this->_conn, $message);
    }

    public function testRejectInvalidSerializers(): void
    {
        if (!function_exists('wddx_serialize_value')) {
            $this->markTestSkipped();
        }

        ini_set('session.serialize_handler', 'wddx');
        $this->expectException(\RuntimeException::class);
        new SessionProvider($this->getMock($this->getComponentClassString()), $this->getMock('\SessionHandlerInterface'));
    }

    protected function doOpen($conn): void
    {
        $request = $this->getMock('Psr\Http\Message\RequestInterface');
        $request->expects($this->any())
            ->method('getHeader')
            ->willReturn([]);

        $this->_serv->onOpen($conn, $request);
    }
}
