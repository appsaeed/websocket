<?php

namespace Appsaeed;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use Appsaeed\ConnectionInterface;
use Exception;

abstract class AbstractMessageComponentTestCase extends TestCase
{
    protected $_app;
    protected $_serv;
    protected $_conn;

    abstract public function getConnectionClassString();
    abstract public function getDecoratorClassString();
    abstract public function getComponentClassString();

    protected function setUp(): void
    {
        $this->_app  = $this->createMock($this->getComponentClassString());
        $decorator   = $this->getDecoratorClassString();
        $this->_serv = new $decorator($this->_app);
        $this->_conn = $this->createMock(ConnectionInterface::class);

        $this->doOpen($this->_conn);
    }

    protected function doOpen($conn)
    {
        $this->_serv->onOpen($conn);
    }

    public function isExpectedConnection(): IsInstanceOf
    {
        return new IsInstanceOf($this->getConnectionClassString());
    }

    public function testOpen()
    {
        $this->_app->expects($this->once())->method('onOpen')->with($this->isExpectedConnection());
        $this->doOpen($this->createMock(ConnectionInterface::class));
    }

    public function testOnClose()
    {
        $this->_app->expects($this->once())->method('onClose')->with($this->isExpectedConnection());
        $this->_serv->onClose($this->_conn);
    }

    public function testOnError()
    {
        $e = new Exception('Whoops!');
        $this->_app->expects($this->once())->method('onError')->with($this->isExpectedConnection(), $e);
        $this->_serv->onError($this->_conn, $e);
    }

    public function passthroughMessageTest($value)
    {
        $this->_app->expects($this->once())->method('onMessage')->with($this->isExpectedConnection(), $value);
        $this->_serv->onMessage($this->_conn, $value);
    }
}
