<?php

namespace Appsaeed\Mock;

use Appsaeed\MessageComponentInterface;
use Appsaeed\WebSocket\WsServerInterface;
use Appsaeed\ConnectionInterface;

class Component implements MessageComponentInterface, WsServerInterface
{
    public $last = array();

    public $protocols = array();

    public function __construct(ComponentInterface $app = null)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->last[__FUNCTION__] = func_get_args();
    }

    public function getSubProtocols()
    {
        return $this->protocols;
    }
}
