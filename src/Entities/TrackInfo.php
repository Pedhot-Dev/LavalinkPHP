<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\Entities;

class TrackInfo
{
    public function __construct(
        public readonly string $identifier,
        public readonly bool $isSeekable,
        public readonly string $author,
        public readonly int $length,
        public readonly bool $isStream,
        public readonly int $position,
        public readonly string $title,
        public readonly ?string $uri,
        public readonly string $sourceName,
        public readonly ?string $artworkUrl,
        public readonly ?string $isrc,
    ) {}
}
