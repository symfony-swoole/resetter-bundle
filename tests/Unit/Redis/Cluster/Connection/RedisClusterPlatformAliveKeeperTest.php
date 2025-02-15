<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\Redis\Cluster\Connection;

use PHPUnit\Framework\TestCase;
use RedisCluster;
use SwooleBundle\ResetterBundle\Redis\Cluster\Connection\RedisClusterAliveKeeper;
use SwooleBundle\ResetterBundle\Redis\Cluster\Connection\RedisClusterPlatformAliveKeeper;

final class RedisClusterPlatformAliveKeeperTest extends TestCase
{
    public function testKeepAlive(): void
    {
        $cName1 = 'default';
        $cMock1 = $this->createMock(RedisCluster::class);
        $cName2 = 'other';
        $cMock2 = $this->createMock(RedisCluster::class);

        $keeper1 = $this->createMock(RedisClusterAliveKeeper::class);
        $keeper1->expects(self::once())
            ->method('keepAlive')
            ->with($cMock1, $cName1);
        $keeper2 = $this->createMock(RedisClusterAliveKeeper::class);
        $keeper2->method('keepAlive')
            ->with($cMock2, $cName2);

        $platformKeeper = new RedisClusterPlatformAliveKeeper(
            [
                $cName1 => $cMock1,
                $cName2 => $cMock2,
            ],
            [
                $cName1 => $keeper1,
                $cName2 => $keeper2,
            ]
        );
        $platformKeeper->keepAlive();
    }
}
