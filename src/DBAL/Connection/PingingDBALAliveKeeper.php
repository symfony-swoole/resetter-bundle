<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\DBAL\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionLost;
use Exception;
use Override;

final class PingingDBALAliveKeeper implements DBALAliveKeeper
{
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
            $connection->executeQuery($query);
        } catch (ConnectionLost) {
            $connection->close();
            $connection->getNativeConnection();
        }
    }
}
