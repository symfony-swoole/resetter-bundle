<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Redis\Cluster\Connection;

use ProxyManager\Proxy\VirtualProxyInterface;
use RedisCluster;

final class PassiveIgnoringRedisClusterAliveKeeper implements RedisClusterAliveKeeper
{
    public function __construct(
        private readonly RedisClusterAliveKeeper $decorated,
    ) {}

    public function keepAlive(RedisCluster $redis, string $connectionName): void
    {
        if ($redis instanceof VirtualProxyInterface && !$redis->isProxyInitialized()) {
            return;
        }

        $this->decorated->keepAlive($redis, $connectionName);
    }
}
