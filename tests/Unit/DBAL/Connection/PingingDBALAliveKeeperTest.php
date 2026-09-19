<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\DBAL\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionLost;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use ErrorException;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SwooleBundle\ResetterBundle\DBAL\Connection\PingingDBALAliveKeeper;

final class PingingDBALAliveKeeperTest extends TestCase
{
    /**
     * @throws Exception
     * @throws MockObjectException
     */
    public function testKeepAliveWithoutReconnect(): void
    {
        $query = 'SELECT 1';
        $platformMock = $this->createMock(AbstractPlatform::class);
        $platformMock->expects($this->atLeast(1))
            ->method('getDummySelectSQL')
            ->willReturn($query);
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects($this->atLeast(1))
            ->method('getDatabasePlatform')
            ->willReturn($platformMock);
        $connectionMock->expects($this->atLeast(1))
            ->method('executeQuery')
            ->with($query);
        $connectionMock->expects($this->exactly(0))
            ->method('close');
        $connectionMock->expects($this->exactly(0))
            ->method('getNativeConnection');

        $aliveKeeper = new PingingDBALAliveKeeper();
        $aliveKeeper->keepAlive($connectionMock, 'default');
    }

    /**
     * @throws Exception
     * @throws MockObjectException
     */
    public function testKeepAliveWithReconnectOnFailedPing(): void
    {
        $query = 'SELECT 1';
        $platformMock = $this->createMock(AbstractPlatform::class);
        $platformMock->expects($this->atLeast(1))
            ->method('getDummySelectSQL')
            ->willReturn($query);
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->expects($this->atLeast(1))
            ->method('getDatabasePlatform')
            ->willReturn($platformMock);
        $connLostRefl = new ReflectionClass(ConnectionLost::class);
        $connectionMock->expects($this->once())
            ->method('executeQuery')
            ->with($query)
            ->willThrowException($connLostRefl->newInstanceWithoutConstructor());
        $connectionMock->expects($this->atLeast(1))
            ->method('close');
        $connectionMock->expects($this->atLeast(1))
            ->method('getNativeConnection')
            ->willReturn(true);

        $aliveKeeper = new PingingDBALAliveKeeper();
        $aliveKeeper->keepAlive($connectionMock, 'default');
    }

    /**
     * What a dead MySQL connection looks like to an application that turns PHP errors into exceptions, as
     * Symfony does in debug: the driver raises a notice about the write it could not make before it throws
     * the error DBAL reads as a lost connection. The notice used to escape as an ErrorException, so the ping
     * failed instead of reconnecting.
     *
     * @throws Exception
     * @throws MockObjectException
     */
    public function testKeepAliveReconnectsWhenTheDeadConnectionRaisesANoticeFirst(): void
    {
        $connectionMock = $this->connectionPinging();
        $connLostRefl = new ReflectionClass(ConnectionLost::class);
        $connectionMock->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(static function () use ($connLostRefl): never {
                trigger_error('PDO::query(): Send of 13 bytes failed with errno=32 Broken pipe', E_USER_NOTICE);

                throw $connLostRefl->newInstanceWithoutConstructor();
            });
        $connectionMock->expects($this->once())
            ->method('close');
        $connectionMock->expects($this->once())
            ->method('getNativeConnection')
            ->willReturn(true);

        $this->withThrowingErrorHandler(static function () use ($connectionMock): void {
            (new PingingDBALAliveKeeper())->keepAlive($connectionMock, 'default');
        });
    }

    /**
     * The ping only sets aside what a dead connection says. Under coroutines another coroutine can raise an
     * error while the ping's handler is in place, and that still has to reach the application.
     *
     * @throws Exception
     * @throws MockObjectException
     */
    public function testAnyOtherErrorDuringThePingStillReachesTheApplication(): void
    {
        $connectionMock = $this->connectionPinging();
        $connectionMock->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(static function (): never {
                trigger_error('Undefined variable $somethingElse', E_USER_WARNING);

                throw new Exception('not reached');
            });
        $connectionMock->expects($this->never())
            ->method('close');

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Undefined variable $somethingElse');

        $this->withThrowingErrorHandler(static function () use ($connectionMock): void {
            (new PingingDBALAliveKeeper())->keepAlive($connectionMock, 'default');
        });
    }

    /**
     * The handler is the ping's for the length of the query and nobody's after it, whichever way the query
     * ended.
     *
     * @throws Exception
     * @throws MockObjectException
     */
    public function testThePingLeavesTheErrorHandlerAsItFoundIt(): void
    {
        $connectionMock = $this->createStub(Connection::class);
        $connectionMock->method('getDatabasePlatform')->willReturn($this->platformStub());
        $connLostRefl = new ReflectionClass(ConnectionLost::class);
        $connectionMock->method('executeQuery')
            ->willThrowException($connLostRefl->newInstanceWithoutConstructor());
        $connectionMock->method('getNativeConnection')->willReturn(true);

        $handler = static fn(): bool => true;
        set_error_handler($handler);

        try {
            (new PingingDBALAliveKeeper())->keepAlive($connectionMock, 'default');

            $current = set_error_handler(static fn(): bool => true);
            restore_error_handler();

            self::assertSame($handler, $current);
        } finally {
            restore_error_handler();
        }
    }

    #[DataProvider('connectionFailureMessages')]
    public function testItRecognisesWhatADeadConnectionSays(string $message): void
    {
        self::assertTrue(PingingDBALAliveKeeper::isConnectionFailure($message));
    }

    public function testItDoesNotMistakeOtherErrorsForADeadConnection(): void
    {
        self::assertFalse(PingingDBALAliveKeeper::isConnectionFailure('Undefined variable $x'));
        self::assertFalse(PingingDBALAliveKeeper::isConnectionFailure('Division by zero'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function connectionFailureMessages(): iterable
    {
        yield 'broken pipe' => ['PDO::query(): Send of 13 bytes failed with errno=32 Broken pipe'];
        yield 'gone away' => ['PDO::query(): MySQL server has gone away'];
        yield 'lost connection' => ['Lost connection to MySQL server during query'];
        yield 'error while sending' => ['PDO::query(): Error while sending QUERY packet. PID=1234'];
        yield 'reading result set' => ["Error reading result set's header"];
        yield 'reset by peer' => ['Connection reset by peer'];
    }

    /**
     * @return Connection&MockObject
     * @throws MockObjectException
     */
    private function connectionPinging(): Connection
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('getDatabasePlatform')->willReturn($this->platformStub());

        return $connectionMock;
    }

    private function platformStub(): AbstractPlatform
    {
        $platform = $this->createStub(AbstractPlatform::class);
        $platform->method('getDummySelectSQL')->willReturn('SELECT 1');

        return $platform;
    }

    /**
     * What Symfony's error handler does in debug: every PHP error becomes an exception.
     *
     * @param callable(): void $run
     */
    private function withThrowingErrorHandler(callable $run): void
    {
        set_error_handler(static function (int $level, string $message, string $file, int $line): never {
            throw new ErrorException($message, 0, $level, $file, $line);
        });

        try {
            $run();
        } finally {
            restore_error_handler();
        }
    }
}
