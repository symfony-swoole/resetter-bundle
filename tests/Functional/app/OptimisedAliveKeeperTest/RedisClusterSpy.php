<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\OptimisedAliveKeeperTest;

use RedisCluster;

/**
 * @final
 */
// phpcs:ignore SlevomatCodingStandard.Classes.RequireAbstractOrFinal.ClassNeitherAbstractNorFinal
class RedisClusterSpy extends RedisCluster
{
    public function __construct(
        private readonly string $name,
        ?array $seeds,
        int|float|null $timeout = null,
        int|float|null $readTimeout = null,
        bool $persistent = false,
        mixed $auth = null,
    ) {
        RedisClusterSpyStateManager::getFor($this->name)->processConstructorCall();
    }

    public function ping(array|string $key_or_address, ?string $message = null): mixed
    {
        return RedisClusterSpyStateManager::getFor($this->name)->ping();
    }
}
