<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP;

class Filters
{
    private array $data = [];

    public function setEqualizer(array $gains): self
    {
        $this->data['equalizer'] = array_map(fn($band, $gain) => ['band' => $band, 'gain' => $gain], array_keys($gains), $gains);
        return $this;
    }

    public function setTimescale(?float $speed = null, ?float $pitch = null, ?float $rate = null): self
    {
        $this->data['timescale'] = array_filter([
            'speed' => $speed,
            'pitch' => $pitch,
            'rate' => $rate
        ], fn($v) => $v !== null);
        return $this;
    }

    public function setKaraoke(?float $level = null, ?float $monoLevel = null, ?float $filterBand = null, ?float $filterWidth = null): self
    {
        $this->data['karaoke'] = array_filter([
            'level' => $level,
            'monoLevel' => $monoLevel,
            'filterBand' => $filterBand,
            'filterWidth' => $filterWidth
        ], fn($v) => $v !== null);
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
