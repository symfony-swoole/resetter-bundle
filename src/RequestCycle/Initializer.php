<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\RequestCycle;

interface Initializer
{
    public function initialize(): void;
}
