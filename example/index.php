<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Websocket implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New client! ({$conn->resourceId})\n\n";
    }

    public function onMessage(ConnectionInterface $from, $message)
    {
        // $numRecv = count($this->clients) - 1;

        echo sprintf('Client %d sent: "%s" ' . "\n",  $from->resourceId,  $message);

        $from->send($message);

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($message);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}



$app = new \Ratchet\App('localhost', 8080);
$app->route('/media-stream', new Websocket, array('*'));
$app->run();

// $server = IoServer::factory(
//     new HttpServer(
//         new WsServer(
//             new Websocket()
//         )
//     ),
//     8080
// );       
// $server->run();