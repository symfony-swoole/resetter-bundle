<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\Redis\Cluster\Connection;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RedisCluster;
use SwooleBundle\ResetterBundle\Redis\Cluster\Connection\PingingRedisClusterAliveKeeper;

final class PingingRedisClusterAliveKeeperTest extends TestCase
{
    public function testKeepAliveWriterWithoutReconnect(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $clusterMock = $this->createMock(RedisCluster::class);
        $clusterMock->expects(self::atLeast(1))
            ->method('ping')
            ->with('hello')
            ->willReturn('hello');
        $aliveKeeper = new PingingRedisClusterAliveKeeper([], $loggerMock);
        $aliveKeeper->keepAlive($clusterMock, 'default');
    }

    public function testKeepAliveWithReconnectOnFailedPing(): void
    {
        $constructorParameters = [
            'session',
            ['localhost:6379'],
            2,
            2,
        ];

        $clusterSpy = new RedisClusterSpy(...$constructorParameters);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects(self::atLeast(1))
            ->method('info')
            ->with("Exceptional reconnect for redis cluster connection 'default'");

        $aliveKeeper = new PingingRedisClusterAliveKeeper($constructorParameters, $loggerMock);
        $aliveKeeper->keepAlive($clusterSpy, 'default');

        self::assertTrue($clusterSpy->wasConstructorCalled());
        self::assertSame($constructorParameters, $clusterSpy->getConstructorParametersSecond());
    }
}
