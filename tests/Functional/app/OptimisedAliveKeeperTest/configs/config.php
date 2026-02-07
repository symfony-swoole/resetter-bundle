<?php

declare(strict_types=1);

use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\TestController;
use SwooleBundle\ResetterBundle\Tests\Functional\app\OptimisedAliveKeeperTest\ConnectionMock;
use SwooleBundle\ResetterBundle\Tests\Functional\app\OptimisedAliveKeeperTest\RedisClusterSpy;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../../config/framework.php');

    $containerConfigurator->import(__DIR__ . '/../../config/doctrine.php');

    $containerConfigurator->extension('swoole_bundle_resetter', [
        'ping_interval' => 10,
        'redis_cluster_connections' => [
            'default' => RedisCluster::class,
        ],
    ]);

    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'default_connection' => 'default',
            'connections' => [
                'default' => [
                    'wrapper_class' => ConnectionMock::class,
                ],
            ],
        ],
    ]);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(TestController::class)
        ->public()
        ->arg('$entityManager', service('doctrine.orm.default_entity_manager'));

    $services->set(RedisCluster::class, RedisClusterSpy::class)
        ->public()
        ->arg('$name', 'default')
        ->arg('$seeds', ['localhost:6379'])
        ->arg('$timeout', 2)
        ->arg('$readTimeout', 2);
};
