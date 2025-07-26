<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\DBAL\Connection;

use Doctrine\DBAL\Connection;
use Override;
use RuntimeException;
use SwooleBundle\ResetterBundle\Connection\PlatformAliveKeeper as GenericPlatformAliveKeeper;

final class DBALPlatformAliveKeeper implements GenericPlatformAliveKeeper
{
    /**
     * @param array<string, Connection> $connections
     * @param array<string, DBALAliveKeeper> $aliveKeepers
     */
    public function __construct(
        private array $connections,
        private array $aliveKeepers,
    ) {}

    #[Override]
    public function keepAlive(): void
    {
        foreach ($this->aliveKeepers as $connectionName => $aliveKeeper) {
            if (!isset($this->connections[$connectionName])) {
                throw new RuntimeException(
                    sprintf('Connection "%s" is missing.', $connectionName),
                );
            }

            $connection = $this->connections[$connectionName];
            $aliveKeeper->keepAlive($connection, $connectionName);
        }
    }

    public function addAliveKeeper(string $connectionName, Connection $connection, DBALAliveKeeper $aliveKeeper): void
    {
        $this->connections[$connectionName] = $connection;
        $this->aliveKeepers[$connectionName] = $aliveKeeper;
    }

    public function removeAliveKeeper(string $connectionName): void
    {
        unset($this->connections[$connectionName], $this->aliveKeepers[$connectionName]);
    }
}
