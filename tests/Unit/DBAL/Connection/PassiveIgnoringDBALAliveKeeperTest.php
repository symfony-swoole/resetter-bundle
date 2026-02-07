<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\DBAL\Connection;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SwooleBundle\ResetterBundle\DBAL\Connection\DBALAliveKeeper;
use SwooleBundle\ResetterBundle\DBAL\Connection\PassiveIgnoringDBALAliveKeeper;
use SwooleBundle\ResetterBundle\Tests\Unit\Helper\ProxyConnectionMock;

final class PassiveIgnoringDBALAliveKeeperTest extends TestCase
{
    public function testKeepAliveWithoutInitialisedConnectionProxyDoesNotDoAnything(): void
    {
        /** @var Connection&MockObject $connectionMock */
        $connectionMock = $this->createMock(ProxyConnectionMock::class);
        $connectionMock->expects(self::exactly(0))
            ->method('getDatabasePlatform');
        $connectionName = 'default';

        $decoratedAliveKeeper = $this->createMock(DBALAliveKeeper::class);
        $decoratedAliveKeeper->expects(self::exactly(0))
            ->method('keepAlive')
            ->with($connectionMock, $connectionName);

        $aliveKeeper = new PassiveIgnoringDBALAliveKeeper($decoratedAliveKeeper);
        $aliveKeeper->keepAlive($connectionMock, $connectionName);
    }

    public function testKeepAliveWithoutInitialisedConnectionDoesNotDoAnything(): void
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects(self::atLeast(1))
            ->method('isConnected')
            ->willReturn(false);
        $connectionMock->expects(self::exactly(0))
            ->method('getDatabasePlatform');
        $connectionName = 'default';

        $decoratedAliveKeeper = $this->createMock(DBALAliveKeeper::class);
        $decoratedAliveKeeper->expects(self::exactly(0))
            ->method('keepAlive')
            ->with($connectionMock, $connectionName);

        $aliveKeeper = new PassiveIgnoringDBALAliveKeeper($decoratedAliveKeeper);
        $aliveKeeper->keepAlive($connectionMock, $connectionName);
    }

    public function testKeepAliveWithInitialisedConnectionDelegatesControl(): void
    {
        /** @var Connection&MockObject $connectionMock */
        $connectionMock = $this->createMock(ProxyConnectionMock::class);
        $connectionMock->expects(self::atLeast(1))
            ->method('isConnected')
            ->willReturn(true);
        $connectionMock->expects(self::exactly(0))
            ->method('getDatabasePlatform');
        $connectionName = 'default';

        $decoratedAliveKeeper = $this->createMock(DBALAliveKeeper::class);
        $decoratedAliveKeeper->expects(self::atLeast(1))
            ->method('keepAlive')
            ->with($connectionMock, $connectionName);

        $aliveKeeper = new PassiveIgnoringDBALAliveKeeper($decoratedAliveKeeper);
        $aliveKeeper->keepAlive($connectionMock, $connectionName);
    }
}
