<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\Entities;

class Track
{
    public function __construct(
        public readonly string $encoded,
        public readonly TrackInfo $info,
        public readonly ?array $userData = null,
    ) {}
}
