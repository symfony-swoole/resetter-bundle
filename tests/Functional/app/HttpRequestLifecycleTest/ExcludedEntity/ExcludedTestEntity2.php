<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\ExcludedEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @final
 */
#[ORM\Entity]
#[ORM\Table(name: 'excluded_test2')]
class ExcludedTestEntity2
{
    public function __construct(
        #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
        #[ORM\Id]
        private int $id,
    ) {}
}
