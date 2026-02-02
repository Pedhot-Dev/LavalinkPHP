<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use PedhotDev\LavalinkPHP\Http\RestClient;
use PedhotDev\LavalinkPHP\WebSocket\LavalinkWebSocket;
use PedhotDev\LavalinkPHP\Factory\PlayerFactory;

class Node extends EventEmitter
{
    private array $players = [];
    private array $stats = [];
    private ?string $sessionId = null;

    public function __construct(
        public readonly string $identifier,
        private readonly RestClient $rest,
        private readonly LavalinkWebSocket $ws,
        private readonly string $userId,
        private readonly LoopInterface $loop,
        private readonly PlayerFactory $playerFactory
    ) {
        $this->setupWsListeners();
    }

    private function setupWsListeners(): void
    {
        $this->ws->on('event', function ($data) {
            $this->handleEvent($data);
        });
    }

    public function connect(): void
    {
        $this->ws->connect();
    }

    public function getRest(): RestClient
    {
        return $this->rest;
    }

    private function handleEvent(array $data): void
    {
        $op = $data['op'];
        if ($op === 'ready') {
            $this->sessionId = $data['sessionId'];
            $this->emit('ready');
            return;
        }

        if ($op === 'stats') {
            $this->stats = $data;
            return;
        }

        if ($op === 'playerUpdate') {
            $guildId = $data['guildId'];
            if (isset($this->players[$guildId])) {
                $this->players[$guildId]->updateState($data['state']);
            }
            return;
        }

        if ($op === 'event') {
            $guildId = $data['guildId'];
            if (isset($this->players[$guildId])) {
                $this->players[$guildId]->handleEvent($data);
            }
        }
    }

    public function getPlayer(string $guildId): Player
    {
        if (!isset($this->players[$guildId])) {
            $this->players[$guildId] = $this->playerFactory->createPlayer($this, $guildId);
        }

        return $this->players[$guildId];
    }

    public function addPlayer(string $guildId, Player $player): void
    {
        $this->players[$guildId] = $player;
    }

    public function send(array $data): void
    {
        $this->ws->send($data);
    }

    public function updatePlayer(string $guildId, array $data): void
    {
        if (!$this->sessionId) {
            return;
        }

        $this->rest->updatePlayer($this->sessionId, $guildId, $data);
    }

    public function getPenalty(): int
    {
        if (empty($this->stats)) {
            return 0;
        }

        $penalties = 0;
        $stats = $this->stats;

        // Player penalty
        $penalties += $stats['playingPlayers'];

        // CPU penalty
        $penalties += (int) (pow(1.05, 100 * $stats['cpu']['systemLoad']) * 10 - 10);

        // Frame loss penalty
        if (isset($stats['frameStats'])) {
            $penalties += (int) ($stats['frameStats']['deficit'] * 1.03);
            $penalties += (int) ($stats['frameStats']['nulled'] * 1.03);
        }

        return $penalties;
    }
}
