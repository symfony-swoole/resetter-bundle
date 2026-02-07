<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'secret' => 'secret',
        'router' => [
            'resource' => '%kernel.project_dir%/%kernel.test_case%/configs/routing.php',
            'utf8' => true,
        ],
        'test' => null,
        'http_method_override' => true,
        'handle_all_throwables' => true,
        'php_errors' => [
            'log' => true,
        ],
    ]);
};
