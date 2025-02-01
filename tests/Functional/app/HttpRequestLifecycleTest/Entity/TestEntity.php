<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @final
 */
#[ORM\Entity]
#[ORM\Table(name: 'test')]
class TestEntity
{
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;
}
