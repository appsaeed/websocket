<?php

namespace Appsaeed\Wamp\Stub;

use Appsaeed\WebSocket\WsServerInterface;
use Appsaeed\Wamp\WampServerInterface;

interface WsWampServerInterface extends WsServerInterface, WampServerInterface {}
