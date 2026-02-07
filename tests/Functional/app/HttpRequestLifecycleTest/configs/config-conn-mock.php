<?php

declare(strict_types=1);

use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\ConnectionMock;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/config.php');

    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'default_connection' => 'default',
            'connections' => [
                'default' => [
                    'wrapper_class' => ConnectionMock::class,
                ],
                'excluded' => [
                    'wrapper_class' => ConnectionMock::class,
                ],
            ],
        ],
    ]);
};
