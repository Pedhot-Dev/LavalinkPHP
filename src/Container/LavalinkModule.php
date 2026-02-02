<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\Container;

use PedhotDev\NepotismFree\Builder\ContainerBuilder;
use PedhotDev\NepotismFree\Contract\ModuleInterface;
use PedhotDev\NepotismFree\Contract\ModuleConfiguratorInterface;
use PedhotDev\LavalinkPHP\NodePool;
use PedhotDev\LavalinkPHP\Factory\NodeFactory;
use PedhotDev\LavalinkPHP\Factory\PlayerFactory;
use PedhotDev\LavalinkPHP\Http\RestClient;
use PedhotDev\LavalinkPHP\WebSocket\LavalinkWebSocket;
use React\Http\Browser;
use React\EventLoop\LoopInterface;

class LavalinkModule implements ModuleInterface
{
    /**
     * @return string[]
     */
    public function getExposedServices(): array
    {
        return [
            NodePool::class,
            NodeFactory::class,
            PlayerFactory::class,
        ];
    }

    public function configure(ModuleConfiguratorInterface $builder): void
    {
        $builder->singleton(NodePool::class);
        $builder->singleton(NodeFactory::class);
        $builder->singleton(PlayerFactory::class);
        
        $builder->prototype(RestClient::class);
        $builder->prototype(LavalinkWebSocket::class);
        
        // We expect the loop to be bound by the user (e.g. from DiscordPHP)
        // or we can bind it here if we want a default.
    }
}
