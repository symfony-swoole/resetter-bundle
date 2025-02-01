<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\OptimisedAliveKeeperTest;

use RedisCluster;

final class RedisClusterSpy extends RedisCluster
{
    private int $constructorCalls = 0;

    private int $pingCount = 0;

    /** @var array<int, mixed> */
    private array $constructorParametersSecond = [];

    public function __construct(
        ?string $name,
        ?array $seeds,
        int|float|null $timeout = null,
        int|float|null $readTimeout = null,
        bool $persistent = false,
        mixed $auth = null,
    ) {
        $this->constructorCalls++;
    }

    public function getConstructorCalls(): int
    {
        return $this->constructorCalls;
    }

    public function ping(array|string $key_or_address, ?string $message = null): mixed
    {
        $this->pingCount++;

        return $this->pingCount;
    }

    public function getPingCount(): int
    {
        return $this->pingCount;
    }
}
