<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\ORM;

use Composer\InstalledVersions;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\ManagerRegistry as RegistryInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use SwooleBundle\ResetterBundle\ORM\ResettableEntityManager;
use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\Entity\TestEntity;

final class ResettableEntityManagerTest extends TestCase
{
    public function testGetRepository(): void
    {
        if (version_compare(InstalledVersions::getVersion('doctrine/orm') ?? '1.0.0', '3.0.0', '<')) {
            $repositoryMock = $this->createMock(ObjectRepository::class);
        } else {
            $repositoryMock = $this->createMock(EntityRepository::class);
        }
        $repositoryFactoryMock = $this->createMock(RepositoryFactory::class);
        $repositoryFactoryMock->expects(self::once())
            ->method('getRepository')
            ->with($this->callback(static function ($value) {
                self::assertInstanceOf(ResettableEntityManager::class, $value);

                return true;
            }))
            ->willReturn($repositoryMock);
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::once())
            ->method('getRepositoryFactory')
            ->willReturn($repositoryFactoryMock);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $registryMock = $this->createMock(RegistryInterface::class);

        $em = new ResettableEntityManager($configurationMock, $emMock, $registryMock, 'default');

        $em->getRepository(TestEntity::class);
    }

    public function testClearOrResetIfNeededShouldClearWhenWrappedIsOpen(): void
    {
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::atLeast(1))
            ->method('getRepositoryFactory')
            ->willReturn($this->createMock(RepositoryFactory::class));
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock->expects(self::atLeast(1))
            ->method('isOpen')
            ->willReturn(true);
        $emMock->expects(self::atLeast(1))
            ->method('clear')
            ->with();
        $registryMock = $this->createMock(RegistryInterface::class);

        $em = new ResettableEntityManager($configurationMock, $emMock, $registryMock, 'default');
        $em->clearOrResetIfNeeded();
    }

    public function testClearOrResetIfNeededShouldResetWhenWrappedIsClosed(): void
    {
        $decoratedName = 'default';
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->expects(self::atLeast(1))
            ->method('getRepositoryFactory')
            ->willReturn($this->createMock(RepositoryFactory::class));
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock->expects(self::atLeast(1))
            ->method('isOpen')
            ->willReturn(false);
        $registryMock = $this->createMock(RegistryInterface::class);
        $registryMock->expects(self::atLeast(1))
            ->method('resetManager')
            ->with($decoratedName)
            ->willReturn($this->createMock(ResettableEntityManager::class));

        $em = new ResettableEntityManager($configurationMock, $emMock, $registryMock, $decoratedName);

        $em->clearOrResetIfNeeded();
    }
}
