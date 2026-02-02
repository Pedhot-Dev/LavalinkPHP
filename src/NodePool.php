<?php

declare(strict_types=1);

namespace PedhotDev\LavalinkPHP;

use PedhotDev\LavalinkPHP\Factory\NodeFactory;

class NodePool
{
    /** @var Node[] */
    private array $nodes = [];

    public function __construct(
        private readonly NodeFactory $nodeFactory
    ) {}

    public function createNode(\PedhotDev\LavalinkPHP\Config\NodeConfiguration $config, string $userId, \React\EventLoop\LoopInterface $loop): Node
    {
        $node = $this->nodeFactory->createNode($config, $userId, $loop);
        $this->addNode($node);
        return $node;
    }

    public function addNode(Node $node): void
    {
        $this->nodes[$node->identifier] = $node;
    }

    public function getBestNode(): Node
    {
        if (empty($this->nodes)) {
            throw new \RuntimeException("No nodes available in pool.");
        }

        $best = null;
        $minPenalty = PHP_INT_MAX;

        foreach ($this->nodes as $node) {
            $penalty = $node->getPenalty();
            if ($penalty < $minPenalty) {
                $minPenalty = $penalty;
                $best = $node;
            }
        }

        return $best;
    }

    public function getNode(string $identifier): ?Node
    {
        return $this->nodes[$identifier] ?? null;
    }

    /** @return Node[] */
    public function getNodes(): array
    {
        return $this->nodes;
    }
}
