<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\Redis\Cluster\Connection;

use PHPUnit\Framework\TestCase;
use RedisCluster;
use SwooleBundle\ResetterBundle\Redis\Cluster\Connection\OptimizedRedisClusterAliveKeeper;
use SwooleBundle\ResetterBundle\Redis\Cluster\Connection\RedisClusterAliveKeeper;
use Symfony\Bridge\PhpUnit\ClockMock;

final class OptimizedRedisClusterAliveKeeperTest extends TestCase
{
    /**
     * @group time-sensitive
     */
    public function testKeepAliveEachXSeconds(): void
    {
        ClockMock::register(OptimizedRedisClusterAliveKeeper::class);

        $connectionMock = $this->createMock(RedisCluster::class);
        $connectionName = 'default';
        $decoratedAliveKeepr = $this->createMock(RedisClusterAliveKeeper::class);
        $decoratedAliveKeepr->expects(self::once())
            ->method('keepAlive')
            ->with($connectionMock, $connectionName);

        $aliveKeeper = new OptimizedRedisClusterAliveKeeper($decoratedAliveKeepr, 3);
        $aliveKeeper->keepAlive($connectionMock, $connectionName);
        sleep(2);
        $aliveKeeper->keepAlive($connectionMock, $connectionName);
    }
}
