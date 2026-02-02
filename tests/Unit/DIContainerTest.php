<?php

declare(strict_types=1);

namespace Tests\Unit;

use PedhotDev\LavalinkPHP\Container\ContainerFactory;
use PedhotDev\LavalinkPHP\NodePool;
use PedhotDev\LavalinkPHP\Factory\NodeFactory;
use PedhotDev\LavalinkPHP\Config\NodeConfiguration;
use React\EventLoop\LoopInterface;
use Mockery;

test('it can resolve core services from the container', function () {
    $loop = Mockery::mock(LoopInterface::class);
    $container = ContainerFactory::create([
        LoopInterface::class => $loop
    ]);
    
    expect($container->get(NodePool::class))->toBeInstanceOf(NodePool::class);
    expect($container->get(NodeFactory::class))->toBeInstanceOf(NodeFactory::class);
    
    // Check singleton behavior
    $pool1 = $container->get(NodePool::class);
    $pool2 = $container->get(NodePool::class);
    expect($pool1)->toBe($pool2);
});

test('it can create a node via the resolved factory', function () {
    $loop = Mockery::mock(LoopInterface::class);
    $container = ContainerFactory::create([
        LoopInterface::class => $loop
    ]);
    
    $factory = $container->get(NodeFactory::class);
    $config = new NodeConfiguration('test', '127.0.0.1', 2333, 'pw');
    
    $node = $factory->createNode($config, 'user-id', $loop);
    
    expect($node)->toBeInstanceOf(\PedhotDev\LavalinkPHP\Node::class);
    expect($node->identifier)->toBe('test');
});
