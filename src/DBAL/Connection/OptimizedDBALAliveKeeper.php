<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\DBAL\Connection;

use Doctrine\DBAL\Connection;
use Exception;
use Override;

final class OptimizedDBALAliveKeeper implements DBALAliveKeeper
{
    /**
     * @const int
     */
    private const DEFAULT_PING_INTERVAL = 0;

    private int $lastPingAt;

    public function __construct(
        private readonly DBALAliveKeeper $decorated,
        private readonly int $pingIntervalInSeconds = self::DEFAULT_PING_INTERVAL,
    ) {
        $this->lastPingAt = 0;
    }

    /**
     * @throws Exception
     */
    #[Override]
    public function keepAlive(Connection $connection, string $connectionName): void
    {
        if (!$this->isPingNeeded()) {
            return;
        }

        $this->decorated->keepAlive($connection, $connectionName);
    }

    private function isPingNeeded(): bool
    {
        $lastPingAt = $this->lastPingAt;
        $now = time();
        $this->lastPingAt = $now;

        return $now - $lastPingAt >= $this->pingIntervalInSeconds;
    }
}
