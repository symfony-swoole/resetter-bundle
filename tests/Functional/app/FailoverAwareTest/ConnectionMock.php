<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\FailoverAwareTest;

use Composer\InstalledVersions;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Override;
use PDO;

// phpcs:disable
/**
 * @phpstan-import-type WrapperParameterTypeArray from Connection
 */
if (version_compare(InstalledVersions::getVersion('doctrine/dbal'), '4.0.0', '<')) {
    final class ConnectionMock extends Connection
    {
        private string $query;

        /**
         * @param array<string, mixed>|list<mixed> $params
         * @phpstan-param WrapperParameterTypeArray $types
         */
        #[Override]
        public function executeQuery(
            string $sql,
            array $params = [],
            $types = [],
            ?QueryCacheProfile $qcp = null,
        ): Result {
            $args = func_get_args();
            $this->query = $args[0];

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

        public function getQuery(): string
        {
            return $this->query;
        }
    }
} else {
    // phpcs:enable
    // phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses
    final class ConnectionMock extends Connection
    {
        private string $query;

        /**
         * @param array<string, mixed>|list<mixed> $params
         * @phpstan-param WrapperParameterTypeArray $types
         */
        #[Override]
        public function executeQuery(
            string $sql,
            array $params = [],
            array $types = [],
            ?QueryCacheProfile $qcp = null,
        ): Result {
            $args = func_get_args();
            $this->query = $args[0];

            return new class extends Result {
                public function __construct() {}

                public function fetchOne(): mixed
                {
                    return '1';
                }
            };
        }

        public function getQuery(): string
        {
            return $this->query;
        }
    }
}
