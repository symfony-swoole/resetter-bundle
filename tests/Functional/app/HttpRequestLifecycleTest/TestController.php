<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\Entity\TestEntity;
use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\Entity\TestEntity2;
use Symfony\Component\HttpFoundation\Response;

final class TestController
{
    private readonly EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $entityManager->getRepository(TestEntity2::class);
    }

    public function doNothingAction(): Response
    {
        return new Response();
    }

    public function persistTestAction(): Response
    {
        $this->entityManager->persist(new TestEntity());
        $this->entityManager->flush();

        return new Response();
    }

    public function persistErrorTestAction(): Response
    {
        try {
            $this->entityManager->persist(new TestEntity2(10));
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
        }

        return new Response();
    }

    public function removeAllPersistedAction(): Response
    {
        $all = $this->repository->findAll();

        foreach ($all as $testEntity2) {
            $this->entityManager->remove($testEntity2);
        }

        $this->entityManager->flush();

        return new Response();
    }
}
