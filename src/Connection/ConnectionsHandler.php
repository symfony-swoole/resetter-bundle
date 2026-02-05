<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Connection;

use Exception;
use Override;
use SwooleBundle\ResetterBundle\RequestCycle\Initializer;

final readonly class ConnectionsHandler implements Initializer
{
    /**
     * @param array<PlatformAliveKeeper> $aliveKeepers
     */
    public function __construct(
        private array $aliveKeepers,
    ) {}

    /**
     * @throws Exception
     */
    #[Override]
    public function initialize(): void
    {
        foreach ($this->aliveKeepers as $aliveKeeper) {
            $aliveKeeper->keepAlive();
        }
    }
}
