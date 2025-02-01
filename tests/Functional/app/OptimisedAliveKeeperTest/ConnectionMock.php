<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\OptimisedAliveKeeperTest;

use Composer\InstalledVersions;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PDO;

/**
 * @phpstan-import-type WrapperParameterTypeArray from Connection
 */

final class ConnectionMock extends Connection
{
    private array $queries = [];

    /**
     * @param array<string, mixed>|list<mixed> $params
     * @phpstan-param WrapperParameterTypeArray $types
     */
    public function executeQuery(
        string $sql,
        array $params = [],
        array $types = [],
        ?QueryCacheProfile $qcp = null,
    ): Result {
        $args = func_get_args();
        $this->queries[] = $args[0];

        // phpcs:disable
        if (version_compare(InstalledVersions::getVersion('doctrine/dbal'), '4.0.0', '<')) {
            return new class extends Result {
                public function __construct() {}

                public function fetchOne(): mixed
                {
                    return '1';
                }

                public function fetch(
                    $fetchMode = null,
                    $cursorOrientation = PDO::FETCH_ORI_NEXT,
                    $cursorOffset = 0,
                ): mixed {
                    return 1;
                }
            };
        }

        // phpcs:enable
        return new class extends Result {
            public function __construct() {}

            public function fetchOne(): mixed
            {
                return '1';
            }
        };
    }

    /**
     * @return array<string>
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    public function getQueriesCount(): int
    {
        return count($this->queries);
    }
}
