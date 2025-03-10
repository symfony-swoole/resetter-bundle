<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Redis\Cluster\Connection;

use Psr\Log\LoggerInterface;
use RedisCluster;
use RedisClusterException;

final class PingingRedisClusterAliveKeeper implements RedisClusterAliveKeeper
{
    /**
     * @param array{
     *   0: string|null,
     *   1: array<string>,
     *   2: float,
     *   3: float,
     *   4: bool,
     *   5: string|null
     * } $constructorArguments
     */
    public function __construct(
        private readonly array $constructorArguments,
        private readonly LoggerInterface $logger,
    ) {}

    public function keepAlive(RedisCluster $redis, string $connectionName): void
    {
        try {
            $redis->ping('hello');
        } catch (RedisClusterException $e) {
            $this->logger->info(
                sprintf("Exceptional reconnect for redis cluster connection '%s'", $connectionName),
                ['exception' => $e],
            );
            // redis cluster does not have a reconnect method and does not work with shard master to slave failover,
            // so this hack has to be used
            call_user_func_array([$redis, '__construct'], $this->constructorArguments);
        }
    }
}
