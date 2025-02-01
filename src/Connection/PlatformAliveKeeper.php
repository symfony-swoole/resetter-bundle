<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Connection;

interface PlatformAliveKeeper
{
    public function keepAlive(): void;
}
