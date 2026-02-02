<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\Factory;

use PedhotDev\LavalinkPHP\Node;
use PedhotDev\LavalinkPHP\Config\NodeConfiguration;
use PedhotDev\LavalinkPHP\Http\RestClient;
use PedhotDev\LavalinkPHP\WebSocket\LavalinkWebSocket;
use React\EventLoop\LoopInterface;

class NodeFactory
{
    public function __construct(
        private readonly PlayerFactory $playerFactory
    ) {}

    public function createNode(NodeConfiguration $config, string $userId, LoopInterface $loop): Node
    {
        // We manually create sub-services that depend on the runtime config
        $rest = new RestClient($config->getHttpUri(), $config->password);
        $ws = new LavalinkWebSocket($config->getWsUri(), $config->password, $userId, $loop);
        
        return new Node(
            $config->identifier,
            $rest,
            $ws,
            $userId,
            $loop,
            $this->playerFactory
        );
    }
}
