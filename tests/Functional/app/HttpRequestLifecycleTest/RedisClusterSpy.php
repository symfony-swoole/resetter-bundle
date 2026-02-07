<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest;

use Override;
use RedisCluster;

/**
 * @final
 */
// phpcs:ignore SlevomatCodingStandard.Classes.RequireAbstractOrFinal.ClassNeitherAbstractNorFinal
class RedisClusterSpy extends RedisCluster
{
    public function __construct(
        string $name,
        ?array $seeds,
        int|float|null $timeout = null,
        int|float|null $readTimeout = null,
        bool $persistent = false,
        mixed $auth = null,
    ) {
        RedisClusterSpyStateManager::getFor($name)->processConstructorCall();
    }

    #[Override]
    public function ping(array|string $key_or_address, ?string $message = null): mixed
    {
        return 1;
    }
}
