<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\Helper;

use Closure;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Override;
use ProxyManager\Proxy\VirtualProxyInterface;

/**
 * @final
 */
class ProxyConnectionMock extends Connection implements VirtualProxyInterface
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

    public function setProxyInitializer(?Closure $initializer = null): void {}

    public function getProxyInitializer(): ?Closure
    {
        return null;
    }

    public function initializeProxy(): bool
    {
        return false;
    }

    public function isProxyInitialized(): bool
    {
        return false;
    }

    public function getWrappedValueHolderValue(): ?object
    {
        return null;
    }

    #[Override]
    public function isTransactionActive(): bool
    {
        return false;
    }

    #[Override]
    public function rollBack(): void {}
}
