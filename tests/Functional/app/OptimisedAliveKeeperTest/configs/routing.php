<?php

declare(strict_types=1);

use SwooleBundle\ResetterBundle\Tests\Functional\app\OptimisedAliveKeeperTest\TestController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('swoole_bundle_resetter_do_nothing', '/dummy')
        ->controller([TestController::class, 'doNothingAction'])
        ->methods(['GET']);
};
