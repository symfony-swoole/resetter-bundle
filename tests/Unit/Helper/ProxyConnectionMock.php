<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\Helper;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Override;

/**
 * @final
 */
class ProxyConnectionMock extends Connection
{
    /**
     * @param array<string, mixed> $params
     */
    public function __construct(
        array $params,
        Driver $driver,
        ?Configuration $config = null,
        ?EventManager $eventManager = null,
    ) {}

    #[Override]
    public function isTransactionActive(): bool
    {
        return false;
    }

    #[Override]
    public function rollBack(): void {}
}
