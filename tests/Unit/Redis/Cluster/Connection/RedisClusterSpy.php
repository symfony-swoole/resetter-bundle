<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\Redis\Cluster\Connection;

use Override;
use RedisCluster;
use RedisClusterException;
use Symfony\Component\VarExporter\LazyObjectInterface;
use Symfony\Component\VarExporter\LazyProxyTrait;

/**
 * @final
 */
class RedisClusterSpy extends RedisCluster implements LazyObjectInterface
{
    use LazyProxyTrait;

    private int $constructorCalls = 0;

    private bool $wasConstructorCalled = false;

    /** @var array<int, mixed> */
    private array $constructorParametersFirst = [];

    /** @var array<int, mixed> */
    private array $constructorParametersSecond = [];

    private bool $initialized = true;

    public function __construct(
        ?string $name,
        ?array $seeds,
        int|float|null $timeout = null,
        int|float|null $readTimeout = null,
        bool $persistent = false,
        mixed $auth = null,
    ) {
        $this->constructorCalls++;

        if ($this->constructorCalls === 1) {
            $this->constructorParametersFirst = [$name, $seeds, $timeout, $readTimeout];
        } elseif ($this->constructorCalls > 1) {
            $this->wasConstructorCalled = true;
            $this->constructorParametersSecond = [$name, $seeds, $timeout, $readTimeout];
        }
    }

    public function wasConstructorCalled(): bool
    {
        return $this->wasConstructorCalled;
    }

    /**
     * @return array<int, mixed>
     */
    public function getConstructorParametersFirst(): array
    {
        return $this->constructorParametersFirst;
    }

    /**
     * @return array<int, mixed>
     */
    public function getConstructorParametersSecond(): array
    {
        return $this->constructorParametersSecond;
    }

    #[Override]
    public function ping(array|string $key_or_address, ?string $message = null): mixed
    {
        throw new RedisClusterException('Test exception');
    }
}
