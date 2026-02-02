<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use PedhotDev\LavalinkPHP\Container\ContainerFactory;
use PedhotDev\LavalinkPHP\NodePool;
use PedhotDev\LavalinkPHP\Config\NodeConfiguration;
use PedhotDev\LavalinkPHP\Entities\Track;
use React\EventLoop\LoopInterface;

// 1. Setup DiscordPHP
$discord = new Discord([
    'token' => 'YOUR_KEY',
    'intents' => Intents::GUILDS | Intents::GUILD_VOICE_STATES | Intents::GUILD_MESSAGES | Intents::MESSAGE_CONTENT,
]);

$discord->on('ready', function (Discord $discord) {
    echo "Bot is ready!", PHP_EOL;

    // 2. Bootstrap the DI Container
    $container = ContainerFactory::create();
    
    // Bind the Discord loop to the container so library services can use it
    $containerBuilder = \PedhotDev\NepotismFree\NepotismFree::createBuilder();
    $containerBuilder->addModule(new \PedhotDev\LavalinkPHP\Container\LavalinkModule());
    $containerBuilder->bindParameterObject(LoopInterface::class, $discord->getLoop());
    $container = $containerBuilder->build();

    /** @var NodePool $pool */
    $pool = $container->get(NodePool::class);

    // 3. Configure and create a Node via the Pool (which uses NodeFactory)
    $config = new NodeConfiguration(
        identifier: 'main',
        host: 'lavalink.jirayu.net',
        port: 13592,
        password: 'youshallnotpass'
    );

    $node = $pool->createNode($config, $discord->id, $discord->getLoop());

    $node->on('ready', function () {
        echo "Lavalink connected!", PHP_EOL;
    });

    $node->on('error', function ($e) {
        echo "Lavalink error: ", $e->getMessage(), PHP_EOL;
    });

    $node->connect();

    // 4. Handle commands
    $discord->on(Event::MESSAGE_CREATE, function (\Discord\Parts\Channel\Message $message) use ($node) {
        if ($message->author->bot) return;

        $content = $message->content;
        if (str_starts_with($content, '!play ')) {
            $query = substr($content, 6);

            if (($member = $message->member) == null) return;
            if (($channel = $member->getVoiceChannel()) == null) {
                $message->reply("You must be in a voice channel!");
                return;
            }

            $channelId = $channel->id;

            $node->getRest()->loadTracks("ytsearch:{$query}")->then(function ($result) use ($node, $message, $channelId) {
                if (empty($result)) {
                    $message->reply("No tracks found.");
                    return;
                }

                $track = is_array($result) ? $result[0] : $result;
                if ($track instanceof \PedhotDev\LavalinkPHP\Entities\Playlist) {
                    $track = $track->tracks[0];
                }

                /** @var \PedhotDev\LavalinkPHP\Player $player */
                $player = $node->getPlayer($message->guild_id);

                $message->reply("Playing: " . $track->info->title);
                
                // Set up listeners for voice updates
                $message->discord->on(Event::VOICE_STATE_UPDATE, function ($state) use ($player, $message) {
                    if ($state->user_id === $message->discord->id && $state->guild_id === $message->guild_id) {
                        $GLOBALS['session_id_' . $message->guild_id] = $state->session_id;
                        checkAndNotifyLavalink($message->guild_id, $player);
                    }
                });

                $message->discord->on(Event::VOICE_SERVER_UPDATE, function ($server) use ($player, $message) {
                    if ($server->guild_id === $message->guild_id) {
                        $GLOBALS['token_' . $message->guild_id] = $server->token;
                        $GLOBALS['endpoint_' . $message->guild_id] = $server->endpoint;
                        checkAndNotifyLavalink($message->guild_id, $player);
                    }
                });

                // Join and Play
                if ($channel = $message->discord->getChannel($channelId)) {
                    $message->discord->joinVoiceChannel($channel)->then(function () use ($player, $track) {
                        $player->play($track);
                    });
                } else {
                    $message->reply("Could not find voice channel.");
                }
            });
        }
    });
});

function checkAndNotifyLavalink($guildId, $player) {
    $sessionId = $GLOBALS['session_id_' . $guildId] ?? null;
    $token = $GLOBALS['token_' . $guildId] ?? null;
    $endpoint = $GLOBALS['endpoint_' . $guildId] ?? null;

    if ($sessionId && $token && $endpoint) {
        $player->provideVoiceUpdate($sessionId, $token, $endpoint);
        unset($GLOBALS['session_id_' . $guildId], $GLOBALS['token_' . $guildId], $GLOBALS['endpoint_' . $guildId]);
    }
}

$discord->run();
