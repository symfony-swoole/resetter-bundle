<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\DBAL\Connection\FailoverAware;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Result;
use Exception;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use SwooleBundle\ResetterBundle\DBAL\Connection\FailoverAware\ConnectionType;
use SwooleBundle\ResetterBundle\DBAL\Connection\FailoverAware\FailoverAwareDBALAliveKeeper;

final class FailoverAwareDBALAliveKeeperTest extends TestCase
{
    public function testKeepAliveWriterWithoutReconnect(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $statementMock = $this->createMock(Result::class);
        $statementMock->expects(self::atLeast(1))
            ->method('fetchOne')
            ->willReturn('0');

        /** @var Connection&MockObject $connectionMock */
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects(self::atLeast(1))
            ->method('executeQuery')
            ->withAnyParameters()
            ->willReturn($statementMock);
        $connectionMock->expects(self::exactly(0))->method('close');
        $connectionMock->expects(self::exactly(0))->method('getNativeConnection');

        $aliveKeeper = new FailoverAwareDBALAliveKeeper($loggerMock);
        $aliveKeeper->keepAlive($connectionMock, 'default');
    }

    public function testKeepAliveReaderWithoutReconnect(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $statementMock = $this->createMock(Result::class);
        $statementMock->expects(self::atLeast(1))
            ->method('fetchOne')
            ->willReturn('1');

        /** @var Connection&MockObject $connectionMock */
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects(self::atLeast(1))
            ->method('executeQuery')
            ->withAnyParameters()
            ->willReturn($statementMock);
        $connectionMock->expects(self::exactly(0))
            ->method('close');
        $connectionMock->expects(self::exactly(0))
            ->method('getNativeConnection');

        $aliveKeeper = new FailoverAwareDBALAliveKeeper($loggerMock, ConnectionType::READER);
        $aliveKeeper->keepAlive($connectionMock, 'default');
    }

    public function testKeepAliveWriterWithReconnectOnFailover(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects(self::atLeast(1))
            ->method('log')
            ->with(LogLevel::ALERT);
        $statementMock = $this->createMock(Result::class);
        $statementMock->expects(self::atLeast(1))
            ->method('fetchOne')
            ->willReturn('1');

        /** @var Connection&MockObject $connectionMock */
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects(self::atLeast(1))
            ->method('executeQuery')
            ->withAnyParameters()
            ->willReturn($statementMock);
        $connectionMock->expects(self::once())
            ->method('close');
        $connectionMock->expects(self::atLeast(1))
            ->method('getNativeConnection');

        $aliveKeeper = new FailoverAwareDBALAliveKeeper($loggerMock);
        $aliveKeeper->keepAlive($connectionMock, 'default');
    }

    /**
     * @throws Exception
     * @throws MockObjectException
     */
    public function testKeepAliveReaderWithReconnectOnFailover(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects(self::atLeast(1))
            ->method('log')
            ->with(LogLevel::WARNING);
        $statementMock = $this->createMock(Result::class);
        $statementMock->expects(self::atLeast(1))
            ->method('fetchOne')
            ->willReturn('0');

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects(self::atLeast(1))
            ->method('executeQuery')
            ->withAnyParameters()
            ->willReturn($statementMock);
        $connectionMock->expects(self::once())
            ->method('close');
        $connectionMock->expects(self::atLeast(1))
            ->method('getNativeConnection');

        $aliveKeeper = new FailoverAwareDBALAliveKeeper($loggerMock, ConnectionType::READER);
        $aliveKeeper->keepAlive($connectionMock, 'default');
    }

    public function testKeepAliveWithReconnectConnectionError(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects(self::atLeast(1))
            ->method('info')
            ->withAnyParameters();
        $statementMock = $this->createMock(Result::class);
        $statementMock->expects(self::atLeast(1))
            ->method('fetchOne')
            ->willThrowException($this->createMock(DriverException::class));

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects(self::atLeast(1))
            ->method('executeQuery')
            ->withAnyParameters()
            ->willReturn($statementMock);
        $connectionMock->expects(self::once())
            ->method('close');
        $connectionMock->expects(self::atLeast(1))
            ->method('getNativeConnection');

        $aliveKeeper = new FailoverAwareDBALAliveKeeper($loggerMock);
        $aliveKeeper->keepAlive($connectionMock, 'default');
    }
}
