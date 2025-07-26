<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Redis\Cluster\Connection;

use Override;
use RedisCluster;
use RuntimeException;
use SwooleBundle\ResetterBundle\Connection\PlatformAliveKeeper as GenericPlatformAliveKeeper;

final class RedisClusterPlatformAliveKeeper implements GenericPlatformAliveKeeper
{
    /**
     * @param array<string, RedisCluster> $connections
     * @param array<string, RedisClusterAliveKeeper> $aliveKeepers
     */
    public function __construct(
        private array $connections,
        private readonly array $aliveKeepers,
    ) {}

    #[Override]
    public function keepAlive(): void
    {
        foreach ($this->aliveKeepers as $connectionName => $aliveKeeper) {
            if (!isset($this->connections[$connectionName])) {
                throw new RuntimeException(
                    sprintf('Connection "%s" is missing.', $connectionName),
                );
            }

            $connection = $this->connections[$connectionName];
            $aliveKeeper->keepAlive($connection, $connectionName);
        }
    }
}
