<?php

declare(strict_types=1);

use SwooleBundle\ResetterBundle\Connection\ConnectionsHandler;
use SwooleBundle\ResetterBundle\DBAL\Connection\DBALPlatformAliveKeeper;
use SwooleBundle\ResetterBundle\DBAL\Connection\FailoverAware\FailoverAwareDBALAliveKeeper;
use SwooleBundle\ResetterBundle\DBAL\Connection\OptimizedDBALAliveKeeper;
use SwooleBundle\ResetterBundle\DBAL\Connection\PassiveIgnoringDBALAliveKeeper;
use SwooleBundle\ResetterBundle\DBAL\Connection\PingingDBALAliveKeeper;
use SwooleBundle\ResetterBundle\DBAL\Connection\TransactionDiscardingDBALAliveKeeper;
use SwooleBundle\ResetterBundle\Redis\Cluster\Connection\OptimizedRedisClusterAliveKeeper;
use SwooleBundle\ResetterBundle\Redis\Cluster\Connection\PassiveIgnoringRedisClusterAliveKeeper;
use SwooleBundle\ResetterBundle\Redis\Cluster\Connection\PingingRedisClusterAliveKeeper;
use SwooleBundle\ResetterBundle\Redis\Cluster\Connection\RedisClusterPlatformAliveKeeper;
use SwooleBundle\ResetterBundle\RequestCycle\Initializers;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults();

    $services->set(ConnectionsHandler::class)
        ->arg('$aliveKeepers', null);

    $services->set(DBALPlatformAliveKeeper::class)
        ->arg('$connections', null)
        ->arg('$aliveKeepers', null);

    $services->set(OptimizedDBALAliveKeeper::class)
        ->abstract(true)
        ->arg('$decorated', null);

    $services->set(PingingDBALAliveKeeper::class);

    $services->set(TransactionDiscardingDBALAliveKeeper::class)
        ->abstract(true)
        ->arg('$decorated', null)
        ->arg('$logger', service('logger'))
        ->tag('monolog.logger', ['channel' => 'resetter-bundle']);

    $services->set(PassiveIgnoringDBALAliveKeeper::class)
        ->abstract(true)
        ->arg('$decorated', null);

    $services->set(RedisClusterPlatformAliveKeeper::class)
        ->arg('$connections', null)
        ->arg('$aliveKeepers', null);

    $services->set(PingingRedisClusterAliveKeeper::class)
        ->abstract(true)
        ->arg('$constructorArguments', null)
        ->arg('$logger', service('logger'))
        ->tag('monolog.logger', ['channel' => 'resetter-bundle']);

    $services->set(PassiveIgnoringRedisClusterAliveKeeper::class)
        ->abstract(true)
        ->arg('$decorated', null);

    $services->set(FailoverAwareDBALAliveKeeper::class)
        ->abstract(true)
        ->arg('$logger', service('logger'))
        ->arg('$connectionType', null)
        ->tag('monolog.logger', ['channel' => 'resetter-bundle']);

    $services->set(OptimizedRedisClusterAliveKeeper::class)
        ->abstract(true)
        ->arg('$decorated', null);

    $services->set(Initializers::class)
        ->args([
            tagged_iterator('swoole_bundle_resetter.app_initializer'),
        ])
        ->tag('kernel.event_listener', [
            'event' => 'kernel.request',
            'method' => 'initialize',
            'priority' => 1000000,
        ]);
};
