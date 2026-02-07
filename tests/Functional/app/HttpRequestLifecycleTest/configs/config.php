<?php

declare(strict_types=1);

use Psr\Log\NullLogger;
use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\EntityManagerChecker;
use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\ExcludedTestController;
use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\RedisClusterSpy;
use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\TestController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../../config/framework.php');

    $containerConfigurator->import(__DIR__ . '/../../config/doctrine.php');

    $containerConfigurator->extension('swoole_bundle_resetter', [
        'exclude_from_processing' => [
            'entity_managers' => [
                'excluded',
            ],
            'connections' => [
                'dbal' => [
                    'excluded',
                ],
                'redis_cluster' => [
                    'excluded',
                ],
            ],
        ],
        'redis_cluster_connections' => [
            'default' => RedisCluster::class,
            'excluded' => 'RedisCluster2',
        ],
    ]);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(TestController::class)
        ->public()
        ->arg('$entityManager', service('doctrine.orm.default_entity_manager'));

    $services->set(
        'SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\EntityManagerChecker.default',
        EntityManagerChecker::class
    )
        ->public()
        ->arg('$entityManager', service('doctrine.orm.default_entity_manager'));

    $services->set(ExcludedTestController::class)
        ->public()
        ->arg('$entityManager', service('doctrine.orm.excluded_entity_manager'));

    $services->set(
        'SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\EntityManagerChecker.excluded',
        EntityManagerChecker::class
    )
        ->public()
        ->arg('$entityManager', service('doctrine.orm.excluded_entity_manager'));

    $services->set(RedisCluster::class, RedisClusterSpy::class)
        ->public()
        ->arg('$name', 'default')
        ->arg('$seeds', ['localhost:6379'])
        ->arg('$timeout', 2)
        ->arg('$readTimeout', 2);

    $services->set('RedisCluster2', RedisClusterSpy::class)
        ->public()
        ->arg('$name', 'excluded')
        ->arg('$seeds', ['localhost:6379'])
        ->arg('$timeout', 2)
        ->arg('$readTimeout', 2);

    $services->set('logger', NullLogger::class);
};
