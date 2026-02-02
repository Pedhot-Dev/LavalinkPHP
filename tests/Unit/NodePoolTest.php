<?php

declare(strict_types=1);

namespace Tests\Unit;

use PedhotDev\LavalinkPHP\Node;
use PedhotDev\LavalinkPHP\NodePool;
use PedhotDev\LavalinkPHP\Factory\NodeFactory;
use PedhotDev\LavalinkPHP\Http\RestClient;
use PedhotDev\LavalinkPHP\WebSocket\LavalinkWebSocket;
use PedhotDev\LavalinkPHP\Factory\PlayerFactory;
use React\EventLoop\LoopInterface;
use Mockery;
use RuntimeException;
use ReflectionClass;

beforeEach(function () {
    $this->nodeFactory = Mockery::mock(NodeFactory::class);
    $this->nodePool = new NodePool($this->nodeFactory);
});

function createRealNode(string $identifier, int $playingPlayers = 0): Node {
    $ws = Mockery::mock(LavalinkWebSocket::class);
    $ws->shouldReceive('on')->andReturnNull();
    
    $node = new Node(
        $identifier,
        Mockery::mock(RestClient::class),
        $ws,
        'user-id',
        Mockery::mock(LoopInterface::class),
        Mockery::mock(PlayerFactory::class)
    );
    
    // Use reflection to set the private 'stats' property
    $reflection = new ReflectionClass(Node::class);
    $statsProp = $reflection->getProperty('stats');
    $statsProp->setValue($node, [
        'playingPlayers' => $playingPlayers,
        'cpu' => ['systemLoad' => 0.1],
    ]);
    
    return $node;
}

test('it can add and retrieve nodes', function () {
    $node = createRealNode('test-node');
    
    $this->nodePool->addNode($node);
    
    expect($this->nodePool->getNode('test-node'))->toBe($node);
    expect($this->nodePool->getNodes())->toHaveCount(1);
    expect($this->nodePool->getNodes()['test-node'])->toBe($node);
});

test('it selects the best node based on penalty', function () {
    $node1 = createRealNode('node1', 100);
    $node2 = createRealNode('node2', 50);
    
    $this->nodePool->addNode($node1);
    $this->nodePool->addNode($node2);
    
    expect($this->nodePool->getBestNode())->toBe($node2);
});

test('it throws exception when getBestNode is called on empty pool', function () {
    $this->nodePool->getBestNode();
})->throws(RuntimeException::class, 'No nodes available in pool.');
