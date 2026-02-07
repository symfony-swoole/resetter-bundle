<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'default_connection' => 'default',
            'connections' => [
                'default' => [
                    'driver' => 'pdo_sqlite',
                    'charset' => 'utf8',
                    'path' => '%kernel.project_dir%/data/sqlite_default.db',
                    'logging' => true,
                ],
                'excluded' => [
                    'driver' => 'pdo_sqlite',
                    'charset' => 'utf8',
                    'path' => '%kernel.project_dir%/data/sqlite_excluded.db',
                    'logging' => true,
                ],
            ],
        ],
        'orm' => [
            'default_entity_manager' => 'default',
            'controller_resolver' => [
                'auto_mapping' => false,
            ],
            'entity_managers' => [
                'default' => [
                    'connection' => 'default',
                    'auto_mapping' => true,
                    'mappings' => [
                        'Entity' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => '%kernel.project_dir%/HttpRequestLifecycleTest/Entity',
                            'prefix' => 'SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\Entity', // phpcs:ignore
                            'alias' => 'Entity',
                        ],
                    ],
                ],
                'excluded' => [
                    'connection' => 'excluded',
                    'mappings' => [
                        'ExcludedEntity' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => '%kernel.project_dir%/HttpRequestLifecycleTest/ExcludedEntity',
                            'prefix' => 'SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\ExcludedEntity', // phpcs:ignore
                            'alias' => 'Entity',
                        ],
                    ],
                ],
            ],
        ],
    ]);
};
