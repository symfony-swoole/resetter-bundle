<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest;

final class RedisClusterSpyStateManager
{
    /**
     * @var array<string, RedisClusterSpyState>
     */
    private static array $connectionStates = [];

    public static function getFor(string $name): RedisClusterSpyState
    {
        if (!isset(self::$connectionStates[$name])) {
            self::$connectionStates[$name] = new RedisClusterSpyState();
        }

        return self::$connectionStates[$name];
    }

    public static function reset(): void
    {
        self::$connectionStates = [];
    }
}
