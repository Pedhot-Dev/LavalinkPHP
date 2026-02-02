<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\Container;

use PedhotDev\NepotismFree\NepotismFree;
use PedhotDev\NepotismFree\Contract\ContainerInterface;

class ContainerFactory
{
    /**
     * Bootstraps a new DI container with the Lavalink module.
     */
    public static function create(): ContainerInterface
    {
        $builder = NepotismFree::createBuilder();
        $builder->addModule(new LavalinkModule());
        
        return $builder->build();
    }
}
