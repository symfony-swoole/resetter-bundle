<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Redis\Cluster\Connection;

use Override;
use RedisCluster;
use Symfony\Component\VarExporter\LazyObjectInterface;

final readonly class PassiveIgnoringRedisClusterAliveKeeper implements RedisClusterAliveKeeper
{
    public function __construct(
        private RedisClusterAliveKeeper $decorated,
    ) {}

    #[Override]
    public function keepAlive(RedisCluster $redis, string $connectionName): void
    {
        if ($redis instanceof LazyObjectInterface && !$redis->isLazyObjectInitialized()) {
            return;
        }

        $this->decorated->keepAlive($redis, $connectionName);
    }
}
