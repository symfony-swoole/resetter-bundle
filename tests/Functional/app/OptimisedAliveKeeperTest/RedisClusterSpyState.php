<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\OptimisedAliveKeeperTest;

final class RedisClusterSpyState
{
    private int $constructorCalls = 0;

    private int $pingCount = 0;

    public function processConstructorCall(): void
    {
        $this->constructorCalls++;
    }

    public function getConstructorCalls(): int
    {
        return $this->constructorCalls;
    }

    public function ping(): int
    {
        $this->pingCount++;

        return $this->pingCount;
    }

    public function getPingCount(): int
    {
        return $this->pingCount;
    }
}
