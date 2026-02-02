<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP;

use Evenement\EventEmitter;
use PedhotDev\LavalinkPHP\Entities\Track;

class Player extends EventEmitter
{
    private string $voiceToken;
    private string $voiceEndpoint;
    private string $sessionId;
    private bool $playing = false;
    private bool $paused = false;
    private int $position = 0;
    private int $volume = 100;
    private ?Track $track = null;
    private ?Filters $filters = null;

    public function __construct(
        protected readonly Node $node,
        public readonly string $guildId
    ) {
        $this->node->addPlayer($this->guildId, $this);
    }

    public function play(Track $track, bool $noReplace = false): void
    {
        $this->node->updatePlayer($this->guildId, [
            'track' => [
                'encoded' => $track->encoded,
            ],
            'noReplace' => $noReplace
        ]);
        $this->track = $track;
        $this->playing = true;
    }

    public function stop(): void
    {
        $this->node->updatePlayer($this->guildId, [
            'track' => [
                'encoded' => null,
            ]
        ]);
        $this->playing = false;
        $this->track = null;
    }

    public function setPaused(bool $paused): void
    {
        $this->node->updatePlayer($this->guildId, [
            'paused' => $paused
        ]);
        $this->paused = $paused;
    }

    public function seek(int $position): void
    {
        $this->node->updatePlayer($this->guildId, [
            'position' => $position
        ]);
    }

    public function setVolume(int $volume): void
    {
        $this->node->updatePlayer($this->guildId, [
            'volume' => $volume
        ]);
        $this->volume = $volume;
    }

    public function setFilters(Filters $filters): void
    {
        $this->node->updatePlayer($this->guildId, [
            'filters' => $filters->toArray()
        ]);
        $this->filters = $filters;
    }

    public function provideVoiceUpdate(string $sessionId, string $token, string $endpoint): void
    {
        $this->sessionId = $sessionId;
        $this->voiceToken = $token;
        $this->voiceEndpoint = $endpoint;

        $this->node->updatePlayer($this->guildId, [
            'voice' => [
                'token' => $token,
                'endpoint' => $endpoint,
                'sessionId' => $sessionId
            ]
        ]);
    }

    public function updateState(array $state): void
    {
        $this->position = $state['position'];
        $this->playing = $state['connected'];
        $this->emit('update', [$state]);
    }

    public function handleEvent(array $data): void
    {
        $type = $data['type'];
        $this->emit($type, [$data]);

        if ($type === 'TrackStartEvent') {
            $this->playing = true;
        } elseif ($type === 'TrackEndEvent') {
            $this->playing = false;
        }
    }

    public function isPlaying(): bool
    {
        return $this->playing;
    }

    public function isPaused(): bool
    {
        return $this->paused;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getVolume(): int
    {
        return $this->volume;
    }

    public function getTrack(): ?Track
    {
        return $this->track;
    }
}
