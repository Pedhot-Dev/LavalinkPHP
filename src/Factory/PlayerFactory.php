<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\Factory;

use PedhotDev\LavalinkPHP\Player;
use PedhotDev\LavalinkPHP\Node;

class PlayerFactory
{
    public function createPlayer(Node $node, string $guildId): Player
    {
        return new Player($node, $guildId);
    }
}
