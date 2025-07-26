<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle;

use Override;
use SwooleBundle\ResetterBundle\DependencyInjection\CompilerPass\AliveKeeperPass;
use SwooleBundle\ResetterBundle\DependencyInjection\CompilerPass\EntityManagerDecoratorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SwooleBundleResetterBundle extends Bundle
{
    #[Override]
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new EntityManagerDecoratorPass());
        $container->addCompilerPass(new AliveKeeperPass());
    }
}
