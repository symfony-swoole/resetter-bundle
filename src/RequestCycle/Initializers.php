<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\RequestCycle;

final readonly class Initializers
{
    /**
     * @param iterable<Initializer> $initializers
     */
    public function __construct(
        private iterable $initializers,
    ) {}

    public function initialize(): void
    {
        foreach ($this->initializers as $initializer) {
            $initializer->initialize();
        }
    }
}
