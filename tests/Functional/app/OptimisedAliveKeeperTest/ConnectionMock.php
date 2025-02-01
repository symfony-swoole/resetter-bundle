<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\OptimisedAliveKeeperTest;

use Composer\InstalledVersions;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PDO;

// phpcs:disable
/**
 * @phpstan-import-type WrapperParameterTypeArray from Connection
 */
if (version_compare(InstalledVersions::getVersion('doctrine/dbal'), '4.0.0', '<')) {
    final class ConnectionMock extends Connection
    {
        /**
         * @var array<string>
         */
        private array $queries = [];

        /**
         * @param array<string, mixed>|list<mixed> $params
         * @phpstan-param WrapperParameterTypeArray $types
         */
        public function executeQuery(
            string $sql,
            array $params = [],
            $types = [],
            ?QueryCacheProfile $qcp = null,
        ): Result {
            $args = func_get_args();
            $this->queries[] = $args[0];

            return new class extends Result {
                public function __construct() {}

                /**
                 * @return mixed
                 */
                public function fetchOne()
                {
                    return '1';
                }

                /**
                 * @return mixed
                 */
                public function fetch($fetchMode = null, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
                {
                    return 1;
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
} else {
    // phpcs:enable
    // phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses
    final class ConnectionMock extends Connection
    {
        /**
         * @var array<string>
         */
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
}
