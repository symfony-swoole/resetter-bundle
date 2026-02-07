<?php

declare(strict_types=1);

use Psr\Log\NullLogger;
use SwooleBundle\ResetterBundle\Tests\Functional\app\FailoverAwareTest\ConnectionMock;
use SwooleBundle\ResetterBundle\Tests\Functional\app\FailoverAwareTest\TestController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../../config/framework.php');

    $containerConfigurator->import(__DIR__ . '/../../config/doctrine.php');

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

    $containerConfigurator->extension('swoole_bundle_resetter', [
        'failover_connections' => [
            'default' => 'writer',
        ],
    ]);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(TestController::class)
        ->public();

    $services->set('logger', NullLogger::class);
};
