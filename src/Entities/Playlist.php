<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\Entities;

class Playlist
{
    /**
     * @param Track[] $tracks
     */
    public function __construct(
        public readonly string $name,
        public readonly int $selectedTrack,
        public readonly array $tracks,
    ) {}
}
