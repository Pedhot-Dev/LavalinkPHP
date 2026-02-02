<?php

declare(strict_types=1);

namespace Tests\Unit;

use PedhotDev\LavalinkPHP\Http\RestClient;
use PedhotDev\LavalinkPHP\Entities\Track;
use PedhotDev\LavalinkPHP\Entities\Playlist;
use React\Http\Browser;
use React\Http\Message\Response;
use Mockery;

beforeEach(function () {
    $this->browser = Mockery::mock(Browser::class);
    $this->browser->shouldReceive('withHeader')->andReturnSelf();
    $this->browser->shouldReceive('withBase')->andReturnSelf();
    
    $this->rest = new RestClient('http://localhost:2333', 'youshallnotpass', $this->browser);
});

test('it can load a single track', function () {
    $responseBody = json_encode([
        'loadType' => 'track',
        'data' => [
            'encoded' => 'encoded-track',
            'info' => [
                'identifier' => 'abc',
                'isSeekable' => true,
                'author' => 'Author',
                'length' => 1000,
                'isStream' => false,
                'position' => 0,
                'title' => 'Title',
                'uri' => 'https://example.com',
                'sourceName' => 'youtube'
            ]
        ]
    ]);
    
    $promise = \React\Promise\resolve(new Response(200, [], $responseBody));
    $this->browser->shouldReceive('get')->once()->andReturn($promise);
    
    $track = null;
    $this->rest->loadTracks('abc')->then(function ($result) use (&$track) {
        $track = $result;
    });
    
    expect($track)->toBeInstanceOf(Track::class);
    expect($track->info->title)->toBe('Title');
});

test('it can load a playlist', function () {
    $responseBody = json_encode([
        'loadType' => 'playlist',
        'data' => [
            'info' => ['name' => 'My Playlist', 'selectedTrack' => 0],
            'tracks' => [
                [
                    'encoded' => 'encoded-track',
                    'info' => [
                        'identifier' => 'abc',
                        'isSeekable' => true,
                        'author' => 'Author',
                        'length' => 1000,
                        'isStream' => false,
                        'position' => 0,
                        'title' => 'Title',
                        'uri' => 'https://example.com',
                        'sourceName' => 'youtube'
                    ]
                ]
            ]
        ]
    ]);
    
    $promise = \React\Promise\resolve(new Response(200, [], $responseBody));
    $this->browser->shouldReceive('get')->once()->andReturn($promise);
    
    $playlist = null;
    $this->rest->loadTracks('abc')->then(function ($result) use (&$playlist) {
        $playlist = $result;
    });
    
    expect($playlist)->toBeInstanceOf(Playlist::class);
    expect($playlist->name)->toBe('My Playlist');
    expect($playlist->tracks)->toHaveCount(1);
    expect($playlist->tracks[0])->toBeInstanceOf(Track::class);
});

test('it can decode a track', function () {
    $responseBody = json_encode([
        'encoded' => 'encoded-track',
        'info' => [
            'identifier' => 'abc',
            'isSeekable' => true,
            'author' => 'Author',
            'length' => 1000,
            'isStream' => false,
            'position' => 0,
            'title' => 'Title',
            'uri' => 'https://example.com',
            'sourceName' => 'youtube'
        ]
    ]);
    
    $promise = \React\Promise\resolve(new Response(200, [], $responseBody));
    $this->browser->shouldReceive('get')->once()->andReturn($promise);
    
    $track = null;
    $this->rest->decodeTrack('encoded-track')->then(function ($result) use (&$track) {
        $track = $result;
    });
    
    expect($track)->toBeInstanceOf(Track::class);
});
