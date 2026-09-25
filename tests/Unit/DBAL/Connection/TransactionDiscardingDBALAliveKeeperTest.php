<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\DBAL\Connection;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SwooleBundle\ResetterBundle\DBAL\Connection\DBALAliveKeeper;
use SwooleBundle\ResetterBundle\DBAL\Connection\TransactionDiscardingDBALAliveKeeper;
use SwooleBundle\ResetterBundle\Tests\Unit\Helper\ProxyConnectionMock;
use SwooleBundle\ResetterBundle\Tests\Unit\Helper\RecordingLogger;

final class TransactionDiscardingDBALAliveKeeperTest extends TestCase
{
    private const string NEEDED_TO_DISCARD
        = 'Connection "default" needed to discard active transaction while running keep-alive routine.';

    private const string ERROR_WHILE_DISCARDING
        = 'An error occurred while discarding active transaction in connection "default".';

    private const string CLOSED
        = 'Connection "default" still believed it was in a transaction after discarding it, so it was closed.';

    /**
     * Every nesting level is rolled back, not only the innermost one.
     */
    public function testEveryLevelOfAnActiveTransactionIsRolledBack(): void
    {
        $logger = new RecordingLogger();
        $connection = $this->createMock(ProxyConnectionMock::class);
        $connection->method('isTransactionActive')->willReturn(true);
        $connection->method('isAutoCommit')->willReturn(true);
        $connection->method('getTransactionNestingLevel')->willReturnOnConsecutiveCalls(2, 0);
        $connection->expects($this->exactly(2))->method('rollBack');
        $connection->expects($this->never())->method('close');

        $this->keeper($logger, $connection)->keepAlive($connection, 'default');

        self::assertSame([self::NEEDED_TO_DISCARD], $logger->messages());
    }

    /**
     * After a deadlock inside a nested transaction the server has rolled everything back and every
     * rollback is refused, so the connection is closed: that is what makes it forget its nesting level.
     */
    public function testAConnectionThatCannotRollBackIsClosed(): void
    {
        $failure = new RuntimeException('SAVEPOINT DOCTRINE_2 does not exist');
        $logger = new RecordingLogger();
        $connection = $this->createMock(ProxyConnectionMock::class);
        $connection->method('isTransactionActive')->willReturn(true);
        $connection->method('isAutoCommit')->willReturn(true);
        $connection->method('getTransactionNestingLevel')->willReturn(2);
        // Refused while the lost transaction is still believed in; the one clearing rollback-only after the
        // close runs on a new connection and succeeds.
        $rollbacks = $this->exactly(2);
        $connection->expects($rollbacks)->method('rollBack')->willReturnCallback(
            static function () use ($rollbacks, $failure): void {
                if ($rollbacks->numberOfInvocations() === 1) {
                    throw $failure;
                }
            },
        );
        $connection->expects($this->once())->method('close');
        $connection->expects($this->once())->method('beginTransaction');

        $this->keeper($logger, $connection)->keepAlive($connection, 'default');

        self::assertSame([self::NEEDED_TO_DISCARD, self::ERROR_WHILE_DISCARDING, self::CLOSED], $logger->messages());
        self::assertSame(['exception' => $failure], $logger->contextOf(1));
    }

    public function testAConnectionStillInATransactionAfterItsRollbacksIsClosed(): void
    {
        $logger = new RecordingLogger();
        $connection = $this->createMock(ProxyConnectionMock::class);
        $connection->method('isTransactionActive')->willReturn(true);
        $connection->method('isAutoCommit')->willReturn(true);
        $connection->method('getTransactionNestingLevel')->willReturn(1);
        $connection->expects($this->exactly(2))->method('rollBack');
        $connection->expects($this->once())->method('close');

        $this->keeper($logger, $connection)->keepAlive($connection, 'default');

        self::assertSame([self::NEEDED_TO_DISCARD, self::CLOSED], $logger->messages());
    }

    /**
     * With auto-commit off DBAL opens a new transaction as soon as the last one is rolled back: one level
     * left after the discard is the connection working as configured, not one to close.
     */
    public function testWithoutAutoCommitTheTransactionDbalReopensIsLeftOpen(): void
    {
        $logger = new RecordingLogger();
        $connection = $this->createMock(ProxyConnectionMock::class);
        $connection->method('isTransactionActive')->willReturn(true);
        $connection->method('isAutoCommit')->willReturn(false);
        $connection->method('getTransactionNestingLevel')->willReturnOnConsecutiveCalls(2, 1);
        $connection->expects($this->exactly(2))->method('rollBack');
        $connection->expects($this->never())->method('close');

        $this->keeper($logger, $connection)->keepAlive($connection, 'default');

        self::assertSame([self::NEEDED_TO_DISCARD], $logger->messages());
    }

    public function testAConnectionOutsideATransactionIsLeftAlone(): void
    {
        $logger = new RecordingLogger();
        $connection = $this->createMock(ProxyConnectionMock::class);
        $connection->method('isTransactionActive')->willReturn(false);
        $connection->expects($this->never())->method('rollBack');
        $connection->expects($this->never())->method('close');

        $this->keeper($logger, $connection)->keepAlive($connection, 'default');

        self::assertSame([], $logger->messages());
    }

    private function keeper(
        RecordingLogger $logger,
        ProxyConnectionMock $connection,
    ): TransactionDiscardingDBALAliveKeeper {
        $decorated = $this->createMock(DBALAliveKeeper::class);
        $decorated->expects($this->once())->method('keepAlive')->with($connection, 'default');

        return new TransactionDiscardingDBALAliveKeeper($decorated, $logger);
    }
}
