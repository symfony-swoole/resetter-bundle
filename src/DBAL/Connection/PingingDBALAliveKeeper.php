<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\DBAL\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionLost;
use Exception;
use Override;

/**
 * Pings the connection and reconnects when the ping finds it dead.
 *
 * A dead MySQL connection does not fail with an exception alone. PDO first raises a PHP warning or notice
 * about the write that could not be made ("Send of 13 bytes failed with errno=32 Broken pipe", "MySQL server
 * has gone away") and only then throws the error DBAL turns into {@see ConnectionLost}. An application that
 * converts PHP errors into exceptions - Symfony does in debug, through `framework.php_errors.throw` - throws
 * at the notice, before the driver ever reaches its own exception, so the ping failed with an `ErrorException`
 * instead of finding the connection lost, and the one thing this class exists to do never happened.
 *
 * The ping therefore runs with those notices set aside, and only those: any other PHP error is handed to
 * whatever handler was installed before. That matters under coroutines, where the query can yield and another
 * coroutine can raise an error while the ping's handler is in place - it must still reach the application.
 */
final class PingingDBALAliveKeeper implements DBALAliveKeeper
{
    /**
     * What PHP and the MySQL drivers say when the connection underneath a query is already gone. Matched as
     * substrings of the error message, case-insensitively.
     */
    private const array CONNECTION_FAILURE_MESSAGES = [
        'broken pipe',
        'server has gone away',
        'lost connection',
        'error while sending',
        'error reading result set',
        'connection reset by peer',
    ];

    /**
     * @throws Exception
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    #[Override]
    public function keepAlive(Connection $connection, string $connectionName): void
    {
        $query = $connection->getDatabasePlatform()->getDummySelectSQL();

        try {
            $this->ping($connection, $query);
        } catch (ConnectionLost) {
            $connection->close();
            $connection->getNativeConnection();
        }
    }

    public static function isConnectionFailure(string $message): bool
    {
        $message = strtolower($message);

        foreach (self::CONNECTION_FAILURE_MESSAGES as $failure) {
            if (str_contains($message, $failure)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function ping(Connection $connection, string $query): void
    {
        // The handler in place now, read first so the ping's own handler can hand everything else back to it.
        // Nothing runs between these two calls, so nothing can be missed while the placeholder is in place.
        $previous = set_error_handler(static fn(): bool => false);
        restore_error_handler();

        set_error_handler(
            static function (int $level, string $message, string $file = '', int $line = 0) use ($previous): bool {
                // Set aside: what the driver throws next is what says the connection is lost.
                if (self::isConnectionFailure($message)) {
                    return true;
                }

                // Anything else is somebody else's, and goes where it would have gone without this ping.
                return $previous !== null && $previous($level, $message, $file, $line);
            },
        );

        try {
            $connection->executeQuery($query);
        } finally {
            restore_error_handler();
        }
    }
}
