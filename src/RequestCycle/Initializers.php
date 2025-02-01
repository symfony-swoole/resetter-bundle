<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\RequestCycle;

final class Initializers
{
    /**
     * @param iterable<Initializer> $initializers
     */
    public function __construct(
        private readonly iterable $initializers,
    ) {}

    public function initialize(): void
    {
        foreach ($this->initializers as $initializer) {
            $initializer->initialize();
        }
    }
}
