<?php

namespace Appsaeed\WebSocket\Stub;

use Appsaeed\MessageComponentInterface;
use Appsaeed\WebSocket\WsServerInterface;

interface WsMessageComponentInterface extends MessageComponentInterface, WsServerInterface {}
