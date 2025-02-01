<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @final
 */
#[ORM\Entity]
#[ORM\Table(name: 'test2')]
class TestEntity2
{
    public function __construct(
        #[ORM\Column(type: 'integer')]
        #[ORM\Id]
        private int $id,
    ) {}
}
