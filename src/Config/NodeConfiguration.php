<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\Config;

/**
 * Immutable configuration for a Lavalink Node.
 */
class NodeConfiguration
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $host,
        public readonly int $port,
        public readonly string $password,
        public readonly bool $secure = false,
    ) {}

    public function getHttpUri(): string
    {
        $scheme = $this->secure ? 'https' : 'http';
        return "{$scheme}://{$this->host}:{$this->port}";
    }

    public function getWsUri(): string
    {
        $scheme = $this->secure ? 'wss' : 'ws';
        return "{$scheme}://{$this->host}:{$this->port}/v4/websocket";
    }
}
