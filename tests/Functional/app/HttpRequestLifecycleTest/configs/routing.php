<?php

declare(strict_types=1);

use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\TestController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('resetter_persist_test', '/')
        ->controller([TestController::class, 'persistTestAction'])
        ->methods(['GET']);

    $routingConfigurator->add('resetter_persist_error_test', '/persist-error')
        ->controller([TestController::class, 'persistErrorTestAction'])
        ->methods(['GET']);

    $routingConfigurator->add('resetter_remove_all_test', '/remove-all')
        ->controller([TestController::class, 'removeAllPersistedAction'])
        ->methods(['GET']);

    $routingConfigurator->add('resetter_do_nothing', '/dummy')
        ->controller([TestController::class, 'doNothingAction'])
        ->methods(['GET']);

    $routingConfigurator->add('resetter_excluded_persist_test', '/persist-excluded')
        ->controller([TestController::class, 'persistTestAction'])
        ->methods(['GET']);

    $routingConfigurator->add('resetter_excluded_persist_error_test', '/persist-error-excluded')
        ->controller([TestController::class, 'persistErrorTestAction'])
        ->methods(['GET']);

    $routingConfigurator->add('resetter_excluded_remove_all_test', '/remove-all-excluded')
        ->controller([TestController::class, 'removeAllPersistedAction'])
        ->methods(['GET']);
};
