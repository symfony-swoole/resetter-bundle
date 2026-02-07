<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\DBAL\Connection;

use Doctrine\DBAL\Connection;
use Exception;
use Override;
use Symfony\Component\VarExporter\LazyObjectInterface;

final readonly class PassiveIgnoringDBALAliveKeeper implements DBALAliveKeeper
{
    public function __construct(
        private DBALAliveKeeper $decorated,
    ) {}

    /**
     * @throws Exception
     */
    #[Override]
    public function keepAlive(Connection $connection, string $connectionName): void
    {
        if ($connection instanceof LazyObjectInterface && !$connection->isLazyObjectInitialized()) {
            return;
        }

        if (!$connection->isConnected()) {
            return;
        }

        $this->decorated->keepAlive($connection, $connectionName);
    }
}
