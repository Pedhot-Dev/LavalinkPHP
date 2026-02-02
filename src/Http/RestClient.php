<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP\Http;

use React\Http\Browser;
use React\Promise\PromiseInterface;
use PedhotDev\LavalinkPHP\Entities\Playlist;
use PedhotDev\LavalinkPHP\Entities\Track;
use PedhotDev\LavalinkPHP\Entities\TrackInfo;

class RestClient
{
    public function __construct(
        private readonly string $uri,
        private readonly string $password,
        private ?Browser $browser = null
    ) {
        $this->browser = ($this->browser ?? new Browser())
            ->withHeader('Authorization', $this->password)
            ->withBase($this->uri);
    }

    public function loadTracks(string $identifier): PromiseInterface
    {
        return $this->browser->get("/v4/loadtracks?identifier=" . urlencode($identifier))
            ->then(function ($response) {
                $data = json_decode((string) $response->getBody(), true);
                return $this->mapLoadResult($data);
            });
    }

    private function mapLoadResult(array $data): mixed
    {
        $loadType = $data['loadType'];
        $data = $data['data'];

        return match ($loadType) {
            'track' => $this->mapTrack($data),
            'playlist' => new Playlist(
                $data['info']['name'],
                $data['info']['selectedTrack'],
                array_map([$this, 'mapTrack'], $data['tracks'])
            ),
            'search' => array_map([$this, 'mapTrack'], $data),
            'empty' => [],
            'error' => throw new \Exception("Lavalink error: " . ($data['message'] ?? 'Unknown error')),
            default => throw new \Exception("Unknown loadType: $loadType"),
        };
    }

    private function mapTrack(array $data): Track
    {
        return new Track(
            $data['encoded'],
            new TrackInfo(
                $data['info']['identifier'],
                $data['info']['isSeekable'],
                $data['info']['author'],
                $data['info']['length'],
                $data['info']['isStream'],
                $data['info']['position'],
                $data['info']['title'],
                $data['info']['uri'] ?? null,
                $data['info']['sourceName'],
                $data['info']['artworkUrl'] ?? null,
                $data['info']['isrc'] ?? null,
            ),
            $data['userData'] ?? null
        );
    }

    public function updatePlayer(string $sessionId, string $guildId, array $data): PromiseInterface
    {
        return $this->browser->patch(
            "/v4/sessions/" . urlencode($sessionId) . "/players/" . urlencode($guildId),
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
    }

    public function decodeTrack(string $encoded): PromiseInterface
    {
        return $this->browser->get("/v4/decodetrack?encodedTrack=" . urlencode($encoded))
            ->then(function ($response) {
                $data = json_decode((string) $response->getBody(), true);
                return $this->mapTrack($data);
            });
    }
}
