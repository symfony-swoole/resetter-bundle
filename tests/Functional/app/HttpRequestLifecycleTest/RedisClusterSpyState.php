<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest;

final class RedisClusterSpyState
{
    private int $constructorCalls = 0;

    public function processConstructorCall(): void
    {
        $this->constructorCalls++;
    }

    public function constructorCalls(): int
    {
        return $this->constructorCalls;
    }
}
