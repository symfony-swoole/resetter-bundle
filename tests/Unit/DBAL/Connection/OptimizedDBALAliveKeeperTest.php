<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\DBAL\Connection;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwooleBundle\ResetterBundle\DBAL\Connection\DBALAliveKeeper;
use SwooleBundle\ResetterBundle\DBAL\Connection\OptimizedDBALAliveKeeper;
use Symfony\Bridge\PhpUnit\ClockMock;

final class OptimizedDBALAliveKeeperTest extends TestCase
{
    /**
     * @group time-sensitive
     */
    public function testKeepAliveEachXSeconds(): void
    {
        ClockMock::register(OptimizedDBALAliveKeeper::class);

        $connectionMock = $this->createStub(Connection::class);
        $connectionName = 'default';
        $decoratedAliveKeepr = $this->createMock(DBALAliveKeeper::class);
        $decoratedAliveKeepr->expects($this->once())
            ->method('keepAlive')
            ->with($connectionMock, $connectionName);

        $aliveKeeper = new OptimizedDBALAliveKeeper($decoratedAliveKeepr, 3);
        $aliveKeeper->keepAlive($connectionMock, $connectionName);
        sleep(2);
        $aliveKeeper->keepAlive($connectionMock, $connectionName);
    }
}
