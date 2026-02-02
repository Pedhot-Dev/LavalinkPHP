<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\WebSocket;

use Evenement\EventEmitter;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;
use React\Socket\Connector as SocketConnector;

class LavalinkWebSocket extends EventEmitter
{
    private ?WebSocket $connection = null;

    public function __construct(
        private readonly string $uri,
        private readonly string $password,
        private readonly string $userId,
        private readonly LoopInterface $loop
    ) {}

    public function connect(): void
    {
        $connector = new Connector($this->loop, new SocketConnector($this->loop));
        $headers = [
            'Authorization' => $this->password,
            'User-Id' => $this->userId,
            'Client-Name' => 'WavelinkPHP/1.0'
        ];

        $connector($this->uri, [], $headers)->then(
            function (WebSocket $conn) {
                $this->connection = $conn;
                $this->emit('ready');

                $conn->on('message', function ($msg) {
                    $data = json_decode((string) $msg, true);
                    if ($data) {
                        $this->emit('event', [$data]);
                    }
                });

                $conn->on('close', function ($code = null, $reason = null) {
                    $this->connection = null;
                    $this->emit('close', [$code, $reason]);
                });
            },
            function (\Exception $e) {
                $this->emit('error', [$e]);
            }
        );
    }

    public function send(array $data): void
    {
        if ($this->connection) {
            $this->connection->send(json_encode($data));
        }
    }

    public function close(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
